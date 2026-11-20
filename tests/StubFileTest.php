<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PsalmStubs\Tests;

use Composer\Semver\VersionParser;
use NetInventors\Shopware6PsalmStubs\StubFile;
use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class StubFileTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/stub-file-test-' . bin2hex(random_bytes(4));
        mkdir($this->directory);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->directory . '/*') ?: []);
        rmdir($this->directory);
    }

    /**
     * @return iterable<string, array{string, ?string, string, bool}>
     */
    public static function versions(): iterable
    {
        yield 'no constraint' => ['', null, '6.7.16.1', true];
        yield 'inside the range' => ["// @shopware-version <6.7.20\n", '<6.7.20', '6.7.16.1', true];
        yield 'above the range' => ["// @shopware-version <6.7.20\n", '<6.7.20', '6.7.20.0', false];
        yield 'docblock syntax' => ["/**\n * @shopware-version >=6.7.0 <6.7.20\n */\n", '>=6.7.0 <6.7.20', '6.6.10.0', false];
    }

    #[DataProvider('versions')]
    public function testConstraintFromTheFileHeader(string $header, ?string $constraint, string $version, bool $applies): void
    {
        $stub = StubFile::fromFile($this->write($header));

        self::assertSame($constraint, $stub->constraint);
        self::assertSame($applies, $stub->appliesTo($version));
    }

    public function testTagsAfterTheNamespaceAreIgnored(): void
    {
        $file = $this->directory . '/Late.phpstub';
        file_put_contents($file, "<?php\n\nnamespace Foo;\n\n// @shopware-version <6.0\nclass Late {}\n");

        self::assertNull(StubFile::fromFile($file)->constraint);
    }

    public function testInvalidConstraintIsRejected(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        StubFile::fromFile($this->write("// @shopware-version not a constraint\n"));
    }

    public function testOnlyOneConstraintIsAllowed(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        StubFile::fromFile($this->write("// @shopware-version <6.7.20\n// @shopware-version <6.7.21\n"));
    }

    public function testVariantName(): void
    {
        self::assertSame($this->directory . '/Collection', StubFile::fromFile($this->write('', 'Collection.legacy'))->variantOf());
        self::assertSame($this->directory . '/Collection', StubFile::fromFile($this->write('', 'Collection'))->variantOf());
    }

    public function testUnknownVersionRegistersTheVariantForTheNewestShopware(): void
    {
        $paths = array_map(static fn (StubFile $stub): string => basename($stub->path), StubFile::forVersion(null));

        self::assertContains('Collection.phpstub', $paths);
        self::assertNotContains('Collection.legacy.phpstub', $paths);
    }

    public function testKnownVersionRegistersTheMatchingVariant(): void
    {
        $paths = array_map(static fn (StubFile $stub): string => basename($stub->path), StubFile::forVersion('6.7.0.0'));

        self::assertContains('Collection.legacy.phpstub', $paths);
        self::assertNotContains('Collection.phpstub', $paths);
        self::assertContains('Criteria.phpstub', $paths);
        self::assertNotContains('Criteria.phpstub', array_map(static fn (StubFile $stub): string => basename($stub->path), StubFile::forVersion('6.7.5.0')));
    }

    /**
     * A stub without an upper bound would apply to the next Shopware minor unchecked, one without a lower bound to an
     * older one.
     */
    public function testEveryStubOfThePackageHasABoundedConstraint(): void
    {
        $stubs = StubFile::all();

        self::assertNotEmpty($stubs);

        foreach ($stubs as $stub) {
            self::assertTrue(StubFile::isStub($stub->path), $stub->path);
            self::assertNotNull($stub->constraint, $stub->path . ' has no @shopware-version.');

            $constraint = (new VersionParser())->parseConstraints($stub->constraint);

            self::assertFalse($constraint->getLowerBound()->isZero(), $stub->path . ' has no lower bound.');
            self::assertFalse($constraint->getUpperBound()->isPositiveInfinity(), $stub->path . ' has no upper bound.');
        }
    }

    /**
     * Psalm would get two stubs of the same class, the result would depend on the order.
     */
    public function testVariantsOfAClassDoNotOverlap(): void
    {
        $parser   = new VersionParser();
        $variants = [];

        foreach (StubFile::all() as $stub) {
            $variants[$stub->variantOf()][] = $stub;
        }

        foreach ($variants as $stubs) {
            foreach ($stubs as $i => $a) {
                foreach (\array_slice($stubs, $i + 1) as $b) {
                    self::assertFalse(
                        $parser->parseConstraints((string) $a->constraint)->matches($parser->parseConstraints((string) $b->constraint)),
                        sprintf('%s and %s overlap.', basename($a->path), basename($b->path)),
                    );
                }
            }
        }
    }

    /**
     * Variants are found by their file name: the files of one class must share it, two classes must not.
     */
    public function testVariantsShareTheFileNameOfTheirClass(): void
    {
        $parser  = (new ParserFactory())->createForNewestSupportedVersion();
        $classes = [];

        foreach (StubFile::all() as $stub) {
            $nodes = $parser->parse((string) file_get_contents($stub->path)) ?? [];
            $names = array_map(
                static fn (Node\Stmt\ClassLike $class): string => $class->namespacedName?->toString() ?? (string) $class->name,
                (new NodeFinder())->findInstanceOf((new NodeTraverser(new NameResolver()))->traverse($nodes), Node\Stmt\ClassLike::class),
            );

            foreach ($names as $name) {
                $classes[$name][$stub->variantOf()] = true;
            }
        }

        $variants = [];

        foreach ($classes as $name => $files) {
            self::assertCount(1, $files, $name . ' is stubbed in files with different names.');
            $variants[array_key_first($files)][] = $name;
        }

        foreach ($variants as $file => $names) {
            self::assertCount(1, array_unique($names), $file . ' stubs more than one class.');
        }
    }

    private function write(string $header, string $name = 'Example'): string
    {
        $file = $this->directory . '/' . $name . '.phpstub';
        file_put_contents($file, "<?php declare(strict_types=1);\n\n" . $header . "\nnamespace Foo;\n\nclass Example {}\n");

        return $file;
    }
}
