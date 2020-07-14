![Alt text](src/Resources/public/logo.png?raw=true "Marko Cupic")


# Contao Bundle Creator

!!! Dieses Modul befindet sich im Entwicklungsstadium.
!!! Achtung: Das Modul ist für Entwickler gedacht, und generiert eine Basis (Boilerplate) für ein Contao Bundle.
!!! ***Bereits bestehende Dateien werden überschrieben.***

## Inbetriebsetzung des Bundles
* Bundle generieren
* Das passende repository in github anlegen.
* Danach die composer.json im Root der Webseite wie folgt anpassen

```
 "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/vendorname/my-new-bundle"
    }
  ],
  "require": {
    "php": "^7.1",
    "vendorname/my-new-bundle": "dev-master"
  },
```

* Contao Manager starten und ein vollständiges Paketupdate durch führen
* Installtool aufrufen und Datenbankupdate durchführen
