<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Struct;

use Shopware\Core\Framework\FrameworkException;
use Shopware\Core\Framework\Log\Package;

#[Package('framework')]
abstract class Collection extends Struct implements \IteratorAggregate, \Countable
{
    /**
     * @template T of TElement
     *
     * @param \Closure(T): bool $closure
     *
     * @return static<T>
     */
    public function filter(\Closure $closure): static;
}
