<?php

declare(strict_types=1);

namespace Ksfraser\CRM\GEDCOM;

class FamilyData
{
    private string $xref;
    private ?string $husbandXref;
    private ?string $wifeXref;
    private array $childXrefs;
    private ?EventData $marriageEvent;

    public function __construct(
        string $xref,
        ?string $husbandXref = null,
        ?string $wifeXref = null,
        array $childXrefs = [],
        ?EventData $marriageEvent = null
    ) {
        $this->xref = $xref;
        $this->husbandXref = $husbandXref;
        $this->wifeXref = $wifeXref;
        $this->childXrefs = $childXrefs;
        $this->marriageEvent = $marriageEvent;
    }

    public function getXref(): string { return $this->xref; }
    public function getHusbandXref(): ?string { return $this->husbandXref; }
    public function getWifeXref(): ?string { return $this->wifeXref; }
    public function getChildXrefs(): array { return $this->childXrefs; }
    public function getMarriageEvent(): ?EventData { return $this->marriageEvent; }
    public function getSpouseXrefs(): array
    {
        return array_filter([$this->husbandXref, $this->wifeXref]);
    }

    public function toArray(): array
    {
        return [
            'xref' => $this->xref,
            'husband_xref' => $this->husbandXref,
            'wife_xref' => $this->wifeXref,
            'child_xrefs' => $this->childXrefs,
            'marriage_event' => $this->marriageEvent ? $this->marriageEvent->toArray() : null,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['xref'] ?? '',
            $data['husband_xref'] ?? null,
            $data['wife_xref'] ?? null,
            $data['child_xrefs'] ?? [],
            isset($data['marriage_event']) ? EventData::fromArray($data['marriage_event']) : null
        );
    }
}
