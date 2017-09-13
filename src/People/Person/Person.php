<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\People\Person;

use Dogma\Time\Date;

class Person
{
    use \Dogma\StrictBehaviorMixin;

    /** @var string|null */
    private $firstName;

    /** @var string|null */
    private $lastName;

    /** @var string[] */
    private $middleNames;

    /** @var string|null */
    private $prefixDegree;

    /** @var string|null */
    private $suffixDegree;

    /** @var \Dogma\People\Person\Gender|null */
    private $gender;

    /** @var \Dogma\People\Person\Generation|null */
    private $generation;

    /** @var \Dogma\Time\Date|null */
    private $birthDate;

    /** @var \Dogma\Time\Date|null */
    private $deceasedDate;

    public function __construct()
    {
        ///
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @return string[]
     */
    public function getMiddleNames(): array
    {
        return $this->middleNames;
    }

    public function getPrefixDegree(): ?string
    {
        return $this->prefixDegree;
    }

    public function getSuffixDegree(): ?string
    {
        return $this->suffixDegree;
    }

    public function getGender(): ?Gender
    {
        return $this->gender;
    }

    public function getGeneration(): ?Generation
    {
        return $this->generation;
    }

    public function getBirthDate(): ?Date
    {
        return $this->birthDate;
    }

    public function getDeceasedDate(): ?Date
    {
        return $this->deceasedDate;
    }

}
