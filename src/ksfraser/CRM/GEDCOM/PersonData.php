<?php

declare(strict_types=1);

namespace Ksfraser\CRM\GEDCOM;

class PersonData
{
    private string $xref;
    private string $firstName;
    private string $lastName;
    private string $sex;
    private ?EventData $birthEvent;
    private ?EventData $deathEvent;
    private array $events;
    private array $famsRefs;
    private array $famcRefs;
    private string $notes;
    private array $occupations;
    private ?string $employer;
    private ?string $beneficiary;

    public function __construct(
        string $xref,
        string $firstName = '',
        string $lastName = '',
        string $sex = '',
        ?EventData $birthEvent = null,
        ?EventData $deathEvent = null,
        array $events = [],
        array $famsRefs = [],
        array $famcRefs = [],
        string $notes = '',
        array $occupations = [],
        ?string $employer = null,
        ?string $beneficiary = null
    ) {
        $this->xref = $xref;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->sex = $sex;
        $this->birthEvent = $birthEvent;
        $this->deathEvent = $deathEvent;
        $this->events = $events;
        $this->famsRefs = $famsRefs;
        $this->famcRefs = $famcRefs;
        $this->notes = $notes;
        $this->occupations = $occupations;
        $this->employer = $employer;
        $this->beneficiary = $beneficiary;
    }

    public function getXref(): string { return $this->xref; }
    public function getFirstName(): string { return $this->firstName; }
    public function getLastName(): string { return $this->lastName; }
    public function getFullName(): string { return trim($this->firstName . ' ' . $this->lastName); }
    public function getSex(): string { return $this->sex; }
    public function getBirthEvent(): ?EventData { return $this->birthEvent; }
    public function getDeathEvent(): ?EventData { return $this->deathEvent; }
    public function getEvents(): array { return $this->events; }
    public function getFamsRefs(): array { return $this->famsRefs; }
    public function getFamcRefs(): array { return $this->famcRefs; }
    public function getNotes(): string { return $this->notes; }
    public function getOccupations(): array { return $this->occupations; }
    public function getEmployer(): ?string { return $this->employer; }
    public function getBeneficiary(): ?string { return $this->beneficiary; }

    public function toArray(): array
    {
        return [
            'xref' => $this->xref,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'sex' => $this->sex,
            'birth_event' => $this->birthEvent ? $this->birthEvent->toArray() : null,
            'death_event' => $this->deathEvent ? $this->deathEvent->toArray() : null,
            'events' => array_map(fn(EventData $e) => $e->toArray(), $this->events),
            'fams_refs' => $this->famsRefs,
            'famc_refs' => $this->famcRefs,
            'notes' => $this->notes,
            'occupations' => $this->occupations,
            'employer' => $this->employer,
            'beneficiary' => $this->beneficiary,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['xref'] ?? '',
            $data['first_name'] ?? '',
            $data['last_name'] ?? '',
            $data['sex'] ?? '',
            isset($data['birth_event']) ? EventData::fromArray($data['birth_event']) : null,
            isset($data['death_event']) ? EventData::fromArray($data['death_event']) : null,
            array_map(fn(array $e) => EventData::fromArray($e), $data['events'] ?? []),
            $data['fams_refs'] ?? [],
            $data['famc_refs'] ?? [],
            $data['notes'] ?? '',
            $data['occupations'] ?? [],
            $data['employer'] ?? null,
            $data['beneficiary'] ?? null
        );
    }
}
