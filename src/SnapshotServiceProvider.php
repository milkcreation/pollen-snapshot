<?php

declare(strict_types=1);

namespace Pollen\Snapshot;

use Pollen\Snapshot\Drivers\SnapshotPuppeteerDriver;
use Pollen\Container\BaseServiceProvider;

class SnapshotServiceProvider extends BaseServiceProvider
{
    /**
     * @var string[]
     */
    protected $provides = [
        SnapshotInterface::class,
        SnapshotDriverInterface::class
    ];

    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->getContainer()->add(SnapshotInterface::class, function () {
            return new Snapshot(
                $this->getContainer()->get(SnapshotDriverInterface::class),
                $this->getContainer()
            );
        });

        $this->getContainer()->add(SnapshotDriverInterface::class, function () {
            return new SnapshotPuppeteerDriver();
        });
    }
}
