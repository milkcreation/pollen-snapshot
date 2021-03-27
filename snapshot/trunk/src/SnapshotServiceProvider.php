<?php

declare(strict_types=1);

namespace Pollen\Snapshot;

use Pollen\Container\BaseServiceProvider;

class SnapshotServiceProvider extends BaseServiceProvider
{
    /**
     * @var string[]
     */
    protected $provides = [
        SnapshotInterface::class
    ];

    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->getContainer()->add(SnapshotInterface::class, function () {
            return new Snapshot();
        });
    }
}
