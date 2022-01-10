<?php

namespace Streams\Core\Field\Type;

use Streams\Core\Field\Field;
use Streams\Core\Field\Value\StrValue;
use Streams\Core\Field\Schema\StrSchema;

class Str extends Field
{

    public function modify($value)
    {
        return (string) $value;
    }

    public function restore($value)
    {
        return (string) $value;
    }

    public function generate()
    {
        return $this->generator()->text();
    }

    public function getValueName()
    {
        return StrValue::class;
    }

    public function getSchemaName()
    {
        return StrSchema::class;
    }
}
