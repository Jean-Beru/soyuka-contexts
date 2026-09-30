<?php

namespace Behatch\Json;

use JsonSchema\SchemaStorage;
use JsonSchema\Validator;

class JsonSchema extends Json
{
    private readonly ?string $uri;

    public function __construct(string|\Stringable $content, ?string $uri = null)
    {
        $this->uri = $uri;

        parent::__construct($content);
    }

    public function resolve(SchemaStorage $resolver): static
    {
        if (!$this->hasUri()) {
            return $this;
        }

        $this->content = $resolver->resolveRef($this->uri);

        return $this;
    }

    public function validate(Json $json, Validator $validator): bool
    {
        $content = $json->getContent();
        $validator->validate($content, $this->getContent());

        if (!$validator->isValid()) {
            $msg = 'JSON does not validate. Violations:'.\PHP_EOL;
            foreach ($validator->getErrors() as $error) {
                $msg .= \sprintf('  - [%s] %s'.\PHP_EOL, $error['property'], $error['message']);
            }
            throw new \Exception($msg);
        }

        return true;
    }

    private function hasUri(): bool
    {
        return null !== $this->uri;
    }
}
