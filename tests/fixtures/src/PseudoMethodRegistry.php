<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PsalmStubs\Fixture;

use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;

/**
 * A pseudo method without native return type, like "@method X[] getElements()" of entity collections. DefinitionInstanceRegistry
 * is stubbed: without StubbedClassesAreUserDefined Psalm treats it like an internal PHP class and reports
 * MethodSignatureMustProvideReturnType.
 *
 * @method EntityRepository getRepository(string $entityName)
 */
class PseudoMethodRegistry extends DefinitionInstanceRegistry
{
}
