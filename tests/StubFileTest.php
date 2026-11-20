<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PsalmStubs\Tests;

use NetInventors\Shopware6PsalmStubs\StubFile;
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
     * @return iterable<string, array{string, ?string, ?string, bool}>
     */
    public static function versions(): iterable
    {
        yield 'no constraint' => ['', null, '6.7.16.1', true];
        yield 'unknown Shopware version' => ["// @shopware-version <6.7.20\n", '<6.7.20', null, true];
        yield 'inside the range' => ["// @shopware-version <6.7.20\n", '<6.7.20', '6.7.16.1', true];
        yield 'above the range' => ["// @shopware-version <6.7.20\n", '<6.7.20', '6.7.20.0', false];
        yield 'docblock syntax' => ["/**\n * @shopware-version >=6.7.0 <6.7.20\n */\n", '>=6.7.0 <6.7.20', '6.6.10.0', false];
    }

    #[DataProvider('versions')]
    public function testConstraintFromTheFileHeader(string $header, ?string $constraint, ?string $version, bool $applies): void
    {
        $stub = StubFile::fromFile($this->write($header));

        self::assertSame($constraint, $stub->constraint);
        self::assertSame($applies, $stub->appliesTo($version));
    }

    public function testTagsAfterTheNamespaceAreIgnored(): void
    {
        $file = $this->directory . '/Late.stub.php';
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

    public function testEveryStubOfThePackageHasAValidHeader(): void
    {
        $stubs = StubFile::all();

        self::assertNotEmpty($stubs);

        foreach ($stubs as $stub) {
            self::assertTrue(StubFile::isStub($stub->path), $stub->path);
        }
    }

    private function write(string $header): string
    {
        $file = $this->directory . '/Example.stub.php';
        file_put_contents($file, "<?php declare(strict_types=1);\n\n" . $header . "\nnamespace Foo;\n\nclass Example {}\n");

        return $file;
    }
}
