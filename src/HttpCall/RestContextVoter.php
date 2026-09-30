<?php

namespace Behatch\HttpCall;

class RestContextVoter implements ContextSupportedVoter, FilterableHttpCallResult
{
    public function vote(HttpCallResult $httpCallResult): bool
    {
        return $httpCallResult->getValue() instanceof \Behat\Mink\Element\DocumentElement;
    }

    public function filter(HttpCallResult $httpCallResult): string
    {
        return $httpCallResult->getValue()->getContent();
    }
}
