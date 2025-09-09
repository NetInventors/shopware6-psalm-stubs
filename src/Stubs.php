<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PsalmStubs;

use Psalm\Plugin\PluginEntryPointInterface;
use Psalm\Plugin\RegistrationInterface;

class Stubs implements PluginEntryPointInterface
{
    public function __invoke(RegistrationInterface $registration, \SimpleXMLElement|null $config = null): void
    {
        foreach (\glob(__DIR__ . '/../stubs/**/*.phpstub') as $stub) {
            $registration->addStubFile($stub);
        }
    }
}
