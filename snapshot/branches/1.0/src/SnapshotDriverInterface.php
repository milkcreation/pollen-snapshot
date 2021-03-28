<?php

declare(strict_types=1);

namespace Pollen\Snapshot;

interface SnapshotDriverInterface
{
    /**
     * Récupération de la liste des arguments de la commande.
     *
     * @param string $url
     * @param string $filename
     * @param string $format
     * @param array $options
     *
     * @return array
     */
    public function getCommand(string $url, string $filename, string $format = 'img', array $options = []): array;

    /**
     * Récupération de l'instance du gestionnaire de capture.
     *
     * @return SnapshotInterface
     */
    public function getSnapshot(): SnapshotInterface;

    /**
     * Définition de l'instance du gestionnaire de capture.
     *
     * @param SnapshotInterface $snapshot
     *
     * @return SnapshotDriverInterface
     */
    public function setSnapshot(SnapshotInterface $snapshot): SnapshotDriverInterface;
}