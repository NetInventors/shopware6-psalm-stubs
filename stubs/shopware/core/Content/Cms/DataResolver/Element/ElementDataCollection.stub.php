<?php declare(strict_types=1);

// @shopware-version <6.7.2
// Shopware annotates ElementDataCollection itself since 6.7.2 (EntitySearchResult<covariant EntityCollection<covariant Entity>>).

namespace Shopware\Core\Content\Cms\DataResolver\Element;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\Log\Package;

/**
 * @implements \IteratorAggregate<array-key, EntitySearchResult<EntityCollection>>
 */
#[Package('discovery')]
class ElementDataCollection implements \IteratorAggregate, \Countable
{
    /**
     * @var array<string, EntitySearchResult<EntityCollection>>
     */
    protected array $searchResults = [];

    /**
     * @param EntitySearchResult<EntityCollection> $entitySearchResult
     */
    public function add(string $key, EntitySearchResult $entitySearchResult): void;

    /**
     * @return EntitySearchResult<EntityCollection>|null
     */
    public function get(string $key): ?EntitySearchResult;

    /**
     * @return \Traversable<string, EntitySearchResult<EntityCollection>>
     */
    public function getIterator(): \Traversable;
}
