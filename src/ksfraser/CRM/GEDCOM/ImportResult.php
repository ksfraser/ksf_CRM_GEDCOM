<?php

declare(strict_types=1);

namespace Ksfraser\CRM\GEDCOM;

class ImportResult
{
    private int $individuals;
    private int $families;
    private int $lifeEvents;
    private int $relationships;
    private int $roles;
    private array $errors;

    public function __construct(
        int $individuals = 0,
        int $families = 0,
        int $lifeEvents = 0,
        int $relationships = 0,
        int $roles = 0,
        array $errors = []
    ) {
        $this->individuals = $individuals;
        $this->families = $families;
        $this->lifeEvents = $lifeEvents;
        $this->relationships = $relationships;
        $this->roles = $roles;
        $this->errors = $errors;
    }

    public function getIndividuals(): int { return $this->individuals; }
    public function getFamilies(): int { return $this->families; }
    public function getLifeEvents(): int { return $this->lifeEvents; }
    public function getRelationships(): int { return $this->relationships; }
    public function getRoles(): int { return $this->roles; }
    public function getErrors(): array { return $this->errors; }

    public function addIndividual(): void { $this->individuals++; }
    public function addFamily(): void { $this->families++; }
    public function addLifeEvent(): void { $this->lifeEvents++; }
    public function addRelationship(): void { $this->relationships++; }
    public function addRole(): void { $this->roles++; }
    public function addError(string $error): void { $this->errors[] = $error; }

    public function toArray(): array
    {
        return [
            'individuals' => $this->individuals,
            'families' => $this->families,
            'life_events' => $this->lifeEvents,
            'relationships' => $this->relationships,
            'roles' => $this->roles,
            'errors' => $this->errors,
        ];
    }
}
