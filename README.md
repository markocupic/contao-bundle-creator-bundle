![passed tests at travis-ci.org](https://travis-ci.org/markocupic/contao-bundle-creator-bundle.svg?branch=master "")


![Marko Cupic](docs/logo.png?raw=true "Marko Cupic")


# Contao Bundle Creator (Boilerplate für eigene Erweiterungen)

Das Modul ist für Entwickler gedacht, und generiert nach Eingabe einiger Parameter ein Grundgerüst (Boilerplate/Skeleton) für ein Contao 4 Bundle.

Es können...
- ein Frontendmodul generiert werden.
- ein Backendmodul generiert werden.
- ein Inhaltselement generiert werden.
- eine custom route (https://myhostname.ch/my_custom) generiert werden.
- eine custom session bag generiert werden.
- eine Basisklasse (mit custom root key) für eine friendly configuration generiert werden.

Alle nötigen Konfigurationsdaten werden automatisch erstellt.

Falls gewünscht, werden auch die für den Betrieb nötigen Einstellungen in der root composer.json automatisch getätigt.
Nach der Generierung ist es lediglich nötig,
- im Contao Manager einen Updatedurchlauf zu starten und mit dem Installtool die Datenbank upzudaten
- oder per Konsole den `php composer update` und `php vendor/bin/contao-console contao:migrate` Befehl auszuführen

## Via Contao Backend das Bundle konfigurieren

![Alt text](docs/backend.png?raw=true "Backend")

## Verzeichnisstruktur
Folgende Verzeichnisstruktur (ohne Resources Verzeichnis) wird im vendor Ordner angelegt.

![Alt text](docs/directory-structure.png?raw=true "Verzeichnisstruktur")


## Inbetriebnahme des Bundles
Nachdem alle Eingaben im Backend gemacht wurden, das Bundle ganz einfach per Knopfdruck generieren lassen.
Die Extension sollte nun im Verzeichnis "vendor" erstellt worden sein und kann auch als ZIP-File heruntergeladen werden.

### Variante A (Auch ohne eigenen github-Account möglich)
In der composer.json folgende 2 Einträge machen:
```
"repositories": [
    {
      "type": "path",
      "url": "/home/myhosting/public_html/dirtyharryproductions.com/vendor/dirtyharrycoding/hello-world-bundle"
    }
  ],
```
In der composer.json den **absoluten Pfad** zum Bundle im vendor-Verzeichnis angeben.
Dieser Schritt kann, wenn so eingestellt, von der Erweiterung auch automatisch erledigt werden.
```
  "require": {
    ....
    ....
    "dirtyharrycoding/hello-world-bundle": "dev-main"
  },
```
Im require-Teil das neu erstellte Bundle registrieren.
Dieser Schritt kann, wenn so eingestellt, von der Erweiterung auch automatisch erledigt werden.

Danach via Contao Manager ein vollständiges Update durchführen und das Installtool aufrufen. Fertig!

___

### Variante B
Die Erweiterung auf github.com hochladen und in der composer.json folgende 2 Einträge machen.
```
"repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/dirtyharrycoding/hello-world-bundle"
    }
  ],
```
In der composer.json den Pfad zum github repo angeben.
```
  "require": {
    ....
    ....
    "dirtyharrycoding/hello-world-bundle": "dev-main"
  },
```
Im require-Teil das neu erstellte Bundle registrieren.

Danach via Contao Manager ein vollständige Update durchführen und das Installtool aufrufen. Fertig!

Bei Variante B kann es sein, dass github.com die Verbindungsanfrage ablehnt.
Die Erstellung eines **Oauth-Access-Tokens** kann hier Abhilfe schaffen.
Das Access Token muss dann in der **config section** der composer.json im Root eingesetzt werden.
[Github Oauth-Access-Token generieren](https://docs.github.com/en/github/authenticating-to-github/creating-a-personal-access-token)
```
   "config": {
     "github-oauth": {
       "github.com": "43dfdfxxxxxxxxxxxxxxxxxxx5645rtzrfhgfe9"
     }
   },
```

___

### Variante C
Die Erweiterung im Backend via "Bundle herunterladen" Button downloaden und dann im Contao Manager als Paket importieren.
Installtool aufrufen. Fertig!

___

### Variante D
Die Erweiterung via github auf packagist.org hochladen und dann via Contao Manager installieren.
Installtool aufrufen. Fertig!

## Anmerkungen
* Falls man in den Einstellungen definiert, dass das Skript während der Erstellung des Bundles auch die die root composer.json anpasst, speichert das Skript zur Sicherheit ein Backup der composer.json in system/tmp ab.
* Bei der Erstellung des Bundles wird im Verzeichnis system/tmp zusätzlich ein zip-package mit dem generierten Bundle abgelegt. Das Package kann per Knopfdruck heruntergeladen werden.

## Templates updatesicher anpassen
Falls man die Standard-Templates anpassen möchte, die der bundle-maker benötigt, um die PHP-Klassen, Konfigurationsdateien, etc. zu generieren,
kann man seine eigene Templates im Verzeichnis templates/contao-bundle-creator-bundle/skeleton ablegen.

![Templates updatesicher überschreiben](docs/custom-templates.png?raw=true "Templates updatesicher überschreiben")

## Codefixing mit easy-coding-standard
Auf Wunsch lässt sich "contao/easy-coding-standard" als Abhängigkeit installieren. Bei der Installation werden die Konfigurationsdateien im "vendor/my-custom-bundle/.ecs" abgelegt. Der Fixer kann nun so über das Terminal aufgerufen werden:

Unter Windows (Backslashes als directory separator verwenden):

```
# Default fixer
vendor\bin\ecs check vendor/vendorname/my-custom-bundle/src --fix --config vendor/vendorname/my-custom-bundle/.ecs/config/default.yaml

# Tests
vendor\bin\ecs check vendor/vendorname/my-custom-bundle/tests --fix --config vendor/vendorname/my-custom-bundle/.ecs/config/default.yaml

# Contao legacy code
vendor\bin\ecs check vendor/vendorname/my-custom-bundle/src/Resources/contao --fix --config vendor/vendorname/my-custom-bundle/.ecs/config/legacy.yaml

```
[easy-coding-standard](https://github.com/symplify/easy-coding-standard)

## App erweitern
Die Bundle-Dateien werden in dieser App über Maker dem neu zu erstellenden Bundle hinzugefügt.
Mit Subscribern können weitere Maker-Klassen hinzugefügt werden. Dazu muss lediglich eine Subscriberklasse angelegt und diese in src/Resources/config/subscriber.yml registriert werden.

## Last but not least
Der Anwender sollte wissen, was er tut ;-)

Im dümmsten Fall überschreibt man bereits bestehende Erweiterungen und beschädigt so die Installation.
