<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PsalmStubs;

use Psalm\Plugin\PluginEntryPointInterface;
use Psalm\Plugin\RegistrationInterface;
use Symfony\Component\Finder\Finder;

class Stubs implements PluginEntryPointInterface
{
    public function __invoke(RegistrationInterface $registration, \SimpleXMLElement|null $config = null): void
    {
        $finder = new Finder();

        $finder->files()
            ->in(__DIR__ . '/../stubs')
            ->name('*.stub.php')
        ;

        foreach ($finder as $stub) {
            $registration->addStubFile($stub->getRealPath());
        }
    }
}
