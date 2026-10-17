<?php

namespace Behatch\Tests\Behat;

use Behat\Behat\Context\TranslatableContext;
use Behat\MinkExtension\Context\MinkContext;

/**
 * friends-of-behat/mink-extension v3.0.0-ALPHA.1 declares `\"` in its step patterns but
 * `\\"` in its xliff sources, so translated MinkContext steps are undefined. Fixed upstream
 * by FriendsOfBehat/MinkExtension#65 (unreleased): remove this context with the next release.
 */
final class MinkTranslationContext implements TranslatableContext
{
    public static function getTranslationResources(): array
    {
        $dir = sys_get_temp_dir().'/behatch-mink-i18n';
        if (!is_dir($dir) && !@mkdir($dir) && !is_dir($dir)) {
            throw new \RuntimeException(\sprintf('Unable to create the "%s" directory.', $dir));
        }

        $resources = [];
        foreach (MinkContext::getMinkTranslationResources() as $resource) {
            $resources[] = $target = $dir.'/'.basename($resource);
            // write then rename, so that a concurrent run never reads a partial file
            $tmp = tempnam($dir, 'xliff');
            file_put_contents($tmp, str_replace('\\\\"', '\\"', file_get_contents($resource)));
            rename($tmp, $target);
        }

        return $resources;
    }
}
