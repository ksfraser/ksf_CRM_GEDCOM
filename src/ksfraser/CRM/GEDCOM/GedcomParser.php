<?php

declare(strict_types=1);

namespace Ksfraser\CRM\GEDCOM;

class GedcomParser
{
    private array $lines;
    private int $pos;
    private GedcomDateConverter $dateConverter;

    public function __construct()
    {
        $this->dateConverter = new GedcomDateConverter();
    }

    public function parse(string $gedcomContent): ParsedGedcomData
    {
        $normalized = str_replace("\r\n", "\n", $gedcomContent);
        $normalized = str_replace("\r", "\n", $normalized);
        $this->lines = explode("\n", $normalized);
        $this->pos = 0;

        $persons = [];
        $families = [];

        while ($this->pos < count($this->lines)) {
            $line = trim($this->lines[$this->pos]);
            $this->pos++;

            if ($line === '') {
                continue;
            }

            if (preg_match('/^0 @(\w+)@ INDI$/', $line, $m)) {
                $xref = $m[1];
                $person = $this->parseIndividual($xref);
                if ($person !== null) {
                    $persons[] = $person;
                }
            } elseif (preg_match('/^0 @(\w+)@ FAM$/', $line, $m)) {
                $xref = $m[1];
                $family = $this->parseFamily($xref);
                if ($family !== null) {
                    $families[] = $family;
                }
            }
        }

        return new ParsedGedcomData($persons, $families);
    }

    private function parseIndividual(string $xref): ?PersonData
    {
        $firstName = '';
        $lastName = '';
        $sex = '';
        $birtDate = null;
        $birtPlace = null;
        $deatDate = null;
        $deatPlace = null;
        $occupations = [];
        $employer = null;
        $beneficiary = null;
        $famsRefs = [];
        $famcRefs = [];
        $notesStr = '';
        $otherEvents = [];

        while ($this->pos < count($this->lines)) {
            $line = trim($this->lines[$this->pos]);
            if ($line === '') {
                $this->pos++;
                continue;
            }

            if (preg_match('/^(\d+)/', $line, $m)) {
                $level = (int)$m[1];
                if ($level <= 0) {
                    break;
                }
            } else {
                break;
            }

            $this->pos++;

            if (preg_match('/^1 NAME (.+)$/', $line, $m)) {
                $parsed = $this->parseName($m[1]);
                $firstName = $parsed['first_name'];
                $lastName = $parsed['last_name'];
            } elseif (preg_match('/^1 SEX (.+)$/', $line, $m)) {
                $sex = strtoupper(trim($m[1]));
            } elseif (preg_match('/^1 BIRT/', $line)) {
                $subs = $this->collectSubs(1);
                foreach ($subs as $sub) {
                    if (preg_match('/^2 DATE (.+)$/', $sub, $m)) {
                        $birtDate = $this->dateConverter->parseGedcomDate(trim($m[1]));
                    } elseif (preg_match('/^2 PLAC (.+)$/', $sub, $m)) {
                        $birtPlace = trim($m[1]);
                    }
                }
            } elseif (preg_match('/^1 DEAT/', $line)) {
                $subs = $this->collectSubs(1);
                foreach ($subs as $sub) {
                    if (preg_match('/^2 DATE (.+)$/', $sub, $m)) {
                        $deatDate = $this->dateConverter->parseGedcomDate(trim($m[1]));
                    } elseif (preg_match('/^2 PLAC (.+)$/', $sub, $m)) {
                        $deatPlace = trim($m[1]);
                    }
                }
            } elseif (preg_match('/^1 OCCU (.+)$/', $line, $m)) {
                $occupations[] = trim($m[1]);
            } elseif (preg_match('/^1 _EMPLOYER (.+)$/', $line, $m)) {
                $employer = trim($m[1]);
            } elseif (preg_match('/^1 _BENEFICIARY (.+)$/', $line, $m)) {
                $beneficiary = trim($m[1]);
            } elseif (preg_match('/^1 FAMS @(\w+)@$/', $line, $m)) {
                $famsRefs[] = $m[1];
            } elseif (preg_match('/^1 FAMC @(\w+)@$/', $line, $m)) {
                $famcRefs[] = $m[1];
            } elseif (preg_match('/^1 NOTE (.+)$/', $line, $m)) {
                $notesStr = trim($m[1]);
            }
        }

        $birthEvent = ($birtDate !== null || $birtPlace !== null)
            ? new EventData('BIRT', $birtDate, $birtPlace)
            : null;

        $deathEvent = ($deatDate !== null || $deatPlace !== null)
            ? new EventData('DEAT', $deatDate, $deatPlace)
            : null;

        return new PersonData(
            $xref,
            $firstName,
            $lastName,
            $sex,
            $birthEvent,
            $deathEvent,
            $otherEvents,
            $famsRefs,
            $famcRefs,
            $notesStr,
            $occupations,
            $employer,
            $beneficiary
        );
    }

    private function parseFamily(string $xref): ?FamilyData
    {
        $husbandXref = null;
        $wifeXref = null;
        $childXrefs = [];
        $marrDate = null;
        $marrPlace = null;

        while ($this->pos < count($this->lines)) {
            $line = trim($this->lines[$this->pos]);
            if ($line === '') {
                $this->pos++;
                continue;
            }

            if (preg_match('/^(\d+)/', $line, $m)) {
                $level = (int)$m[1];
                if ($level <= 0) {
                    break;
                }
            } else {
                break;
            }

            $this->pos++;

            if (preg_match('/^1 HUSB @(\w+)@$/', $line, $m)) {
                $husbandXref = $m[1];
            } elseif (preg_match('/^1 WIFE @(\w+)@$/', $line, $m)) {
                $wifeXref = $m[1];
            } elseif (preg_match('/^1 CHIL @(\w+)@$/', $line, $m)) {
                $childXrefs[] = $m[1];
            } elseif (preg_match('/^1 MARR/', $line)) {
                $subs = $this->collectSubs(1);
                foreach ($subs as $sub) {
                    if (preg_match('/^2 DATE (.+)$/', $sub, $m)) {
                        $marrDate = $this->dateConverter->parseGedcomDate(trim($m[1]));
                    } elseif (preg_match('/^2 PLAC (.+)$/', $sub, $m)) {
                        $marrPlace = trim($m[1]);
                    }
                }
            }
        }

        $marriageEvent = ($marrDate !== null || $marrPlace !== null)
            ? new EventData('MARR', $marrDate, $marrPlace)
            : null;

        return new FamilyData($xref, $husbandXref, $wifeXref, $childXrefs, $marriageEvent);
    }

    private function collectSubs(int $parentLevel): array
    {
        $subs = [];
        while ($this->pos < count($this->lines)) {
            $line = trim($this->lines[$this->pos]);
            if ($line === '') {
                $this->pos++;
                continue;
            }

            if (preg_match('/^(\d+)/', $line, $m)) {
                $level = (int)$m[1];
                if ($level <= $parentLevel) {
                    break;
                }
            } else {
                break;
            }

            $this->pos++;
            $subs[] = $line;
        }
        return $subs;
    }

    private function parseName(string $nameStr): array
    {
        $nameStr = trim($nameStr);
        if (preg_match('/^([^\/]*)\/?\s*(.*?)\s*\/?$/', $nameStr, $m)) {
            $first = trim($m[1]);
            $last = trim($m[2]);
            if ($last === '' && strpos($nameStr, '/') !== false) {
                if (preg_match('/\/([^\/]+)\//', $nameStr, $nm)) {
                    $last = trim($nm[1]);
                    $first = trim(str_replace('/' . $nm[1] . '/', '', $nameStr));
                }
            }
            return [
                'first_name' => $first ?: $nameStr,
                'last_name' => $last,
            ];
        }
        return ['first_name' => $nameStr, 'last_name' => ''];
    }
}
