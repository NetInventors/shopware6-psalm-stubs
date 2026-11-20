<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PsalmStubs\Fixture;

use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;

/**
 * Narrows a parameter of the stubbed DefinitionInstanceRegistry: Psalm must report MoreSpecificImplementedParamType, it
 * skips the check for classes that are not user defined.
 */
class StrictRegistry extends DefinitionInstanceRegistry
{
    /**
     * @param non-empty-string $entityName
     */
    #[\Override]
    public function getRepository(string $entityName): EntityRepository
    {
        return parent::getRepository($entityName);
    }
}
