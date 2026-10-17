<?php

namespace Behatch\Context;

use Behat\Behat\Context\TranslatableContext;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Transformation\Transform;

abstract class BaseContext extends RawMinkContext implements TranslatableContext
{
    use \Behatch\Asserter;
    use \Behatch\Html;

    public static function getTranslationResources(): array
    {
        return glob(__DIR__.'/../../i18n/*.xliff') ?: [];
    }

    #[Transform('/^(0|[1-9]\d*)(?:st|nd|rd|th)?$/')] // en
    #[Transform('/^(0|[1-9]\d*)(?:ier|er|e|ème)?$/')] // fr
    #[Transform('/^(0|[1-9]\d*)º?$/')] // pt
    #[Transform('/^(0|[1-9]\d*)(?:ой|ий|ый|ей|й)?$/')] // ru
    public function castToInt($count)
    {
        if ((int) $count < \PHP_INT_MAX) {
            return (int) $count;
        }

        return $count;
    }

    protected function getMinkContext()
    {
        $context = new \Behat\MinkExtension\Context\MinkContext();
        $context->setMink($this->getMink());
        $context->setMinkParameters($this->getMinkParameters());

        return $context;
    }
}
