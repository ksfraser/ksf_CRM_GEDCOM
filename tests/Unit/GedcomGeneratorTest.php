<?php

declare(strict_types=1);

namespace Ksfraser\CRM\GEDCOM\Tests\Unit;

use Ksfraser\CRM\GEDCOM\EventData;
use Ksfraser\CRM\GEDCOM\FamilyData;
use Ksfraser\CRM\GEDCOM\GedcomGenerator;
use Ksfraser\CRM\GEDCOM\PersonData;
use PHPUnit\Framework\TestCase;

class GedcomGeneratorTest extends TestCase
{
    private GedcomGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new GedcomGenerator();
    }

    public function testGenerateWithSinglePerson(): void
    {
        $persons = [
            new PersonData('I1', 'John', 'Doe', 'M',
                new EventData('BIRT', '1900-01-15', 'New York'),
                new EventData('DEAT', '1980-06-30', 'Boston')
            ),
        ];

        $output = $this->generator->generate($persons, []);

        $this->assertStringContainsString('0 HEAD', $output);
        $this->assertStringContainsString('0 @I1@ INDI', $output);
        $this->assertStringContainsString('1 NAME John /Doe/', $output);
        $this->assertStringContainsString('1 SEX M', $output);
        $this->assertStringContainsString('1 BIRT', $output);
        $this->assertStringContainsString('2 DATE 15 JAN 1900', $output);
        $this->assertStringContainsString('2 PLAC New York', $output);
        $this->assertStringContainsString('1 DEAT', $output);
        $this->assertStringContainsString('2 DATE 30 JUN 1980', $output);
        $this->assertStringContainsString('2 PLAC Boston', $output);
        $this->assertStringContainsString('0 TRLR', $output);
    }

    public function testGenerateWithFamily(): void
    {
        $persons = [
            new PersonData('I1', 'John', 'Doe', 'M'),
            new PersonData('I2', 'Jane', 'Doe', 'F'),
            new PersonData('I3', 'Child', 'Doe', 'M'),
        ];

        $families = [
            new FamilyData('F1', 'I1', 'I2', ['I3'],
                new EventData('MARR', '1920-05-01', 'Chicago')
            ),
        ];

        $output = $this->generator->generate($persons, $families);

        $this->assertStringContainsString('0 @F1@ FAM', $output);
        $this->assertStringContainsString('1 HUSB @I1@', $output);
        $this->assertStringContainsString('1 WIFE @I2@', $output);
        $this->assertStringContainsString('1 CHIL @I3@', $output);
        $this->assertStringContainsString('1 MARR', $output);
        $this->assertStringContainsString('2 DATE 01 MAY 1920', $output);
    }

    public function testGenerateWithMultiplePersons(): void
    {
        $persons = [
            new PersonData('I1', 'Alice', 'Smith', 'F'),
            new PersonData('I2', 'Bob', 'Smith', 'M'),
        ];

        $output = $this->generator->generate($persons, []);

        $this->assertStringContainsString('@I1@', $output);
        $this->assertStringContainsString('@I2@', $output);
        $this->assertStringContainsString('Alice', $output);
        $this->assertStringContainsString('Bob', $output);
    }

    public function testGenerateEmpty(): void
    {
        $output = $this->generator->generate([], []);

        $this->assertStringContainsString('0 HEAD', $output);
        $this->assertStringContainsString('0 TRLR', $output);
        $this->assertStringNotContainsString('INDI', $output);
        $this->assertStringNotContainsString('FAM', $output);
    }

    public function testGenerateWithSex(): void
    {
        $persons = [
            new PersonData('I1', 'Test', 'Person', 'F'),
        ];

        $output = $this->generator->generate($persons, []);

        $this->assertStringContainsString('1 SEX F', $output);
    }

    public function testGenerateWithOccupationsAndCustomTags(): void
    {
        $persons = [
            new PersonData('I1', 'John', 'Doe', 'M', null, null, [],
                [], [], '', ['Engineer'], 'Acme Corp', 'Charity Org'),
        ];

        $output = $this->generator->generate($persons, []);

        $this->assertStringContainsString('1 OCCU Engineer', $output);
        $this->assertStringContainsString('1 _EMPLOYER Acme Corp', $output);
        $this->assertStringContainsString('1 _BENEFICIARY Charity Org', $output);
    }
}
