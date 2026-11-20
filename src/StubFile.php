<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PsalmStubs;

use Composer\Semver\Semver;
use Composer\Semver\VersionParser;
use Symfony\Component\Finder\Finder;

/**
 * A stub file of this package and the Shopware versions it applies to.
 *
 * Like a polyfill, every stub is limited to the Shopware versions that need it with a Composer constraint in its file
 * header (before "namespace"), e.g. when Shopware fixes the annotation itself in 6.7.20:
 *
 *     <?php declare(strict_types=1);
 *
 *     // @shopware-version >=6.7 <6.7.20
 *
 *     namespace Shopware\Core\...;
 *
 * Stubs of the same class for different Shopware versions are variants: "Collection.phpstub",
 * "Collection.legacy.phpstub". The part after the first dot only tells the files apart, the constraint decides.
 */
final readonly class StubFile
{
    public const DIRECTORY = __DIR__ . '/../stubs';
    public const EXTENSION = '.phpstub';

    private function __construct(
        public string $path,
        public ?string $constraint,
    ) {
    }

    /**
     * @return list<self>
     */
    public static function all(): array
    {
        $finder = (new Finder())->files()->in(self::DIRECTORY)->name('*' . self::EXTENSION)->sortByName();
        $stubs  = [];

        foreach ($finder as $file) {
            $stubs[] = self::fromFile((string) $file->getRealPath());
        }

        return $stubs;
    }

    /**
     * The stubs to register for the installed Shopware version. An unknown version (no shopware/core installed, e.g.
     * analysing a library) registers one variant per class: the one for the newest Shopware version.
     *
     * @return list<self>
     */
    public static function forVersion(?string $shopwareVersion): array
    {
        if ($shopwareVersion !== null) {
            return array_values(array_filter(self::all(), static fn (self $stub): bool => $stub->appliesTo($shopwareVersion)));
        }

        $newest = [];

        foreach (self::all() as $stub) {
            $current = $newest[$stub->variantOf()] ?? null;

            if ($current === null || version_compare($stub->upperBound(), $current->upperBound(), '>')) {
                $newest[$stub->variantOf()] = $stub;
            }
        }

        return array_values($newest);
    }

    public static function fromFile(string $path): self
    {
        $header = (string) strstr((string) file_get_contents($path), "\nnamespace ", true);
        $count  = preg_match_all('/@shopware-version\s+([^\r\n*]+)/', $header, $matches);

        if ($count > 1) {
            throw new \InvalidArgumentException(sprintf('%s has more than one @shopware-version.', $path));
        }

        $constraint = $count === 1 ? trim($matches[1][0]) : null;

        if ($constraint !== null) {
            // Rejects an invalid constraint when the stub is loaded, not only when a version is checked against it
            (new VersionParser())->parseConstraints($constraint);
        }

        return new self($path, $constraint);
    }

    public function appliesTo(string $shopwareVersion): bool
    {
        return $this->constraint === null || Semver::satisfies($shopwareVersion, $this->constraint);
    }

    /**
     * The file without the variant name: stubs with the same value stub the same class.
     */
    public function variantOf(): string
    {
        $name = basename($this->path, self::EXTENSION);

        return \dirname($this->path) . '/' . explode('.', $name, 2)[0];
    }

    /**
     * The normalized upper bound of the constraint ("9999999-dev" without one).
     */
    public function upperBound(): string
    {
        if ($this->constraint === null) {
            return '9999999-dev';
        }

        return (new VersionParser())->parseConstraints($this->constraint)->getUpperBound()->getVersion();
    }

    public static function isStub(string $path): bool
    {
        $directory = realpath(self::DIRECTORY);
        $file      = realpath($path);

        return $directory !== false && $file !== false && str_starts_with($file, $directory . \DIRECTORY_SEPARATOR);
    }
}
