<?php

declare(strict_types=1);

namespace Pollen\Snapshot;

use Pollen\Http\ResponseInterface;
use RuntimeException;

interface SnapshotInterface
{
    /**
     * Récupération de la liste des captures.
     *
     * @return array
     */
    public function all(): array;

    /**
     * Récupération de la réponse HTTP d'affichage d'une capture générée.
     *
     * @param string|null $name
     *
     * @return ResponseInterface
     */
    public function displayResponse(?string $name = null): ResponseInterface;

    /**
     * Récupération de la réponse HTTP de téléchargement d'une capture générée.
     *
     * @param string|null $name
     *
     * @return ResponseInterface
     */
    public function downloadResponse(?string $name = null): ResponseInterface;

    /**
     * Récupération du chemin absolue vers la dernière capture.
     *
     * @param string|null $name
     *
     * @return string|null
     */
    public function get(?string $name = null): ?string;

    /**
     * Récupération du chemin absolu vers une ressource du répertoire de sortie.
     *
     * @param string $path
     *
     * @return string
     */
    public function getOutputPath(string $path = ''): string;

    /**
     * Exécution d'une capture au format image.
     *
     * @param string $url
     * @param string|null $name
     *
     * @return SnapshotInterface
     */
    public function img(string $url, ?string $name = null): SnapshotInterface;

    /**
     * Exécution d'une capture au format PDF.
     *
     * @param string $url
     * @param string|null $name
     *
     * @return SnapshotInterface
     */
    public function pdf(string $url, ?string $name = null): SnapshotInterface;

    /**
     * Chemin absolu vers une ressource (fichier|répertoire).
     *
     * @param string|null $path Chemin relatif vers la ressource.
     *
     * @return string
     */
    public function resources(?string $path = null): string;

    /**
     * Définition d'une capture.
     *
     * @param string $name
     * @param string $filename
     *
     * @return static
     *
     * @throws RuntimeException
     */
    public function setCapture(string $name, string $filename): SnapshotInterface;

    /**
     * Définition du chemin absolu vers le répertoire des ressources.
     *
     * @param string $outputDir
     *
     * @return static
     */
    public function setOutputDir(string $outputDir): SnapshotInterface;

    /**
     * Définition de la réécriture des captures existantes.
     *
     * @param bool $overwrite
     *
     * @return static
     */
    public function setOverwrite(bool $overwrite = true): SnapshotInterface;

    /**
     * Définition du chemin absolu vers le répertoire des ressources.
     *
     * @param string $resourceBaseDir
     *
     * @return static
     */
    public function setResourcesBaseDir(string $resourceBaseDir): SnapshotInterface;
}
