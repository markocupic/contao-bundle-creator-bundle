![Alt text](src/Resources/public/logo.png?raw=true "Marko Cupic")


# Contao Bundle Creator (Boilerplate für eigene Erweiterungen)

Das Modul ist für Entwickler gedacht, und generiert ein Grundgerüst (Boilerplate) für ein Contao Bundle.


## Via Contao Backend das Bundle konfigurieren

![Alt text](src/Resources/public/backend.png?raw=true "Backend")


## Verzeichnisstruktur
Folgende Verzeichnisstruktur wird im vendor Vezeichnis abgelegt.

![Alt text](src/Resources/public/file-tree.png?raw=true "Verzeichnisstruktur")


## Inbetriebsetzung des Bundles
* Bundle generieren
* Das passende repository in github anlegen.
* Danach a) die composer.json im Root der Webseite wie folgt anpassen

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
* Oder b) die Erweiterung als Paket von github herunterladen und im Contao Manager hochladen
* Contao Manager starten und ein vollständiges Paketupdate durchführen
* Installtool aufrufen und Datenbankupdate durchführen
