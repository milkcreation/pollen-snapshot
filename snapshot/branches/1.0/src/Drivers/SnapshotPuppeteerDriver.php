<?php

declare(strict_types=1);

namespace Pollen\Snapshot\Drivers;

use Pollen\Snapshot\AbstractSnapshotDriver;

class SnapshotPuppeteerDriver extends AbstractSnapshotDriver
{
    /**
     * @inheritDoc
     */
    public function getCommand(string $url, string $filename, string $format = 'img', array $options = []): array
    {
        return [
            'node',
            $this->getSnapshot()->resources('/assets/dist/js/drivers/puppeteer/node-snapshot.js'),
            $url,
            $filename,
            $format,
        ];
    }
}