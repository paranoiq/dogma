<?php

namespace Dogma\Time;

interface TimeProvider
{

    /**
     * @return \Dogma\Time\DateTime
     */
    public function getDateTime();

}
