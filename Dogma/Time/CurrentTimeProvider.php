<?php

namespace Dogma\Time;

class CurrentTimeProvider implements TimeProvider
{
    use \Dogma\StrictBehaviorMixin;

    public function getDateTime(): DateTime
    {
        return new DateTime();
    }

}
