<?php

namespace Streams\Core\Field\Types;

use Carbon\Carbon;
use Streams\Core\Field\Schema\TimeSchema;

class TimeFieldType extends DatetimeFieldType
{
    public function modify($value): string
    {
        return $this->toCarbon($value)->format('H:i:s');
    }

    public function restore($value): Carbon
    {
        return $this->toCarbon($value);
    }

    public function getSchemaName()
    {
        return TimeSchema::class;
    }

    public function generate()
    {
        return $this->generator()->time();
    }
}