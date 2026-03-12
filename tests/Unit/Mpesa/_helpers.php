<?php

use Illuminate\Http\Client\Factory as HttpFactory;

function makeFakeGuzzleClient(array &$calls): object
{
    return new class($calls) {
        public array $calls = [];

        public function __construct(&$calls)
        {
            $this->calls = &$calls;
        }

        public function post(string $url, array $options)
        {
            $this->calls[] = ['method' => 'post', 'url' => $url, 'options' => $options];

            return new class {
                public function getBody()
                {
                    return new class {
                        public function getContents()
                        {
                            return json_encode(['ok' => true]);
                        }
                    };
                }
            };
        }
    };
}

/**
 * Set a protected or private property on an object (walks up class hierarchy if needed).
 */
function setProtected(object $object, string $property, mixed $value): void
{
    $ref = new ReflectionClass($object);
    while (! $ref->hasProperty($property) && $ref = $ref->getParentClass()) {
    }

    $prop = $ref->getProperty($property);
    $scope = $prop->getDeclaringClass()->getName();

    $setter = Closure::bind(
        function (string $property, mixed $value): void {
            $this->{$property} = $value;
        },
        $object,
        $scope
    );

    $setter($property, $value);
}

/**
 * Get a protected or private property from an object (walks up class hierarchy if needed).
 */
function getProtected(object $object, string $property): mixed
{
    $ref = new ReflectionClass($object);
    while (! $ref->hasProperty($property) && $ref = $ref->getParentClass()) {
    }

    $prop = $ref->getProperty($property);
    $scope = $prop->getDeclaringClass()->getName();

    $getter = Closure::bind(
        function (string $property): mixed {
            return $this->{$property};
        },
        $object,
        $scope
    );

    return $getter($property);
}

/**
 * Call a protected or private method on an object (walks up class hierarchy if needed).
 *
 * @param  array  $args
 * @return mixed
 */
function callPrivate(object $object, string $method, array $args = []): mixed
{
    $ref = new ReflectionClass($object);
    while (! $ref->hasMethod($method) && $ref = $ref->getParentClass()) {
    }

    $m = $ref->getMethod($method);
    $scope = $m->getDeclaringClass()->getName();

    $caller = Closure::bind(
        function (string $method, array $args): mixed {
            return $this->{$method}(...$args);
        },
        $object,
        $scope
    );

    return $caller($method, $args);
}

/**
 * Swap the Http facade's underlying Factory (e.g. for test fakes).
 */
function swapHttpFactory(HttpFactory $factory): void
{
    Illuminate\Support\Facades\Http::swap($factory);
}

