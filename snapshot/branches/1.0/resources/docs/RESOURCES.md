# Liste de ressources utiles

## Puppeteer

- https://pptr.dev/
- https://dev.to/sagar/how-to-capture-screenshots-with-puppeteer-3mb2
- https://www.hebergementwebs.com/nouvelles/generation-de-pdf-de-haute-qualite-a-l-aide-de-marionnettiste

## HTML2CANVAS

- http://html2canvas.hertzen.com/

## BROWSERSHOT

- https://github.com/spatie/browsershot

## WkHTMLToPDF + Snappy

@see https://github.com/KnpLabs/snappy

```php
<?php

namespace App\Controller;

use Pollen\Snapshot\Lib\WkHtmlToPdf;
use Knp\Snappy\Pdf as SnappyPdf;
use Pollen\Http\ResponseInterface;
use Pollen\Http\StreamedResponse;
use Pollen\Http\StreamedResponseInterface;
use Pollen\Routing\BaseViewController;
use Pollen\Support\Proxy\AssetProxy;
use Pollen\WpPost\WpPostProxy;

class SnapshotController extends BaseViewController
{
    use AssetProxy;
    use WpPostProxy;

    /**
     * @param numeric $id
     *
     * @return ResponseInterface
     */
    public function snapshotArticleHtml($id): ResponseInterface
    {
        $this->datas(
            [
                'css' => [
                    $this->asset('api.snapshot.article-html.css'),
                ],
                'post'        => $this->wpPost()->get($id),
            ]
        );

        return $this->view('api/snapshot/index');
    }

    /**
     * @param numeric $id
     *
     * @return StreamedResponseInterface
     */
    public function snapshotPdf($id): StreamedResponseInterface
    {
        $response = new StreamedResponse();
        $disposition = $response->headers->makeDisposition('inline', 'snappy');
        $response->headers->replace(
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => $disposition,
            ]
        );

        $response->setCallback(
            function () use ($id) {
                $snappy = new SnappyPdf(realpath(WkHtmlToPdf::PATH));
                $snappy->setOption('disable-javascript', true);
                $stream = fopen('php://memory', 'rb+');
                $url = $this->router()->getNamedRouteUrl('api.snapshot.article_html', ['id' => $id], true);
                fwrite($stream, $snappy->getOutput($url));
                rewind($stream);
                fpassthru($stream);
                fclose($stream);
            }
        );

        return $response;
    }

    /**
     * @inheritDoc
     */
    protected function viewEngineDirectory(): string
    {
        return get_template_directory() . '/views/';
    }
}
```