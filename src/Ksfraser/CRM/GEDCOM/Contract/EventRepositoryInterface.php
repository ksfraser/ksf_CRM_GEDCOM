<?php

declare(strict_types=1);

namespace Ksfraser\CRM\GEDCOM\Contract;

interface EventRepositoryInterface
{
    public function create(
        string $entityType,
        int $entityId,
        string $eventType,
        ?string $eventDate = null,
        ?string $eventPlace = null,
        ?string $gedcomTag = null,
        ?string $details = null
    ): int;

    public function findByEntity(string $entityType, int $entityId): array;
}
