<?php

declare(strict_types=1);

namespace Ksfraser\CRM\GEDCOM\Tests\Unit;

use Ksfraser\CRM\GEDCOM\EventData;
use PHPUnit\Framework\TestCase;

class EventDataTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $event = new EventData('BIRT', '1900-01-15', 'New York', 'Birth of John');

        $this->assertSame('BIRT', $event->getType());
        $this->assertSame('1900-01-15', $event->getDate());
        $this->assertSame('New York', $event->getPlace());
        $this->assertSame('Birth of John', $event->getDescription());
    }

    public function testToArray(): void
    {
        $event = new EventData('DEAT', '1950-06-30', 'Boston', 'Death of John');

        $array = $event->toArray();

        $this->assertSame('DEAT', $array['type']);
        $this->assertSame('1950-06-30', $array['date']);
        $this->assertSame('Boston', $array['place']);
        $this->assertSame('Death of John', $array['description']);
    }

    public function testFromArray(): void
    {
        $data = [
            'type' => 'MARR',
            'date' => '1920-05-01',
            'place' => 'Chicago',
            'description' => 'Wedding',
        ];

        $event = EventData::fromArray($data);

        $this->assertSame('MARR', $event->getType());
        $this->assertSame('1920-05-01', $event->getDate());
        $this->assertSame('Chicago', $event->getPlace());
        $this->assertSame('Wedding', $event->getDescription());
    }
}
