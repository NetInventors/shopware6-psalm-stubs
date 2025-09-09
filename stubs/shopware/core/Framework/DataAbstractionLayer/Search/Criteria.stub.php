<?php

declare(strict_types=1);

namespace Shopware\Core\Framework\DataAbstractionLayer\Search;

use Shopware\Core\Framework\Log\Package;

/**
 * @final
 */
#[Package('framework')]
class Criteria extends Struct implements \Stringable
{
    /**
     * @var array<string>|array<int, array<string>>
     */
    protected array $ids = [];

    /**
     * @param array<string>|array<array<string, string>>|null $ids
     */
    public function __construct(?array $ids = null, protected int $nestingLevel = 0);

    /**
     * @return array<string>|array<array<string, string>>
     */
    public function getIds(): array;

    /**
     * @param array<string>|array<array<string, string>> $ids
     */
    public function setIds(array $ids): self;
}
