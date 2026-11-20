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
powerful as the comment, and with the planned `.phpstub` extension the IDE does not index the stubs, so the IDE support
of an attribute is lost.
