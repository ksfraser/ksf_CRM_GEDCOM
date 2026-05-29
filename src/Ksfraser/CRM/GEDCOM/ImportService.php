<?php

declare(strict_types=1);

namespace Ksfraser\CRM\GEDCOM;

use Ksfraser\CRM\GEDCOM\Contract\EventRepositoryInterface;
use Ksfraser\CRM\GEDCOM\Contract\PersonRepositoryInterface;
use Ksfraser\CRM\GEDCOM\Contract\RelationshipRepositoryInterface;

class ImportService
{
    private PersonRepositoryInterface $personRepo;
    private RelationshipRepositoryInterface $relationshipRepo;
    private EventRepositoryInterface $eventRepo;
    private ParsedGedcomData $data;
    private ImportResult $result;
    private array $xrefToPersonId;

    public function __construct(
        PersonRepositoryInterface $personRepo,
        RelationshipRepositoryInterface $relationshipRepo,
        EventRepositoryInterface $eventRepo
    ) {
        $this->personRepo = $personRepo;
        $this->relationshipRepo = $relationshipRepo;
        $this->eventRepo = $eventRepo;
    }

    public function import(ParsedGedcomData $data): ImportResult
    {
        $this->data = $data;
        $this->result = new ImportResult();
        $this->xrefToPersonId = [];

        $this->importPersons();

        $this->importFamilies();

        $this->resolveDeferredRelationships();

        return $this->result;
    }

    private function importPersons(): void
    {
        foreach ($this->data->getPersons() as $person) {
            $personId = $this->personRepo->findOrCreate(
                $person->getXref(),
                $person->getFirstName(),
                $person->getLastName(),
                $person->getSex(),
                $person->getNotes()
            );

            $this->xrefToPersonId[$person->getXref()] = $personId;
            $this->result->addIndividual();

            $birthEvent = $person->getBirthEvent();
            if ($birthEvent !== null) {
                $this->eventRepo->create('person', $personId, 'BIRT',
                    $birthEvent->getDate(), $birthEvent->getPlace(), 'BIRT');
                $this->result->addLifeEvent();
            }

            $deathEvent = $person->getDeathEvent();
            if ($deathEvent !== null) {
                $this->eventRepo->create('person', $personId, 'DEAT',
                    $deathEvent->getDate(), $deathEvent->getPlace(), 'DEAT');
                $this->result->addLifeEvent();
            }

            foreach ($person->getOccupations() as $occ) {
                $this->result->addRole();
            }
        }
    }

    private function importFamilies(): void
    {
        foreach ($this->data->getFamilies() as $family) {
            $this->result->addFamily();

            $husbandId = $family->getHusbandXref() !== null
                ? ($this->xrefToPersonId[$family->getHusbandXref()] ?? null)
                : null;
            $wifeId = $family->getWifeXref() !== null
                ? ($this->xrefToPersonId[$family->getWifeXref()] ?? null)
                : null;

            if ($husbandId !== null && $wifeId !== null) {
                $marriageEvent = $family->getMarriageEvent();
                $this->relationshipRepo->create(
                    $husbandId, $wifeId, 'spouse',
                    $marriageEvent !== null ? $marriageEvent->getDate() : null,
                    null,
                    $marriageEvent !== null ? $marriageEvent->getDescription() : null
                );
                $this->result->addRelationship();

                $marriageEvent = $family->getMarriageEvent();
                if ($marriageEvent !== null) {
                    $this->eventRepo->create('person', $husbandId, 'MARR',
                        $marriageEvent->getDate(), $marriageEvent->getPlace(), 'MARR');
                    $this->eventRepo->create('person', $wifeId, 'MARR',
                        $marriageEvent->getDate(), $marriageEvent->getPlace(), 'MARR');
                    $this->result->addLifeEvent();
                    $this->result->addLifeEvent();
                }
            }

            foreach ($family->getChildXrefs() as $childXref) {
                $childId = $this->xrefToPersonId[$childXref] ?? null;
                if ($childId === null) {
                    continue;
                }

                if ($husbandId !== null) {
                    $this->relationshipRepo->create($husbandId, $childId, 'parent');
                    $this->relationshipRepo->create($childId, $husbandId, 'child');
                    $this->result->addRelationship();
                    $this->result->addRelationship();
                }

                if ($wifeId !== null) {
                    $this->relationshipRepo->create($wifeId, $childId, 'parent');
                    $this->relationshipRepo->create($childId, $wifeId, 'child');
                    $this->result->addRelationship();
                    $this->result->addRelationship();
                }
            }
        }
    }

    private function resolveDeferredRelationships(): void
    {
        // Future: handle deferred FAMS/FAMC resolution
        // for persons referenced by families not yet parsed.
    }
}
