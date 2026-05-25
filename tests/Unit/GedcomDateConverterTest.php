<?php

declare(strict_types=1);

namespace Ksfraser\CRM\GEDCOM\Tests\Unit;

use Ksfraser\CRM\GEDCOM\GedcomDateConverter;
use PHPUnit\Framework\TestCase;

class GedcomDateConverterTest extends TestCase
{
    private GedcomDateConverter $converter;

    protected function setUp(): void
    {
        $this->converter = new GedcomDateConverter();
    }

    public function testParseFullDate(): void
    {
        $this->assertSame('1900-01-15', $this->converter->parseGedcomDate('15 JAN 1900'));
        $this->assertSame('1920-12-25', $this->converter->parseGedcomDate('25 DEC 1920'));
    }

    public function testParseMonthYear(): void
    {
        $this->assertSame('1900-03-01', $this->converter->parseGedcomDate('MAR 1900'));
    }

    public function testParseYearOnly(): void
    {
        $this->assertSame('1900-01-01', $this->converter->parseGedcomDate('1900'));
    }

    public function testParseIsoDate(): void
    {
        $this->assertSame('1900-01-15', $this->converter->parseGedcomDate('1900-01-15'));
    }

    public function testParseWithPrefix(): void
    {
        $this->assertSame('1900-01-15', $this->converter->parseGedcomDate('ABT 15 JAN 1900'));
        $this->assertSame('1900-01-15', $this->converter->parseGedcomDate('BEF 15 JAN 1900'));
        $this->assertSame('1900-01-15', $this->converter->parseGedcomDate('AFT 15 JAN 1900'));
    }

    public function testParseInvalid(): void
    {
        $this->assertNull($this->converter->parseGedcomDate('invalid date'));
        $this->assertNull($this->converter->parseGedcomDate(''));
    }

    public function testFormatGedcomDate(): void
    {
        $result = $this->converter->formatGedcomDate('1900-01-15');
        $this->assertSame('15 JAN 1900', $result);
    }
}
