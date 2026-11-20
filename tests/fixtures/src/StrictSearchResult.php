<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PsalmStubs\Fixture;

use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Content\Product\ProductEntity;

/**
 * Narrows a parameter of the stubbed EntitySearchResult: Psalm must report MoreSpecificImplementedParamType, it skips
 * the check for classes that are not user defined.
 *
 * @psalm-suppress InvalidExtendClass EntitySearchResult is @final, the fixture only needs a stubbed parent
 */
final class StrictSearchResult extends EntitySearchResult
{
    /**
     * @param iterable<ProductEntity> $elements
     */
    #[\Override]
    protected function createNew(iterable $elements = []): static
    {
        return parent::createNew($elements);
    }
}
