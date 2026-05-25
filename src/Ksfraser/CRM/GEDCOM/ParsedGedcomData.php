<?php

declare(strict_types=1);

namespace Ksfraser\CRM\GEDCOM;

class ParsedGedcomData
{
    private array $persons;
    private array $families;

    /**
     * @param PersonData[] $persons
     * @param FamilyData[] $families
     */
    public function __construct(array $persons = [], array $families = [])
    {
        $this->persons = [];
        foreach ($persons as $p) {
            $this->persons[$p->getXref()] = $p;
        }
        $this->families = [];
        foreach ($families as $f) {
            $this->families[$f->getXref()] = $f;
        }
    }

    public function getPersons(): array
    {
        return array_values($this->persons);
    }

    public function getPerson(string $xref): ?PersonData
    {
        return $this->persons[$xref] ?? null;
    }

    public function getFamilies(): array
    {
        return array_values($this->families);
    }

    public function getFamily(string $xref): ?FamilyData
    {
        return $this->families[$xref] ?? null;
    }

    public function getPersonCount(): int
    {
        return count($this->persons);
    }

    public function getFamilyCount(): int
    {
        return count($this->families);
    }

    public function addPerson(PersonData $person): void
    {
        $this->persons[$person->getXref()] = $person;
    }

    public function addFamily(FamilyData $family): void
    {
        $this->families[$family->getXref()] = $family;
    }
}
