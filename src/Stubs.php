<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PsalmStubs;

use Psalm\Plugin\PluginEntryPointInterface;
use Psalm\Plugin\RegistrationInterface;

/**
 * Registers the stubs of this package that apply to the installed Shopware version (see StubFile) and keeps the stubbed
 * classes user defined (see StubbedClassesAreUserDefined).
 */
class Stubs implements PluginEntryPointInterface
{
    #[\Override]
    public function __invoke(RegistrationInterface $registration, \SimpleXMLElement|null $config = null): void
    {
        foreach (StubFile::forVersion(ShopwareVersion::installed()) as $stub) {
            $registration->addStubFile($stub->path);
        }

        // Psalm checks the handler with class_exists(..., false): load it first
        class_exists(StubbedClassesAreUserDefined::class);
        $registration->registerHooksFromClass(StubbedClassesAreUserDefined::class);
    }
}
