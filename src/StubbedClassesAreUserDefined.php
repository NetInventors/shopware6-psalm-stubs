<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PsalmStubs;

use Psalm\Plugin\EventHandler\AfterClassLikeVisitInterface;
use Psalm\Plugin\EventHandler\Event\AfterClassLikeVisitEvent;

/**
 * Keeps the classes of the stubs of this package user defined.
 *
 * Psalm marks every class of a stub file as not user defined (ClassLikeNodeScanner: user_defined = !is_stub_file), so a
 * stubbed Shopware class is treated like an internal PHP class. That changes the checks of the classes extending it:
 * - MethodComparator demands a native return type for every method overriding it (the PHP 8.1 rule for internal
 *   classes), e.g. MethodSignatureMustProvideReturnType for "@method X[] getElements()" of an entity collection;
 * - it skips the comparison of parameter types, e.g. MoreSpecificImplementedParamType is no longer reported.
 * Shopware classes are userland code, a stub only refines their types.
 */
final class StubbedClassesAreUserDefined implements AfterClassLikeVisitInterface
{
    #[\Override]
    public static function afterClassLikeVisit(AfterClassLikeVisitEvent $event): void
    {
        $storage = $event->getStorage();
        $file    = $storage->location?->file_path;

        if ($file !== null && StubFile::isStub($file)) {
            $storage->user_defined = true;
        }
    }
}
