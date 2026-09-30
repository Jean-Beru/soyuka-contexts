<?php

namespace Behatch\Json;

use Symfony\Component\PropertyAccess\PropertyAccessor;

class Json implements \Stringable
{
    protected mixed $content;

    public function __construct(string|\Stringable $content)
    {
        $this->content = $this->decode((string) $content);
    }

    public function getContent(): mixed
    {
        return $this->content;
    }

    public function read(string $expression, PropertyAccessor $accessor): mixed
    {
        if (\is_array($this->content)) {
            $expression = preg_replace('/^root/', '', $expression);
        } else {
            $expression = preg_replace('/^root./', '', $expression);
        }

        // If root asked, we return the entire content
        if ('' === trim($expression)) {
            return $this->content;
        }

        return $accessor->getValue($this->content, $expression);
    }

    public function encode(bool $pretty = true): string
    {
        $flags = \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE | \JSON_THROW_ON_ERROR;

        if ($pretty) {
            $flags |= \JSON_PRETTY_PRINT;
        }

        return json_encode($this->content, $flags);
    }

    public function __toString(): string
    {
        return $this->encode(false);
    }

    private function decode(string $content): mixed
    {
        $result = json_decode($content);

        if (\JSON_ERROR_NONE !== json_last_error()) {
            throw new \Exception("The string '$content' is not valid json");
        }

        return $result;
    }
}
