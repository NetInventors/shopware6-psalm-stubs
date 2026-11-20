# netinventors/shopware6-psalm-stubs

Psalm stubs for Shopware 6: they refine docblocks of Shopware classes whose types are too loose for a strict analysis
(e.g. generics). The package is a Psalm plugin; the QA image `netinventors/qa-psalm` (tool chain for Shopware 6.7) loads
it for every plugin.

| Branch / version                   | Shopware |
| ---------------------------------- | -------- |
| `main` / 10.x                      | 6.7      |
| `7.0` / 7.x (no longer maintained) | 6.7      |

One branch serves every Shopware version: each stub declares the Shopware versions it applies to (see
[Versioned stubs](#versioned-stubs)). Stubs for a new Shopware minor are added next to the existing ones.

From 10.0 on the major version is not tied to Shopware any more (7.x stood for Shopware 6.7), it only counts breaking
changes; 8 and 9 were skipped so that no version looks like Shopware 6.8 or 6.9.

## Usage

```xml
<plugins>
    <pluginClass class="NetInventors\Shopware6PsalmStubs\Stubs" />
</plugins>
```

`Stubs` registers every file `stubs/**/*.phpstub` that applies to the installed `shopware/core` and keeps the stubbed
classes user defined (see below).

## Stubs

| Stub                             | Shopware       | Why                                                                                                 |
| -------------------------------- | -------------- | --------------------------------------------------------------------------------------------------- |
| `EntitySearchResult`             | `~6.7.0`       | compatibility: Psalm does not understand `@phpstan-type … template-type<…>` of Shopware             |
| `BundleConfigGeneratorInterface` | `~6.7.0`       | `@psalm-type BundleConfig` for Psalm                                                                |
| `Collection`                     | `>=6.7.7 <6.8` | `filter()` narrows to the parameter type of the closure (`static<T>`), with the templates of 6.7.7+ |
| `Collection` (`legacy`)          | `>=6.7 <6.7.7` | the same, with the templates before 6.7.7 (no `TKey`)                                               |
| `DefinitionInstanceRegistry`     | `>=6.7 <6.7.2` | `getRepository()` returns a typed repository; Shopware annotates it itself since 6.7.2              |
| `ElementDataCollection`          | `>=6.7 <6.7.2` | same as Shopware up to 6.7.1; Shopware adds `covariant` since 6.7.2                                 |
| `RepositoryIterator`             | `>=6.7 <6.7.2` | same as Shopware up to 6.7.1; Shopware adds `covariant` since 6.7.2                                 |
| `Criteria`                       | `>=6.7 <6.7.5` | same as Shopware up to 6.7.4; Shopware adds `@template IDStructure` since 6.7.5                     |

For a stub Shopware covers itself later, the upper bound is the first Shopware release whose own annotation differs from the stub (checked for every release
from 6.7.0.0 to 6.7.16.1). Below it the stub keeps the types of older projects stable, from it on Shopware's annotation
applies unchanged.

## Versioned stubs

A stub replaces the docblock and signature of the Shopware member for Psalm, it does not add to it. When Shopware improves
or changes the annotation later, the stub would hide that. Like a polyfill, a stub can therefore be limited to the Shopware
versions that need it, with a Composer constraint in its file header (before `namespace`):

```php
<?php declare(strict_types=1);

// @shopware-version >=6.7 <6.7.20

namespace Shopware\Core\Framework\Struct;
```

The plugin reads the installed version of `shopware/core` of the analysed project (Composer runtime API) and skips stubs
whose constraint does not match.

- Every stub has a constraint with a lower and an upper bound (`~6.7.0` for a whole minor), so a new Shopware minor does
  not get a stub that was never checked against it. A test enforces it.
- Stubs of the same class for different versions are variants: `Collection.phpstub`, `Collection.legacy.phpstub`. The
  name after the first dot only tells the files apart, the constraint decides. Their constraints must not overlap (test).
- With an unknown version (no `shopware/core` installed, e.g. analysing a library) the variant with the highest upper
  bound of each class applies.

Constraints apply to whole files, not to single methods: see [decisions](docs/en/decisions.md)
([deutsch](docs/de/decisions.md)).

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

## Upgrade from 7.x to 10.0

- The stubs are named `*.phpstub` instead of `*.stub.php` (IDEs no longer index them as a second definition of the
  Shopware class). Remove references to single stub files of this package from `psalm.xml`; the plugin registers them.
- The plugin class and its configuration are unchanged.
