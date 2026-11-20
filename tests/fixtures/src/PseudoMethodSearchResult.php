<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PsalmStubs\Fixture;

use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;

/**
 * A pseudo method without native return type, like "@method X[] getElements()" of entity collections. EntitySearchResult
 * is stubbed: without StubbedClassesAreUserDefined Psalm treats it like an internal PHP class and reports
 * MethodSignatureMustProvideReturnType.
 *
 * @psalm-suppress InvalidExtendClass EntitySearchResult is @final, the fixture only needs a stubbed parent
 *
 * @method static filter(\Closure $closure)
 */
final class PseudoMethodSearchResult extends EntitySearchResult
{
}
