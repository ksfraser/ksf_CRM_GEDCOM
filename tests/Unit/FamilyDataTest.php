<?php

declare(strict_types=1);

namespace Ksfraser\CRM\GEDCOM\Tests\Unit;

use Ksfraser\CRM\GEDCOM\EventData;
use Ksfraser\CRM\GEDCOM\FamilyData;
use PHPUnit\Framework\TestCase;

class FamilyDataTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $marriage = new EventData('MARR', '1920-05-01', 'Chicago');
        $family = new FamilyData('F1', 'I1', 'I2', ['I3', 'I4'], $marriage);

        $this->assertSame('F1', $family->getXref());
        $this->assertSame('I1', $family->getHusbandXref());
        $this->assertSame('I2', $family->getWifeXref());
        $this->assertSame(['I3', 'I4'], $family->getChildXrefs());
        $this->assertSame($marriage, $family->getMarriageEvent());
        $this->assertSame(['I1', 'I2'], $family->getSpouseXrefs());
    }

    public function testToArray(): void
    {
        $family = new FamilyData('F1', 'I1', null, ['I3']);

        $array = $family->toArray();

        $this->assertSame('F1', $array['xref']);
        $this->assertSame('I1', $array['husband_xref']);
        $this->assertNull($array['wife_xref']);
        $this->assertSame(['I3'], $array['child_xrefs']);
    }

    public function testFromArray(): void
    {
        $data = [
            'xref' => 'F2',
            'husband_xref' => 'I5',
            'wife_xref' => 'I6',
            'child_xrefs' => ['I7'],
            'marriage_event' => null,
        ];

        $family = FamilyData::fromArray($data);

        $this->assertSame('F2', $family->getXref());
        $this->assertSame('I5', $family->getHusbandXref());
        $this->assertSame('I6', $family->getWifeXref());
        $this->assertSame(['I7'], $family->getChildXrefs());
    }
}
