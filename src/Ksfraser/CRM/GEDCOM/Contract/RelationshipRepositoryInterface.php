<?php

declare(strict_types=1);

namespace Ksfraser\CRM\GEDCOM\Contract;

interface RelationshipRepositoryInterface
{
    public function create(
        int $personAId,
        int $personBId,
        string $type,
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $details = null
    ): int;

    public function findByPerson(int $personId): array;

    public function findByPersons(array $personIds): array;
}
