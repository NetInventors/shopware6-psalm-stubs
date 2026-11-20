# Entscheidungen

## Versionen pro Stub-Datei, nicht pro Element (2026-11-20)

**Entscheidung:** Ein `@shopware-version`-Constraint gilt für eine ganze Stub-Datei. Elementweise Versionen (Methode,
Property, Konstante), wie die `#[PhpStormStubsElementAvailable]`-Attribute von `jetbrains/phpstorm-stubs`, bauen wir erst,
wenn wirklich ein Fall auftaucht, in dem nur eine Methode abweicht und die Klasse sonst gleich bleibt.

**Warum:**

- Psalm kennt solche Attribute nicht. Zwei Varianten derselben Methode in einem Stub wären für Psalm eine doppelte
  Methode. Elementweise Versionen erfordern deshalb, die Stubs zur Laufzeit zu filtern (PhpParser) und in ein
  temporäres Verzeichnis zu schreiben, bevor sie registriert werden. `StubbedClassesAreUserDefined` und
  `bin/stub-signatures` müssten die erzeugten Dateien ebenfalls behandeln.
- Kein aktueller Stub braucht das: Der einzige Stub mit zwei Varianten, `Collection`, unterscheidet sich in den
  Klassen-Templates (`TKey`), nicht in einzelnen Methoden.
- Ein späterer Umstieg ist für Nutzer kein Bruch, der Mechanismus ist intern.

**Vorerst verworfen:** ein Klassen-Attribut (z. B. `#[ShopwareVersion('<6.7.7')]`) statt des Kommentars im Dateikopf. Es
kann nicht mehr als der Kommentar, und mit der geplanten Endung `.phpstub` indexiert die IDE die Stubs nicht, die
IDE-Unterstützung eines Attributs entfällt also.
