<?php

namespace Behatch\Json;

use Behatch\MissingDependencyException;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class JsonInspector
{
    private $evaluationMode;

    private $accessor;

    public function __construct($evaluationMode)
    {
        $this->evaluationMode = $evaluationMode;
    }

    public function evaluate(Json $json, $expression)
    {
        if (!class_exists(PropertyAccessor::class)) {
            throw new MissingDependencyException('JSON node access cannot be used as the PropertyAccess component is not installed. Try running "composer require --dev symfony/property-access".');
        }

        $this->accessor ??= new PropertyAccessor(
            PropertyAccessor::MAGIC_GET | PropertyAccessor::MAGIC_SET,
            PropertyAccessor::THROW_ON_INVALID_INDEX | PropertyAccessor::THROW_ON_INVALID_PROPERTY_PATH
        );

        if ('javascript' === $this->evaluationMode) {
            $expression = str_replace('->', '.', $expression);
        }

        try {
            return $json->read($expression, $this->accessor);
        } catch (\Exception $e) {
            throw new \Exception("Failed to evaluate expression '$expression'");
        }
    }

    public function validate(Json $json, JsonSchema $schema)
    {
        if (!class_exists(\JsonSchema\Validator::class)) {
            throw new MissingDependencyException('JSON schema validation cannot be used as the justinrainbow/json-schema package is not installed. Try running "composer require --dev justinrainbow/json-schema".');
        }

        $validator = new \JsonSchema\Validator();

        $resolver = new \JsonSchema\SchemaStorage(new \JsonSchema\Uri\UriRetriever(), new \JsonSchema\Uri\UriResolver());
        $schema->resolve($resolver);

        return $schema->validate($json, $validator);
    }
}
