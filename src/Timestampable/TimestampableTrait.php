<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\Timestampable;

trait TimestampableTrait
{
    use CreatedAtTimestampableTrait;
    use UpdatedAtTimestampableTrait;
}
