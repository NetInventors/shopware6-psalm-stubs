<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Struct;

use Shopware\Core\Framework\FrameworkException;
use Shopware\Core\Framework\Log\Package;

#[Package('framework')]
abstract class Collection extends Struct implements \IteratorAggregate, \Countable
{
    /**
     * @param \Closure(TElement): bool $closure
     *                                         
     * @return static<TElement>
     */
    public function filter(\Closure $closure): static;
}
