<?php

namespace Behatch\HttpCall;

class HttpCallResult
{
    public function __construct(private mixed $value)
    {
    }

    public function update(mixed $value): void
    {
        $this->value = $value;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
