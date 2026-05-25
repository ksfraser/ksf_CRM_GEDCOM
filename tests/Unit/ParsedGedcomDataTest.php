<?php

declare(strict_types=1);

namespace Ksfraser\CRM\GEDCOM\Tests\Unit;

use Ksfraser\CRM\GEDCOM\FamilyData;
use Ksfraser\CRM\GEDCOM\ParsedGedcomData;
use Ksfraser\CRM\GEDCOM\PersonData;
use PHPUnit\Framework\TestCase;

class ParsedGedcomDataTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $persons = [
            new PersonData('I1', 'John', 'Doe'),
            new PersonData('I2', 'Jane', 'Doe'),
        ];
        $families = [
            new FamilyData('F1', 'I1', 'I2', ['I3']),
        ];

        $data = new ParsedGedcomData($persons, $families);

        $this->assertCount(2, $data->getPersons());
        $this->assertCount(1, $data->getFamilies());
        $this->assertSame(2, $data->getPersonCount());
        $this->assertSame(1, $data->getFamilyCount());
    }

    public function testGetPersonByXref(): void
    {
        $persons = [new PersonData('I1', 'John', 'Doe')];
        $data = new ParsedGedcomData($persons);

        $person = $data->getPerson('I1');
        $this->assertNotNull($person);
        $this->assertSame('John', $person->getFirstName());

        $this->assertNull($data->getPerson('I999'));
    }

    public function testGetFamilyByXref(): void
    {
        $families = [new FamilyData('F1', 'I1', 'I2')];
        $data = new ParsedGedcomData([], $families);

        $family = $data->getFamily('F1');
        $this->assertNotNull($family);
        $this->assertSame('I1', $family->getHusbandXref());

        $this->assertNull($data->getFamily('F999'));
    }

    public function testAddPerson(): void
    {
        $data = new ParsedGedcomData();
        $data->addPerson(new PersonData('I5', 'Alice'));

        $this->assertCount(1, $data->getPersons());
        $this->assertNotNull($data->getPerson('I5'));
    }

    public function testAddFamily(): void
    {
        $data = new ParsedGedcomData();
        $data->addFamily(new FamilyData('F5', 'I1', 'I2'));

        $this->assertCount(1, $data->getFamilies());
        $this->assertNotNull($data->getFamily('F5'));
    }
}
