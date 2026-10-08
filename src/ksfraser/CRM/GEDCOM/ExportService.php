<?php

declare(strict_types=1);

namespace Ksfraser\CRM\GEDCOM;

use Ksfraser\CRM\GEDCOM\Contract\EventRepositoryInterface;
use Ksfraser\CRM\GEDCOM\Contract\PersonRepositoryInterface;
use Ksfraser\CRM\GEDCOM\Contract\RelationshipRepositoryInterface;

class ExportService
{
    private PersonRepositoryInterface $personRepo;
    private RelationshipRepositoryInterface $relationshipRepo;
    private EventRepositoryInterface $eventRepo;
    private GedcomGenerator $generator;

    public function __construct(
        PersonRepositoryInterface $personRepo,
        RelationshipRepositoryInterface $relationshipRepo,
        EventRepositoryInterface $eventRepo
    ) {
        $this->personRepo = $personRepo;
        $this->relationshipRepo = $relationshipRepo;
        $this->eventRepo = $eventRepo;
        $this->generator = new GedcomGenerator();
    }

    /**
     * Export all persons, or a single person with their related network.
     */
    public function export(?int $personId = null): string
    {
        if ($personId !== null) {
            $personIds = $this->getRelatedPersonIds($personId);
        } else {
            $personIds = array_map(fn(array $p) => (int)$p['id'], $this->personRepo->findAll());
        }

        $persons = $this->buildPersons($personIds);
        $families = $this->buildFamilies($personIds);

        return $this->generator->generate($persons, $families);
    }

    private function getRelatedPersonIds(int $personId): array
    {
        $ids = [$personId];
        $related = $this->personRepo->findRelated($personId);
        foreach ($related as $row) {
            $ids[] = (int)$row['id'];
        }
        return array_unique($ids);
    }

    private function buildPersons(array $personIds): array
    {
        $persons = [];
        $xrefNum = 1;

        foreach ($personIds as $pid) {
            $row = $this->personRepo->findById($pid);
            if ($row === null) {
                continue;
            }

            $xref = 'I' . $xrefNum++;
            $birthEvent = null;
            $deathEvent = null;
            $otherEvents = [];

            $events = $this->eventRepo->findByEntity('person', $pid);
            foreach ($events as $ev) {
                $ed = new EventData(
                    $ev['event_type'] ?? '',
                    $ev['event_date'] ?? null,
                    $ev['event_place'] ?? null,
                    $ev['description'] ?? null
                );
                if ($ed->getType() === 'BIRT') {
                    $birthEvent = $ed;
                } elseif ($ed->getType() === 'DEAT') {
                    $deathEvent = $ed;
                } else {
                    $otherEvents[] = $ed;
                }
            }

            $persons[] = new PersonData(
                $xref,
                $row['first_name'] ?? '',
                $row['last_name'] ?? '',
                $row['sex'] ?? '',
                $birthEvent,
                $deathEvent,
                $otherEvents
            );
        }

        return $persons;
    }

    private function buildFamilies(array $personIds): array
    {
        $families = [];
        $relationships = $this->relationshipRepo->findByPersons($personIds);

        $spousePairs = [];
        $familyId = 1;

        foreach ($relationships as $rel) {
            if (($rel['relation_type'] ?? $rel['type'] ?? '') === 'spouse') {
                $a = (int)$rel['person_a_id'];
                $b = (int)$rel['person_b_id'];
                $key = $a < $b ? $a . '-' . $b : $b . '-' . $a;
                if (!isset($spousePairs[$key])) {
                    $spousePairs[$key] = [
                        'spouse_a_id' => $a,
                        'spouse_b_id' => $b,
                        'children' => [],
                    ];
                }
            }
        }

        foreach ($relationships as $rel) {
            if (($rel['relation_type'] ?? $rel['type'] ?? '') === 'parent') {
                $parentId = (int)$rel['person_a_id'];
                $childId = (int)$rel['person_b_id'];

                foreach ($spousePairs as $key => &$spair) {
                    if ($spair['spouse_a_id'] === $parentId || $spair['spouse_b_id'] === $parentId) {
                        if (!in_array($childId, $spair['children'])) {
                            $spair['children'][] = $childId;
                        }
                        continue 2;
                    }
                }
            }
        }

        foreach ($spousePairs as &$spair) {
            $aXref = $this->findPersonXref($spair['spouse_a_id']);
            $bXref = $this->findPersonXref($spair['spouse_b_id']);
            if ($aXref === null || $bXref === null) {
                continue;
            }

            $childrenXrefs = [];
            foreach ($spair['children'] as $childId) {
                $cXref = $this->findPersonXref($childId);
                if ($cXref !== null) {
                    $childrenXrefs[] = $cXref;
                }
            }

            $families[] = new FamilyData(
                'F' . $familyId++,
                $aXref,
                $bXref,
                $childrenXrefs
            );
        }

        return $families;
    }

    private array $idToXrefMap = [];

    private function findPersonXref(int $personId): ?string
    {
        if (isset($this->idToXrefMap[$personId])) {
            return $this->idToXrefMap[$personId];
        }

        $row = $this->personRepo->findById($personId);
        if ($row === null) {
            return null;
        }

        $xref = 'I' . (count($this->idToXrefMap) + 1);
        $this->idToXrefMap[$personId] = $xref;
        return $xref;
    }
}
