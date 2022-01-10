<?php

namespace Streams\Core\Entry;

use ArrayAccess;
use Carbon\Carbon;
use JsonSerializable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Streams\Core\Stream\Stream;
use Illuminate\Validation\Validator;
use Streams\Core\Field\Type\Datetime;
use Illuminate\Support\Traits\Macroable;
use Streams\Core\Support\Traits\Fluency;
use Streams\Core\Support\Facades\Streams;
use Illuminate\Contracts\Support\Jsonable;
use Streams\Core\Support\Traits\HasMemory;
use Illuminate\Contracts\Support\Arrayable;
use Streams\Core\Entry\Contract\EntryInterface;

class Entry implements
    JsonSerializable,
    EntryInterface,
    ArrayAccess,
    Arrayable,
    Jsonable
{

    use Macroable {
        Macroable::__call as private callMacroable;
    }

    use Fluency {
        Fluency::__construct as private constructFluency;
    }

    use HasMemory;

    public ?Stream $stream;

    public function __construct(array $attributes = [])
    {
        $this->stream = Arr::pull($attributes, 'stream');

        $this->constructFluency($attributes);
    }

    /**
     * Map the ID attribute to the desired key.
     */
    public function getIdAttribute()
    {
        $name = $this->stream()->config('meta.key_name', 'id');

        $value = $this->__prototype['attributes'][$name] ?? $this->getPrototypeAttributeDefault($name);

        return $value;
    }

    /**
     * Return the last modified date if possible.
     */
    public function lastModified()
    {
        return $this->once(__METHOD__, function () {

            $datetime = $this->__updated_at;

            if (!$datetime instanceof Datetime) {
                $datetime = new Carbon($datetime);
            }

            return $datetime;
        });
    }

    /**
     * Return the entry stream.
     * 
     * @return Stream
     */
    public function stream()
    {
        return $this->stream;
    }

    /**
     * Save the entry.
     * 
     * @param array $options
     * @var bool
     */
    public function save(array $options = [])
    {
        return $this->stream()
            ->repository()
            ->save($this);
    }

    /**
     * Delete the entry.
     *
     * @return bool
     */
    public function delete()
    {
        return $this->stream()
            ->repository()
            ->delete($this);
    }

    /**
     * Return the entry validator.
     * 
     * @return Validator
     */
    public function validator()
    {
        return $this->stream()->validator($this);
    }

    /**
     * Return the entry with defined attributes only.
     */
    public function strict()
    {
        $attributes = $this->getPrototypeAttributes();
        $allowedKeys = $this->stream()->fields->keys()->all();

        $this->setPrototypeAttributes(array_intersect_key($attributes, array_flip($allowedKeys)));

        return $this;
    }

    // /**
    //  * Load an entry over this one.
    //  *
    //  * @param $identifier
    //  * @return $this
    //  */
    // protected function loadEntry($identifier)
    // {
    //     $loaded = $this->stream()->repository()->find($identifier);

    //     $this->setPrototypeAttributes(
    //         array_merge($this->toArray(), $loaded->toArray())
    //     );

    //     return $this;
    // }

    // /**
    //  * Extend over another entry.
    //  *
    //  * @param $identifier
    //  * @return $this
    //  */
    // protected function extendEntry($identifier)
    // {
    //     $extended = $this->stream()->repository()->find($identifier);

    //     $this->setPrototypeAttributes(
    //         array_merge($extended->toArray(), $this->toArray())
    //     );

    //     return $this;
    // }

    public function toArray()
    {
        return array_diff_key($this->getAttributes(), array_flip([
            '__created_at',
            '__updated_at',
        ]));
    }

    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function __toString()
    {
        return $this->toJson();
    }

    public function __get($key)
    {
        return $this->getPrototypeAttribute($key);
    }

    public function __set($key, $value)
    {
        $this->setPrototypeAttribute($key, $value);
    }

    public function __call($method, $arguments)
    {
        if (static::hasMacro($method)) {
            return $this->callMacroable($method, $arguments);
        }

        $key = Str::snake($method);

        if ($this->hasPrototypeAttribute($key)) {
            return $this->expandPrototypeAttribute($key);
        }

        throw new \BadMethodCallException(sprintf(
            'Method %s::%s does not exist.',
            static::class,
            $method
        ));
    }
}
