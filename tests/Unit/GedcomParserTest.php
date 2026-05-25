<?php

declare(strict_types=1);

namespace Ksfraser\CRM\GEDCOM\Tests\Unit;

use Ksfraser\CRM\GEDCOM\GedcomParser;
use PHPUnit\Framework\TestCase;

class GedcomParserTest extends TestCase
{
    private GedcomParser $parser;

    protected function setUp(): void
    {
        $this->parser = new GedcomParser();
    }

    public function testParseSingleIndividual(): void
    {
        $gedcom = <<<GEDCOM
0 HEAD
1 SOUR TEST
0 @I1@ INDI
1 NAME John /Doe/
1 SEX M
1 BIRT
2 DATE 15 JAN 1900
2 PLAC New York
1 DEAT
2 DATE 30 JUN 1980
2 PLAC Boston
0 TRLR
GEDCOM;

        $data = $this->parser->parse($gedcom);

        $this->assertSame(1, $data->getPersonCount());
        $this->assertSame(0, $data->getFamilyCount());

        $person = $data->getPerson('I1');
        $this->assertNotNull($person);
        $this->assertSame('John', $person->getFirstName());
        $this->assertSame('Doe', $person->getLastName());
        $this->assertSame('M', $person->getSex());

        $birth = $person->getBirthEvent();
        $this->assertNotNull($birth);
        $this->assertSame('BIRT', $birth->getType());
        $this->assertSame('1900-01-15', $birth->getDate());
        $this->assertSame('New York', $birth->getPlace());

        $death = $person->getDeathEvent();
        $this->assertNotNull($death);
        $this->assertSame('DEAT', $death->getType());
        $this->assertSame('1980-06-30', $death->getDate());
        $this->assertSame('Boston', $death->getPlace());
    }

    public function testParseFamily(): void
    {
        $gedcom = <<<GEDCOM
0 HEAD
1 SOUR TEST
0 @I1@ INDI
1 NAME John /Doe/
1 SEX M
0 @I2@ INDI
1 NAME Jane /Doe/
1 SEX F
0 @I3@ INDI
1 NAME Child /Doe/
1 SEX M
0 @F1@ FAM
1 HUSB @I1@
1 WIFE @I2@
1 CHIL @I3@
1 MARR
2 DATE 1 MAY 1920
2 PLAC Chicago
0 TRLR
GEDCOM;

        $data = $this->parser->parse($gedcom);

        $this->assertSame(3, $data->getPersonCount());
        $this->assertSame(1, $data->getFamilyCount());

        $family = $data->getFamily('F1');
        $this->assertNotNull($family);
        $this->assertSame('I1', $family->getHusbandXref());
        $this->assertSame('I2', $family->getWifeXref());
        $this->assertSame(['I3'], $family->getChildXrefs());

        $marriage = $family->getMarriageEvent();
        $this->assertNotNull($marriage);
        $this->assertSame('1920-05-01', $marriage->getDate());
        $this->assertSame('Chicago', $marriage->getPlace());
    }

    public function testParseCustomTags(): void
    {
        $gedcom = <<<GEDCOM
0 HEAD
0 @I1@ INDI
1 NAME John /Doe/
1 OCCU Engineer
1 _EMPLOYER Acme Corp
1 _BENEFICIARY Charity Org
0 TRLR
GEDCOM;

        $data = $this->parser->parse($gedcom);

        $person = $data->getPerson('I1');
        $this->assertNotNull($person);
        $this->assertSame(['Engineer'], $person->getOccupations());
        $this->assertSame('Acme Corp', $person->getEmployer());
        $this->assertSame('Charity Org', $person->getBeneficiary());
    }

    public function testParseEmptyFile(): void
    {
        $data = $this->parser->parse('0 HEAD' . "\n" . '0 TRLR');

        $this->assertSame(0, $data->getPersonCount());
        $this->assertSame(0, $data->getFamilyCount());
    }

    public function testParseNameFormats(): void
    {
        $gedcom = <<<GEDCOM
0 HEAD
0 @I1@ INDI
1 NAME John Robert /Doe/
0 @I2@ INDI
1 NAME Jane /Smith-Jones/
0 TRLR
GEDCOM;

        $data = $this->parser->parse($gedcom);

        $p1 = $data->getPerson('I1');
        $this->assertSame('John Robert', $p1->getFirstName());
        $this->assertSame('Doe', $p1->getLastName());

        $p2 = $data->getPerson('I2');
        $this->assertSame('Jane', $p2->getFirstName());
        $this->assertSame('Smith-Jones', $p2->getLastName());
    }

    public function testParseFamilyReferences(): void
    {
        $gedcom = <<<GEDCOM
0 HEAD
0 @I1@ INDI
1 NAME John /Doe/
1 FAMS @F1@
1 FAMC @F2@
0 TRLR
GEDCOM;

        $data = $this->parser->parse($gedcom);

        $person = $data->getPerson('I1');
        $this->assertNotNull($person);
        $this->assertSame(['F1'], $person->getFamsRefs());
        $this->assertSame(['F2'], $person->getFamcRefs());
    }
}
