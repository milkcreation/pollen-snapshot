<?php

declare(strict_types=1);

namespace Pollen\Snapshot;

abstract class AbstractSnapshotDriver implements SnapshotDriverInterface
{
    /**
     * Instance du gestionnaire de capture.
     * @var SnapshotInterface
     */
    private $snapshot;

    /**
     * @inheritDoc
     */
    abstract public function getCommand(string $url, string $filename, string $format = 'img', array $options = []): array;

    /**
     * Récupération de l'instance du gestionnaire de capture.
     *
     * @return SnapshotInterface
     */
    public function getSnapshot(): SnapshotInterface
    {
        if ($this->snapshot === null) {
            $this->snapshot = new Snapshot();
        }

        return $this->snapshot;
    }

    /**
     * Définition de l'instance du gestionnaire de capture.
     *
     * @param SnapshotInterface $snapshot
     *
     * @return SnapshotDriverInterface
     */
    public function setSnapshot(SnapshotInterface $snapshot): SnapshotDriverInterface
    {
        $this->snapshot = $snapshot;

        return $this;
    }
}