<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PsalmStubs\Tests;

use PHPUnit\Framework\TestCase;

/**
 * The stubbed members are unchanged in the installed shopware/core (see bin/stub-signatures). A failure means Shopware
 * changed a docblock or signature this package stubs: check the stub, then "composer signatures:update".
 */
final class SignaturesTest extends TestCase
{
    public function testShopwareDidNotChangeTheStubbedMembers(): void
    {
        exec(
            sprintf('%s %s check 2>&1', escapeshellarg(PHP_BINARY), escapeshellarg(dirname(__DIR__) . '/bin/stub-signatures')),
            $output,
            $code,
        );

        self::assertSame(0, $code, implode("\n", $output));
    }
}
