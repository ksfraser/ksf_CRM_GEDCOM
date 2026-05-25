<?php

declare(strict_types=1);

namespace Ksfraser\CRM\GEDCOM;

class EventData
{
    private string $type;
    private ?string $date;
    private ?string $place;
    private ?string $description;

    public function __construct(
        string $type,
        ?string $date = null,
        ?string $place = null,
        ?string $description = null
    ) {
        $this->type = $type;
        $this->date = $date;
        $this->place = $place;
        $this->description = $description;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function getPlace(): ?string
    {
        return $this->place;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'date' => $this->date,
            'place' => $this->place,
            'description' => $this->description,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['type'] ?? '',
            $data['date'] ?? null,
            $data['place'] ?? null,
            $data['description'] ?? null
        );
    }
}
