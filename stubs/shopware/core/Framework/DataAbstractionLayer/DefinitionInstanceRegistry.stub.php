<?php declare(strict_types=1);

namespace Shopware\Core\Framework\DataAbstractionLayer;

use Shopware\Core\Framework\Log\Package;

#[Package('framework')]
class DefinitionInstanceRegistry
{
    /**
     * @return EntityRepository<EntityCollection<Entity>>
     */
    public function getRepository(string $entityName): EntityRepository;
}
