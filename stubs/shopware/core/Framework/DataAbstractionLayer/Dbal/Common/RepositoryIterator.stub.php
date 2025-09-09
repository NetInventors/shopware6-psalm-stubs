<?php declare(strict_types=1);

namespace Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\Log\Package;

/**
 * @template TEntityCollection of EntityCollection
 */
#[Package('framework')]
class RepositoryIterator
{
    /**
     * @param EntityRepository<TEntityCollection> $repository
     */
    public function __construct(
        EntityRepository $repository,
        Context $context,
        ?Criteria $criteria = null
    );
}
