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

function setProtected(object $object, string $property, mixed $value): void
{
    $ref = new ReflectionClass($object);
    while (! $ref->hasProperty($property) && $ref = $ref->getParentClass()) {
        // keep walking up
    }

    $prop = $ref->getProperty($property);
    $prop->setAccessible(true);
    $prop->setValue($object, $value);
}

function callPrivate(object $object, string $method, array $args = []): mixed
{
    $ref = new ReflectionClass($object);
    while (! $ref->hasMethod($method) && $ref = $ref->getParentClass()) {
        // keep walking up
    }

    $m = $ref->getMethod($method);
    $m->setAccessible(true);

    return $m->invokeArgs($object, $args);
}

function swapHttpFactory(HttpFactory $factory): void
{
    // The Http facade can swap the underlying Factory instance.
    Illuminate\Support\Facades\Http::swap($factory);
}

