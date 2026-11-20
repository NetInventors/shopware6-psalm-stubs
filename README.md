# netinventors/shopware6-psalm-stubs

Psalm stubs for Shopware 6: they refine docblocks of Shopware classes whose types are too loose for a strict analysis
(e.g. generics). The package is a Psalm plugin; the QA image `netinventors/qa-psalm` (tool chain for Shopware 6.7) loads
it for every plugin.

| Branch / version | Shopware |
| ---------------- | -------- |
| `7.0` / 7.x      | 6.7      |

## Usage

```xml
<plugins>
    <pluginClass class="NetInventors\Shopware6PsalmStubs\Stubs" />
</plugins>
```

`Stubs` registers every file `stubs/**/*.stub.php` that applies to the installed `shopware/core` and keeps the stubbed
classes user defined (see below).

## Versioned stubs

A stub replaces the docblock and signature of the Shopware member for Psalm, it does not add to it. When Shopware improves
or changes the annotation later, the stub would hide that. Like a polyfill, a stub can therefore be limited to the Shopware
versions that need it, with a Composer constraint in its file header (before `namespace`):

```php
<?php declare(strict_types=1);

// @shopware-version <6.7.20

namespace Shopware\Core\Framework\Struct;
```

The plugin reads the installed version of `shopware/core` of the analysed project (Composer runtime API) and skips stubs
whose constraint does not match. Without the tag a stub applies to the whole line of the package; with an unknown version
(no `shopware/core` installed) every stub applies.

## Drift detection

[signatures.json](signatures.json) records the original docblock and signature of every stubbed member (class, methods,
properties, constants) as found in `shopware/core`. `composer signatures:check` (part of `composer test`) compares them
with the installed release and fails on a difference, with a diff of the change.

When it fails: look at the change of Shopware, then adapt the stub, limit it with `@shopware-version` or remove it, and
run `composer signatures:update`. New stubs need `composer signatures:update` as well.

The CI installs the newest `shopware/core` of the line on every run (`composer.lock` is not committed), so a weekly
pipeline schedule notices changes of new Shopware releases.

## Stubbed classes stay user defined

Psalm treats every class of a stub file as not user defined, i.e. like an internal PHP class. For classes extending a
stubbed Shopware class that means: every overriding method needs a native return type (the PHP 8.1 rule for internal
classes, `MethodSignatureMustProvideReturnType`, e.g. for `@method X[] getElements()` of entity collections), and the
parameter types are no longer compared (`MoreSpecificImplementedParamType` and others disappear).
`StubbedClassesAreUserDefined` sets the classes of this package's stubs back to user defined, Shopware classes are
userland code.

## Development

```sh
composer install          # shopware/core of the line as require-dev
composer test             # PHPUnit: StubFile, Psalm integration (tests/fixtures), signatures
composer signatures:update
```
