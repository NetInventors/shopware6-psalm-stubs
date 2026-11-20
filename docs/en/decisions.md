# Decisions

## Versions per stub file, not per element (2026-11-20)

**Decision:** A `@shopware-version` constraint applies to a whole stub file. We build versions per element (method,
property, constant), like the `#[PhpStormStubsElementAvailable]` attributes of `jetbrains/phpstorm-stubs`, only when a
real case comes up in which just one method differs and the rest of the class stays the same.

**Why:**

- Psalm does not know such attributes. Two variants of the same method in one stub would be a duplicate method for
  Psalm. Versions per element therefore need the stubs to be filtered at runtime (PhpParser) and written to a temporary
  directory before they are registered. `StubbedClassesAreUserDefined` and `bin/stub-signatures` would have to handle the
  generated files as well.
- No current stub needs it: the only stub with two variants, `Collection`, differs in its class templates (`TKey`), not in
  single methods.
- Switching later is no break for users, the mechanism is internal.

**Rejected for now:** a class attribute (e.g. `#[ShopwareVersion('<6.7.7')]`) instead of the header comment. It is as
powerful as the comment, and with the `.phpstub` extension the IDE does not index the stubs, so the IDE support of an
attribute is lost.

## 10.0: version independent of Shopware (2026-11-20)

**Decision:** The release after 7.x is 10.0.0; from then on the major version only counts breaking changes of the
package.

**Why:** Like the taskfile and `shopware6-psalm-plugins`, 7.x stood for Shopware 6.7, so 8.x would look like Shopware 6.8.
Since one branch serves every Shopware version (see below), that link does not hold any more. Skipping 8 and 9 makes the
break visible. Which Shopware versions are supported is stated by the constraints of the stubs.

## 10.0: one branch, `.phpstub`, bounded constraints (2026-11-20)

- **One branch `main` instead of a branch per Shopware line.** The constraints already select the stubs per Shopware
  version, a branch per line would only duplicate them. There never was a 6.x line: the package came up with Shopware
  6.7. Stubs for a new Shopware minor are added next to the existing ones.
- **Extension `.phpstub` instead of `.stub.php`**, like Psalm's own stubs (PHPStan uses `.stub`): IDEs index `.php` files
  in `vendor/` and would see a second definition of the Shopware class. Renaming the files breaks references to them,
  hence the major version.
- **Every stub has a constraint with a lower and an upper bound** (`~6.7.0` for a whole minor). A stub without an upper
  bound would apply to a new Shopware minor without having been checked against it.
- **Variants of a class** share the file name up to the first dot (`Collection.phpstub`, `Collection.legacy.phpstub`);
  the name only tells the files apart, the constraint decides. Their constraints must not overlap, otherwise Psalm gets
  two stubs of the same class. Without a known Shopware version the variant with the highest upper bound applies.
