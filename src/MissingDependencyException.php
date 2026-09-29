<?php

namespace Behatch;

/**
 * Thrown when a step needs an optional package that is not installed.
 */
final class MissingDependencyException extends \LogicException
{
}
