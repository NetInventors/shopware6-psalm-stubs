<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PsalmStubs\Fixture;

use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;

/**
 * Like NetiNextEasyCoupon: without the stub of Collection::filter() Psalm resolves "static" of filter() to Collection,
 * so getPrices() of LineItemCollection "does not exist".
 */
final class CollectionFilter
{
    public function totalOfGoods(LineItemCollection $items, bool $onlyGoods): float
    {
        if ($onlyGoods) {
            $items = $items->filter(static fn (LineItem $item): bool => $item->isGood());
        }

        return $items->getPrices()->sum()->getTotalPrice();
    }
}
