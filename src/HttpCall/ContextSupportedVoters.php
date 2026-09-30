<?php

namespace Behatch\HttpCall;

class ContextSupportedVoters implements ContextSupportedVoter
{
    private array $voters = [];

    public function __construct(array $voters = [])
    {
        foreach ($voters as $voter) {
            $this->register($voter);
        }
    }

    public function register(ContextSupportedVoter $voter): void
    {
        $this->voters[] = $voter;
    }

    public function vote(HttpCallResult $httpCallResult): bool
    {
        foreach ($this->voters as $voter) {
            if ($voter->vote($httpCallResult)) {
                if ($voter instanceof FilterableHttpCallResult) {
                    $httpCallResult->update($voter->filter($httpCallResult));
                }

                return true;
            }
        }

        return false;
    }
}
