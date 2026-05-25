<?php

declare(strict_types=1);

namespace Ksfraser\CRM\GEDCOM\Tests\Unit;

use Ksfraser\CRM\GEDCOM\EventData;
use Ksfraser\CRM\GEDCOM\PersonData;
use PHPUnit\Framework\TestCase;

class PersonDataTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $birth = new EventData('BIRT', '1900-01-15');
        $death = new EventData('DEAT', '1980-06-30');
        $otherEvents = [new EventData('BAPM', '1900-02-10')];

        $person = new PersonData(
            'I1', 'John', 'Doe', 'M', $birth, $death, $otherEvents,
            ['F1'], ['F2'], 'Some notes', ['Engineer'], 'Acme Corp', 'Charity Org'
        );

        $this->assertSame('I1', $person->getXref());
        $this->assertSame('John', $person->getFirstName());
        $this->assertSame('Doe', $person->getLastName());
        $this->assertSame('John Doe', $person->getFullName());
        $this->assertSame('M', $person->getSex());
        $this->assertSame($birth, $person->getBirthEvent());
        $this->assertSame($death, $person->getDeathEvent());
        $this->assertCount(1, $person->getEvents());
        $this->assertSame(['F1'], $person->getFamsRefs());
        $this->assertSame(['F2'], $person->getFamcRefs());
        $this->assertSame('Some notes', $person->getNotes());
        $this->assertSame(['Engineer'], $person->getOccupations());
        $this->assertSame('Acme Corp', $person->getEmployer());
        $this->assertSame('Charity Org', $person->getBeneficiary());
    }

    public function testToArray(): void
    {
        $person = new PersonData('I1', 'Jane', 'Smith', 'F');

        $array = $person->toArray();

        $this->assertSame('I1', $array['xref']);
        $this->assertSame('Jane', $array['first_name']);
        $this->assertSame('Smith', $array['last_name']);
        $this->assertSame('F', $array['sex']);
    }

    public function testFromArray(): void
    {
        $data = [
            'xref' => 'I2',
            'first_name' => 'Bob',
            'last_name' => 'Jones',
            'sex' => 'M',
            'birth_event' => null,
            'death_event' => null,
            'events' => [],
            'fams_refs' => ['F1'],
            'famc_refs' => [],
            'notes' => '',
            'occupations' => ['Doctor'],
            'employer' => 'Hospital',
            'beneficiary' => null,
        ];

        $person = PersonData::fromArray($data);

        $this->assertSame('I2', $person->getXref());
        $this->assertSame('Bob', $person->getFirstName());
        $this->assertSame('Jones', $person->getLastName());
        $this->assertSame('M', $person->getSex());
        $this->assertSame(['F1'], $person->getFamsRefs());
        $this->assertSame(['Doctor'], $person->getOccupations());
    }
}
