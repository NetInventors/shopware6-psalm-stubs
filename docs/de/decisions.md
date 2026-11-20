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
kann nicht mehr als der Kommentar, und mit der Endung `.phpstub` indexiert die IDE die Stubs nicht, die
IDE-Unterstützung eines Attributs entfällt also.

## 10.0: Version unabhängig von Shopware (2026-11-20)

**Entscheidung:** Auf 7.x folgt 10.0.0; ab dann zählt die Major-Version nur noch BC-Brüche des Pakets.

**Warum:** Wie beim Taskfile und bei `shopware6-psalm-plugins` stand 7.x für Shopware 6.7, 8.x sähe also nach Shopware
6.8 aus. Da ein Branch alle Shopware-Versionen bedient (siehe unten), gilt diese Kopplung nicht mehr. Das Überspringen
von 8 und 9 macht den Bruch sichtbar. Welche Shopware-Versionen unterstützt sind, sagen die Constraints der Stubs.

## 10.0: ein Branch, `.phpstub`, begrenzte Constraints (2026-11-20)

- **Ein Branch `main` statt eines Branches pro Shopware-Linie.** Die Constraints wählen die Stubs bereits pro
  Shopware-Version aus, ein Branch pro Linie würde das nur verdoppeln. Eine 6.x-Linie gab es nie: Das Paket entstand mit
  Shopware 6.7. Stubs für eine neue Shopware-Minor kommen neben die bestehenden.
- **Endung `.phpstub` statt `.stub.php`**, wie Psalms eigene Stubs (PHPStan nutzt `.stub`): IDEs indexieren `.php`-Dateien
  in `vendor/` und sähen eine zweite Definition der Shopware-Klasse. Die Umbenennung bricht Verweise auf die Dateien,
  daher die Major-Version.
- **Jeder Stub hat einen Constraint mit Unter- und Obergrenze** (`~6.7.0` für eine ganze Minor). Ein Stub ohne
  Obergrenze würde für eine neue Shopware-Minor gelten, ohne dagegen geprüft zu sein.
- **Varianten einer Klasse** teilen den Dateinamen bis zum ersten Punkt (`Collection.phpstub`,
  `Collection.legacy.phpstub`); der Name unterscheidet nur die Dateien, der Constraint entscheidet. Ihre Constraints
  dürfen sich nicht überschneiden, sonst bekäme Psalm zwei Stubs derselben Klasse. Ohne bekannte Shopware-Version gilt
  die Variante mit der höchsten Obergrenze.
