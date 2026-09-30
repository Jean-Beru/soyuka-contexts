<?php

namespace Behatch;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Session;

trait Html
{
    abstract protected function getSession(?string $name = null): Session;

    protected function countElements(string $element, int $index, string $parent): int
    {
        $page = $this->getSession()->getPage();

        $parents = $page->findAll('css', $parent);
        if (!isset($parents[$index - 1])) {
            throw new \Exception("The $index element '$parent' was not found anywhere in the page");
        }

        $elements = $parents[$index - 1]->findAll('css', $element);

        return \count($elements);
    }

    protected function findElement(string $selector, string|array $locator, int $index): NodeElement
    {
        $page = $this->getSession()->getPage();

        $nodes = $page->findAll($selector, $locator);

        if (!isset($nodes[$index - 1])) {
            $locator = \is_array($locator) ? implode(' ', $locator) : $locator;

            throw new \Exception("The $index $selector '$locator' was not found anywhere in the page");
        }

        return $nodes[$index - 1];
    }
}
