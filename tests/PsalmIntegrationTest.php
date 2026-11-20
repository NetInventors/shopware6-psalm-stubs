<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PsalmStubs\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Runs Psalm with the plugin on tests/fixtures (classes extending stubbed Shopware classes, shopware/core of
 * require-dev).
 */
final class PsalmIntegrationTest extends TestCase
{
    /**
     * @var array<string, list<string>>|null issue type => files
     */
    private static ?array $issues = null;

    public function testStubbedClassesKeepTheChecksOfUserDefinedClasses(): void
    {
        $issues = self::issues();

        self::assertArrayNotHasKey(
            'MethodSignatureMustProvideReturnType',
            $issues,
            'A pseudo method overriding a stubbed Shopware class must not need a native return type.',
        );
        self::assertContains(
            'StrictSearchResult.php',
            $issues['MoreSpecificImplementedParamType'] ?? [],
            'Narrowing a parameter of a stubbed Shopware class must be reported.',
        );
    }

    /**
     * @return array<string, list<string>>
     */
    private static function issues(): array
    {
        if (self::$issues !== null) {
            return self::$issues;
        }

        $root    = dirname(__DIR__);
        $command = sprintf(
            '%s %s --config=%s --root=%s --no-cache --no-progress --output-format=json --threads=1 2>/dev/null',
            escapeshellarg(PHP_BINARY),
            escapeshellarg($root . '/vendor/bin/psalm'),
            escapeshellarg(__DIR__ . '/fixtures/psalm.xml'),
            escapeshellarg($root),
        );

        exec($command, $output);

        $report = json_decode(implode("\n", $output), true);
        self::assertIsArray($report, "Psalm did not return JSON:\n" . implode("\n", $output));

        self::$issues = [];

        foreach ($report as $issue) {
            self::$issues[(string) $issue['type']][] = basename((string) $issue['file_name']);
        }

        return self::$issues;
    }
}
