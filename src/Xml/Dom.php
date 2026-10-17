<?php

namespace Behatch\Xml;

class Dom implements \Stringable
{
    private readonly \DOMDocument $dom;

    public function __construct(string $content)
    {
        if ('' === $content) {
            throw new \DOMException('The XML content is empty');
        }

        $this->dom = new \DOMDocument();
        $this->dom->strictErrorChecking = false;
        $this->dom->validateOnParse = false;
        $this->dom->preserveWhiteSpace = true;
        $this->dom->loadXML($content, \LIBXML_PARSEHUGE);
        $this->throwError();
    }

    public function __toString(): string
    {
        $this->dom->formatOutput = true;

        return $this->dom->saveXML();
    }

    public function validate(): void
    {
        $this->dom->validate();
        $this->throwError();
    }

    public function validateXsd(string $xsd): void
    {
        $this->dom->schemaValidateSource($xsd);
        $this->throwError();
    }

    public function validateNg(string $ng): void
    {
        try {
            $this->dom->relaxNGValidateSource($ng);
            $this->throwError();
        } catch (\DOMException $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function xpath(string $element): \DOMNodeList
    {
        $xpath = new \DOMXPath($this->dom);
        $this->registerNamespace($xpath);

        $element = $this->fixNamespace($element);
        $elements = $xpath->query($element);

        return (false === $elements) ? new \DOMNodeList() : $elements;
    }

    private function registerNamespace(\DOMXPath $xpath): void
    {
        $namespaces = $this->getNamespaces();

        foreach ($namespaces as $prefix => $namespace) {
            if (empty($prefix) && $this->hasDefaultNamespace()) {
                $prefix = 'rootns';
            }
            $xpath->registerNamespace($prefix, $namespace);
        }
    }

    /**
     * "fix" queries to the default namespace if any namespaces are defined.
     */
    private function fixNamespace(string $element): string
    {
        $namespaces = $this->getNamespaces();

        if (!empty($namespaces) && $this->hasDefaultNamespace()) {
            for ($i = 0; $i < 2; ++$i) {
                $element = preg_replace('/\/(\w+)(\[[^]]+\])?\//', '/rootns:$1$2/', $element);
            }
            $element = preg_replace('/\/(\w+)(\[[^]]+\])?$/', '/rootns:$1$2', $element);
        }

        return $element;
    }

    private function hasDefaultNamespace(): bool
    {
        $defaultNamespaceUri = $this->dom->lookupNamespaceURI(null);
        $defaultNamespacePrefix = $defaultNamespaceUri ? $this->dom->lookupPrefix($defaultNamespaceUri) : null;

        return empty($defaultNamespacePrefix) && !empty($defaultNamespaceUri);
    }

    public function getNamespaces(): array
    {
        $xml = simplexml_import_dom($this->dom);

        return $xml->getNamespaces(true);
    }

    private function throwError(): void
    {
        $error = libxml_get_last_error();
        if (!empty($error)) {
            // https://bugs.php.net/bug.php?id=46465
            if ('Validation failed: no DTD found !' != $error->message) {
                throw new \DOMException($error->message.' at line '.$error->line);
            }
        }
    }
}
