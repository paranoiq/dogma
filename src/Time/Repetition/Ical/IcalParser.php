<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\Repetition\Ical;

use Dogma\Geolocation\Position;
use Dogma\NotImplementedException;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\InvalidIcalDefinitionException;
use Dogma\Time\Span\DateTimeSpan;
use Dogma\Time\TimeZone;
use Dogma\ValueOutOfRangeException;

class IcalParser
{
    use StrictBehaviorMixin;

    /**
     * @param string $rules
     * @return \Dogma\Time\Repetition\Ical\IcalNode[]
     */
    public function parse(string $rules): array
    {
        $nodes = [];
        $rows = explode("\n", $rules);
        foreach ($rows as $row) {
            $node = $this->parseRow($row);
            if ($node !== null) {
                $nodes[] = $node;
            }
        }

        return $nodes;
    }

    private function parseRow(string $row): ?IcalNode
    {
        $row = trim($row);
        if ($row === '') {
            return null;
        }
        if (strpos($row, ':') === false) {
            throw new InvalidIcalDefinitionException($row);
        }

        [$prefix, $value] = explode(':', $row);
        $paramParts = explode(';', $prefix);
        $name = array_shift($paramParts);
        if (!IcalNodeType::isValid($name)) {
            throw new InvalidIcalDefinitionException($name);
        }
        $nodeType = IcalNodeType::get($name);
        $params = $this->parseParameters($paramParts);

        switch ($name) {
            case IcalNodeType::REPETITION_DATE:

            case IcalNodeType::REPETITION_RULE:

            case IcalNodeType::EXCLUDE_DATE:

            case IcalNodeType::EXCLUDE_RULE:
                ///
            case IcalNodeType::TIME_ZONE_ID:
                $parts = explode('/', $value);
                while (count($parts > 2)) {
                    array_shift($parts);
                }
                try {
                    $timeZone = TimeZone::get(implode('/', $parts))->getDateTimeZone();
                } catch (\Exception $e) {
                    throw new InvalidIcalDefinitionException($value, $e);
                }
                return new IcalNode($nodeType, $timeZone);
            case IcalNodeType::TIME_ZONE_NAME:
                if (isset($params[IcalParameter::LANGUAGE])) {
                    throw new NotImplementedException('ICalParser: translated time zone names are not supported.');
                }
                try {
                    $timeZone = new \DateTimeZone($value);
                } catch (\Exception $e) {
                    throw new InvalidIcalDefinitionException($value, $e);
                }
                return new IcalNode($nodeType, $timeZone);
            case IcalNodeType::START_TIME:


            case IcalNodeType::DURATION:
                try {
                    $duration = DateTimeSpan::createFromDateIntervalString($value);
                } catch (\Exception $e) {
                    throw new InvalidIcalDefinitionException($value, $e);
                }
                return new IcalNode($nodeType, $duration);
            case IcalNodeType::GPS_POSITION:
                $coordinates = explode(';', $value);
                if (count($coordinates) !== 2 || !is_numeric($coordinates[0]) || !is_numeric($coordinates[1])) {
                    throw new InvalidIcalDefinitionException($value);
                }
                try {
                    $position = new Position((float) $coordinates[0], (float) $coordinates[1]);
                } catch (ValueOutOfRangeException $e) {
                    throw new InvalidIcalDefinitionException($value, $e);
                }
                return new IcalNode($nodeType, $position);
            default:
                return null;
        }
    }

    private function parseParameters(array $parts): array
    {
        $params = [];
        foreach ($parts as $part) {
            if (!strpos($part, '=')) {
                throw new InvalidIcalDefinitionException($part);
            }
            [$name, $value] = explode('=', $part);
            if (!IcalParameter::isValid($name)) {
                throw new InvalidIcalDefinitionException($name);
            }
            $params[$name] = $value;
        }

        return $params;
    }



}
