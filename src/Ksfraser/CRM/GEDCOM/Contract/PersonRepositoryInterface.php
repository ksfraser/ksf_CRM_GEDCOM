<?php

declare(strict_types=1);

namespace Ksfraser\CRM\GEDCOM\Contract;

interface PersonRepositoryInterface
{
    public function findOrCreate(string $xref, string $firstName, string $lastName, string $sex, string $notes): int;

    public function findById(int $id): ?array;

    public function findAll(): array;

    public function findRelated(int $personId): array;

    public function findByXref(string $xref): ?array;
}
