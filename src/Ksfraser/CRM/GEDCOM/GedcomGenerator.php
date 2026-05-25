<?php

declare(strict_types=1);

namespace Ksfraser\CRM\GEDCOM;

class GedcomGenerator
{
    private GedcomDateConverter $dateConverter;

    public function __construct()
    {
        $this->dateConverter = new GedcomDateConverter();
    }

    /**
     * @param PersonData[] $persons
     * @param FamilyData[] $families
     */
    public function generate(array $persons, array $families): string
    {
        $lines = [];

        $lines[] = '0 HEAD';
        $lines[] = '1 SOUR KSF_CRM_GEDCOM';
        $lines[] = '2 NAME KSF CRM GEDCOM Export';
        $lines[] = '1 CHAR UTF-8';
        $lines[] = '1 GEDC';
        $lines[] = '2 VERS 5.5';
        $lines[] = '2 FORM LINEAGE-LINKED';
        $lines[] = '1 DATE ' . date('d M Y');
        $lines[] = '1 SUBM @SUBMITTER@';
        $lines[] = '0 @SUBMITTER@ SUBM';
        $lines[] = '1 NAME KSF CRM Export';

        foreach ($persons as $person) {
            $this->writeIndividual($lines, $person);
        }

        foreach ($families as $family) {
            $this->writeFamily($lines, $family, $persons);
        }

        $lines[] = '0 TRLR';

        return implode("\r\n", $lines);
    }

    private function writeIndividual(array &$lines, PersonData $person): void
    {
        $xref = $person->getXref();
        $fullName = $person->getFullName();
        $gedcomName = $person->getFirstName() . ' /' . ($person->getLastName() ?: '?') . '/';

        $lines[] = '0 @' . $xref . '@ INDI';
        $lines[] = '1 NAME ' . $gedcomName;

        $sex = $person->getSex();
        if ($sex === 'M' || $sex === 'F') {
            $lines[] = '1 SEX ' . $sex;
        }

        $birthEvent = $person->getBirthEvent();
        if ($birthEvent !== null) {
            $lines[] = '1 BIRT';
            if ($birthEvent->getDate() !== null) {
                $lines[] = '2 DATE ' . $this->dateConverter->formatGedcomDate($birthEvent->getDate());
            }
            if ($birthEvent->getPlace() !== null) {
                $lines[] = '2 PLAC ' . $birthEvent->getPlace();
            }
        }

        $deathEvent = $person->getDeathEvent();
        if ($deathEvent !== null) {
            $lines[] = '1 DEAT';
            if ($deathEvent->getDate() !== null) {
                $lines[] = '2 DATE ' . $this->dateConverter->formatGedcomDate($deathEvent->getDate());
            }
            if ($deathEvent->getPlace() !== null) {
                $lines[] = '2 PLAC ' . $deathEvent->getPlace();
            }
        }

        foreach ($person->getOccupations() as $occ) {
            $lines[] = '1 OCCU ' . $occ;
        }

        if ($person->getEmployer() !== null) {
            $lines[] = '1 _EMPLOYER ' . $person->getEmployer();
        }

        if ($person->getBeneficiary() !== null) {
            $lines[] = '1 _BENEFICIARY ' . $person->getBeneficiary();
        }

        foreach ($person->getFamsRefs() as $fref) {
            $lines[] = '1 FAMS @' . $fref . '@';
        }

        foreach ($person->getFamcRefs() as $fref) {
            $lines[] = '1 FAMC @' . $fref . '@';
        }

        if ($person->getNotes() !== '') {
            $lines[] = '1 NOTE ' . $person->getNotes();
        }
    }

    private function writeFamily(array &$lines, FamilyData $family, array $persons): void
    {
        $lines[] = '0 @' . $family->getXref() . '@ FAM';

        if ($family->getHusbandXref() !== null) {
            $lines[] = '1 HUSB @' . $family->getHusbandXref() . '@';
        }

        if ($family->getWifeXref() !== null) {
            $lines[] = '1 WIFE @' . $family->getWifeXref() . '@';
        }

        foreach ($family->getChildXrefs() as $childXref) {
            $lines[] = '1 CHIL @' . $childXref . '@';
        }

        $marriageEvent = $family->getMarriageEvent();
        if ($marriageEvent !== null) {
            $lines[] = '1 MARR';
            if ($marriageEvent->getDate() !== null) {
                $lines[] = '2 DATE ' . $this->dateConverter->formatGedcomDate($marriageEvent->getDate());
            }
            if ($marriageEvent->getPlace() !== null) {
                $lines[] = '2 PLAC ' . $marriageEvent->getPlace();
            }
        }
    }
}
