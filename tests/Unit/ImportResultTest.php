<?php

declare(strict_types=1);

namespace Ksfraser\CRM\GEDCOM\Tests\Unit;

use Ksfraser\CRM\GEDCOM\ImportResult;
use PHPUnit\Framework\TestCase;

class ImportResultTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $result = new ImportResult();

        $this->assertSame(0, $result->getIndividuals());
        $this->assertSame(0, $result->getFamilies());
        $this->assertSame(0, $result->getLifeEvents());
        $this->assertSame(0, $result->getRelationships());
        $this->assertSame(0, $result->getRoles());
        $this->assertEmpty($result->getErrors());
    }

    public function testAddMethods(): void
    {
        $result = new ImportResult();
        $result->addIndividual();
        $result->addIndividual();
        $result->addFamily();
        $result->addLifeEvent();
        $result->addRelationship();
        $result->addRole();
        $result->addError('Something went wrong');

        $this->assertSame(2, $result->getIndividuals());
        $this->assertSame(1, $result->getFamilies());
        $this->assertSame(1, $result->getLifeEvents());
        $this->assertSame(1, $result->getRelationships());
        $this->assertSame(1, $result->getRoles());
        $this->assertCount(1, $result->getErrors());
        $this->assertSame('Something went wrong', $result->getErrors()[0]);
    }

    public function testToArray(): void
    {
        $result = new ImportResult(2, 1, 3, 4, 5, ['err1']);

        $array = $result->toArray();

        $this->assertSame(2, $array['individuals']);
        $this->assertSame(1, $array['families']);
        $this->assertSame(3, $array['life_events']);
        $this->assertSame(4, $array['relationships']);
        $this->assertSame(5, $array['roles']);
        $this->assertSame(['err1'], $array['errors']);
    }
}
