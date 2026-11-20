<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PsalmStubs;

use Composer\Semver\Semver;
use Symfony\Component\Finder\Finder;

/**
 * A stub file of this package and the Shopware versions it applies to.
 *
 * Like a polyfill, a stub may be limited to the Shopware versions that need it with a Composer constraint in its file
 * header, e.g. when Shopware fixes the annotation itself in 6.7.20:
 *
 *     <?php declare(strict_types=1);
 *
 *     // @shopware-version <6.7.20
 *
 *     namespace Shopware\Core\...;
 *
 * Without the tag the stub applies to every version of the package's line (7.x: Shopware 6.7).
 */
final readonly class StubFile
{
    public const DIRECTORY = __DIR__ . '/../stubs';

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
        $finder = (new Finder())->files()->in(self::DIRECTORY)->name('*.stub.php')->sortByName();
        $stubs  = [];

        foreach ($finder as $file) {
            $stubs[] = self::fromFile((string) $file->getRealPath());
        }

        return $stubs;
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
            (new \Composer\Semver\VersionParser())->parseConstraints($constraint);
        }

        return new self($path, $constraint);
    }

    /**
     * Whether the stub is registered for the installed Shopware version. An unknown version (no shopware/core
     * installed, e.g. analysing a library) registers every stub, as before the constraints existed.
     */
    public function appliesTo(?string $shopwareVersion): bool
    {
        if ($this->constraint === null || $shopwareVersion === null) {
            return true;
        }

        return Semver::satisfies($shopwareVersion, $this->constraint);
    }

    public static function isStub(string $path): bool
    {
        $directory = realpath(self::DIRECTORY);
        $file      = realpath($path);

        return $directory !== false && $file !== false && str_starts_with($file, $directory . \DIRECTORY_SEPARATOR);
    }
}
