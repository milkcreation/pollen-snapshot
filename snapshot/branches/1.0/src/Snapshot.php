<?php

declare(strict_types=1);

namespace Pollen\Snapshot;

use Exception;
use Pollen\Http\BinaryFileResponse;
use Pollen\Http\BinaryFileResponseInterface;
use Pollen\Http\Response;
use Pollen\Http\ResponseInterface;
use Pollen\Support\Concerns\ResourcesAwareTrait;
use Psr\Container\ContainerInterface as Container;
use Pollen\Snapshot\Drivers\SnapshotPuppeteerDriver;
use Pollen\Support\Filesystem;
use Pollen\Support\Proxy\ContainerProxy;
use Pollen\Validation\Validator as v;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Snapshot implements SnapshotInterface
{
    use ResourcesAwareTrait;
    use ContainerProxy;

    /**
     * Séparateur de répertoire du sytème de fichier.
     * @var string
     */
    protected const DS = DIRECTORY_SEPARATOR;

    /**
     * Instance du pilote de capture.
     * @var SnapshotDriverInterface
     */
    protected $driver;

    /**
     * Chemin absolu vers le répertoire de dépôt des fichiers.
     * @var string
     */
    protected $outputDir;

    /**
     * Activation de la réécriture des captures existantes.
     * @var bool
     */
    protected $overwrite = true;

    /**
     * Liste des chemins absolus vers les captures.
     * @var string[]
     */
    protected $captures = [];

    /**
     * Liste des résultats de sortie d'exécution de script.
     * @var string[][]
     */
    protected $outputs = [];

    /**
     * @param SnapshotDriverInterface|null $driver
     * @param Container|null $container
     */
    public function __construct(?SnapshotDriverInterface $driver = null, ?Container $container = null)
    {
        if ($container !== null) {
            $this->setContainer($container);
        }

        $this->setResourcesBaseDir(dirname(__DIR__) . '/resources');

        if ($driver !== null) {
            $this->driver = $driver;
        } else {
            $this->driver = $this->containerHas(SnapshotDriverInterface::class)
                ? $this->containerGet(SnapshotDriverInterface::class) : new SnapshotPuppeteerDriver();
        }

        $this->outputDir = Filesystem::normalizePath(sys_get_temp_dir());
    }

    /**
     * @inheritDoc
     */
    public function all(): array
    {
        return $this->captures;
    }

    /**
     * @inheritDoc
     */
    public function displayResponse(?string $name = null): ResponseInterface
    {
       try {
           return $this->response($name, 'inline');
       } catch(RuntimeException $e) {
         return new Response($e->getMessage(), 404);
       }
    }

    /**
     * @inheritDoc
     */
    public function downloadResponse(?string $name = null): ResponseInterface
    {
        try {
            return $this->response($name);
        } catch(RuntimeException $e) {
            return new Response($e->getMessage(), 404);
        }
    }

    /**
     * Exécution d'une capture
     *
     * @param string $url
     * @param string|null $name
     * @param string $format
     *
     * @return void
     *
     * @throws RuntimeException
     * @throws ProcessFailedException
     */
    protected function exec(string $url, ?string $name = null, string $format = 'img'): void
    {
        if (!v::url()->validate($url)) {
            throw new RuntimeException('Snapshot source is not a valid url');
        }

        if ($name === null) {
            try {
                $bytes = random_bytes(26);
                $name = 'snap_' . rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
            } catch (Exception $e) {
                throw new RuntimeException('Snapshot unable to create random output filename');
            }
        }

        $ext = pathinfo($name, PATHINFO_EXTENSION);
        if (!$ext) {
            switch ($format) {
                default:
                case 'img' :
                    $ext = 'jpg';
                    break;
                case 'pdf' :
                    $ext = 'pdf';
                    break;
            }
            $name .= ".$ext";
        }

        if ($format === 'img' && !in_array($ext, ['jpg', 'png'])) {
            throw new RuntimeException('Image Snapshot name extension invalid, only .jpg or .png are allowed');
        } elseif ($format === 'pdf' && $ext !== 'pdf') {
            throw new RuntimeException('Pdt Snapshot name extension invalid, only .pdf is allowed');
        }

        $filename = $this->getOutputPath($name);
        if (!is_writable($this->outputDir)) {
            throw new RuntimeException(sprintf('Snapshot output dir [%s] is not writable', $this->outputDir));
        }

        if (file_exists($filename)){
            if(!$this->overwrite) {
                $this->setCapture($name, $filename);
                return;
            }

            if (!is_writable($filename)) {
                throw new RuntimeException(sprintf('Snapshot output filename [%s] is not writable', $filename));
            }
        }

        $args = $this->driver->setSnapshot($this)->getCommand($url, $filename, $format);

        $process = new Process($args);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        try {
            $this->setCapture($name, $filename);
        } catch(RuntimeException $e) {
            throw $e;
        }

        try {
            $this->outputs[$name] = $process->getOutput();
        } catch (LogicException $e) {
            $this->outputs[$name] = $e->getMessage();
        }
    }

    /**
     * @inheritDoc
     */
    public function get(?string $name = null): ?string
    {
        if ($name === null) {
            return end($this->captures)?: null;
        }
        return $this->captures[$name] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getOutputPath(string $path = ''): string
    {
        if ($path && (trim($path) !== '/')) {
            $path = rtrim(ltrim($path, static::DS), static::DS);
            $filename = $this->outputDir . static::DS . $path;
        } else {
            $filename = $this->outputDir;
        }

        return $filename;
    }

    /**
     * @inheritDoc
     */
    public function img(string $url, ?string $name = null): SnapshotInterface
    {
        try {
            $this->exec($url, $name);
        } catch(Exception $e) {
            throw new RuntimeException('Image Snapshot throws an exception : ' . $e->getMessage(), 0, $e);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function pdf(string $url, ?string $name= null): SnapshotInterface
    {
        try {
            $this->exec($url, $name, 'pdf');
        } catch(Exception $e) {
            throw new RuntimeException('PDF Snapshot throws an exception', 0, $e);
        }

        return $this;
    }

    /**
     * Récupère la réponse de téléchargement ou d'affichage d'un fichier.
     *
     * @param string|null $name
     * @param string $disposition attachment|inline
     *
     * @return BinaryFileResponseInterface
     */
    protected function response(?string $name = null, string $disposition = 'attachment'): BinaryFileResponseInterface
    {
        if (!$file = $this->get($name)) {
            throw new RuntimeException(
                sprintf('Snapshot unable creates HTTP Response for [%s], file seems inaccessible', $name)
            );
        }

        try {
            $response = new BinaryFileResponse($file);
        } catch(FileException $e) {
            throw new RuntimeException(
                sprintf('Snapshot unable creates HTTP Response for file [%s]', $file), 0, $e
            );
        }

        $filename = $response->getFile()->getFilename();
        $response->headers->set ('Content-Type', $response->getFile()->getMimeType());
        $response->setContentDisposition($disposition, $filename);

        return $response;
    }

    /**
     * @inheritDoc
     */
    public function setCapture(string $name, string $filename): SnapshotInterface
    {
        if (!file_exists($filename)) {
            throw new RuntimeException(
                sprintf('Snapshot unable sets capture [%s] for file [%s]', $name, $filename)
            );
        }
        $this->captures[$name] = $filename;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setOutputDir(string $outputDir): SnapshotInterface
    {
        $this->outputDir = Filesystem::normalizePath($outputDir);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setOverwrite(bool $overwrite = true): SnapshotInterface
    {
        $this->overwrite = $overwrite;

        return $this;
    }
}
