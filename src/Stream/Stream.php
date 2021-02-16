<?php

namespace Streams\Core\Stream;

use ArrayAccess;
use JsonSerializable;
use Illuminate\Support\Arr;
use Illuminate\Validation\Factory;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Validator;
use Streams\Core\Repository\Repository;
use Illuminate\Contracts\Support\Jsonable;
use Streams\Core\Support\Facades\Hydrator;
use Streams\Core\Support\Traits\HasMemory;
use Streams\Core\Support\Traits\Prototype;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Traits\ForwardsCalls;
use Illuminate\Validation\ValidationRuleParser;
use Streams\Core\Support\Traits\FiresCallbacks;
use Streams\Core\Validation\StreamsPresenceVerifier;
use Streams\Core\Criteria\Contract\CriteriaInterface;
use Streams\Core\Repository\Contract\RepositoryInterface;

/**
 * @property string handle
 * @property Repository repository
 * @property array rules
 * @property array validators
 * @property \Streams\Core\Field\FieldCollection|\Streams\Core\Field\Field[] fields
 *
 */
class Stream implements
    JsonSerializable,
    ArrayAccess,
    Arrayable,
    Jsonable
{

    use Prototype {
        Prototype::initializePrototype as private initializePrototypeTrait;
    }

    use HasMemory;
    use ForwardsCalls;
    use FiresCallbacks;

    /**
     * Initialize the prototype.
     *
     * @param array $attributes
     * @return $this
     */
    protected function initializePrototype(array $attributes)
    {
        return $this->initializePrototypeTrait(array_merge([
            'handle' => null,
            'routes' => [],
            'config' => [
                'key_name' => 'id',
            ],
        ], $attributes));
    }

    /**
     * Return the entry repository.
     *
     * @todo Let's review this idea. Could use for allowing configuration of criteria too. Flat or in some kinda config array?
     * @return RepositoryInterface
     */
    public function repository()
    {
        $repository = $this->repository ?: Repository::class;

        return new $repository($this);
    }

    /**
     * Return the entry criteria.
     *
     * @return CriteriaInterface
     */
    public function entries()
    {
        return $this
            ->repository()
            ->newCriteria();
    }

    /**
     * Return an entry validator with the data.
     *
     * @param $data
     * @return Validator
     */
    public function validator($data): Validator
    {
        $data = Arr::make($data);

        $factory = App::make(Factory::class);

        $factory->setPresenceVerifier(new StreamsPresenceVerifier(App::make('db')));

        /**
         * https://gph.is/g/Eqn635a
         */
        $rules = $this->getPrototypeAttribute('rules') ?: [];
        $validators = $this->getPrototypeAttribute('validators') ?: [];

        $fieldRules = $this->fields->rules();
        $fieldValidators = $this->fields->validators();

        /**
         * Merge stream and field configurations.
         */
        foreach ($fieldRules as $field => $configured) {
            $rules[$field] = array_merge(Arr::get($rules, $field, []), $configured);
        }

        foreach ($fieldValidators as $field => $configured) {
            $validators[$field] = array_merge(Arr::get($validators, $field, []), $configured);
        }

        /**
         * Stringify rules for Laravel.
         */
        $rules = array_map(function ($rules) {
            return implode('|', array_unique($rules));
        }, $rules);

        /**
         * Extend the factory with custom validators.
         */
        foreach ($validators as $rule => $validator) {

            $handler = Arr::get($validator, 'handler');

            if (strpos($handler, '@')) {

                $handler = function ($attribute, $value, $parameters, Validator $validator) use ($handler) {

                    return App::call(
                        $handler,
                        [
                            'value' => $value,
                            'attribute' => $attribute,
                            'validator' => $validator,
                            'parameters' => $parameters,
                        ],
                        'handle'
                    );
                };
            }

            $factory->extend(
                $rule,
                $handler,
                Arr::get($validator, 'message')
            );
        }

        return $factory->make($data, $rules);
    }

    public function hasRule($field, $rule)
    {
        return (bool) $this->getRule($field, $rule);
    }

    public function getRule($field, $rule)
    {
        $rules = Arr::get($this->rules, $field, []);

        return Arr::first($rules, function ($target) use ($rule) {
            return strpos($target, $rule . ':') !== false || strpos($target, $rule) !== false;
        });
    }

    public function getRuleParameters($field, $rule)
    {
        if (!$rule = $this->getRule($field, $rule)) {
            return [];
        }

        [$rule, $parameters] = ValidationRuleParser::parse($rule);

        return $parameters;
    }

    public function isRequired($field)
    {
        return $this->hasRule($field, 'required');
    }

    public function config($key = null, $default = null)
    {
        if (!$key) {
            return $this->expandPrototypeAttribute('config');
        }
        return Arr::get($this->config, $key, $default);
    }

    public function meta($key = null, $default = null)
    {
        if (!$key) {
            return $this->expandPrototypeAttribute('meta');
        }
        return Arr::get($this->meta, $key, $default);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return Arr::make(Hydrator::dehydrate($this));
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Specify data which should
     * be serialized to JSON.
     * 
     * @return mixed
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Return a string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }
}
