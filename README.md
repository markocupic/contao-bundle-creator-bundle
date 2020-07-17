![Alt text](src/Resources/public/logo.png?raw=true "Marko Cupic")


# Contao Bundle Creator (Boilerplate für eigene Erweiterungen)

Das Modul ist für Entwickler gedacht, und generiert ein Grundgerüst (Boilerplate) für ein Contao Bundle.


## Via Contao Backend das Bundle konfigurieren

![Alt text](src/Resources/public/backend.png?raw=true "Backend")


## Verzeichnisstruktur
Folgende Verzeichnisstruktur wird im vendor Vezeichnis abgelegt.

![Alt text](src/Resources/public/file-tree.png?raw=true "Verzeichnisstruktur")


## Inbetriebsetzung des Bundles
Das Bundle, nachdem alle Eingaben im Backend gemacht wurden, ganz einfach per Knopfdruck generieren lassen. Die Extension sollte nun im Verzeichnis "vendor" erstellt worden sein und kann auch als ZIP-File heruntergeladen werden.

### Variante A 
In der composer.json folgende 2 Einträge machen:
```
"repositories": [
    {
      "type": "path",
      "url": "/home/myhosting/public_html/dirtyharryproductions.com/vendor/dirtyharrycoding/hello-world-bundle"
    }
  ],
```
In der composer.json den **absoluten Pfad** zum Bundle im vendor-Verzeichnis angeben. Dieser Schritt kann, wenn so eingestellt, von der Erweiterung auch automatisch erledigt werden.
```
  "require": {
    ....
    ....
    "dirtyharrycoding/hello-world-bundle": "dev-master"
  },
```
Im require-Teil das neu erstellte Bundle registrieren. Dieser Schritt kann, wenn so eingestellt, von der Erweiterung auch automatisch erledigt werden.

Danach via Contao Manager ein vollständige Update durchführen und das Installtool aufrufen. Fertig!

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
    "dirtyharrycoding/hello-world-bundle": "dev-master"
  },
```
Im require-Teil das neu erstellte Bundle registrieren. 

Danach via Contao Manager ein vollständige Update durchführen und das Installtool aufrufen. Fertig!

Bei Variante B kann es sein, dass github.com die Verbindungsanfrage ablehnt. Die Erstellung eines **Oauth-Access-Tokens** kann hier Abhilfe schaffen.
Das Access Token muss dann in der **config section** der composer.json im Root eingesetzt werden. [Github Oauth-Access-Token generieren](https://docs.github.com/en/github/authenticating-to-github/creating-a-personal-access-token)
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
* Falls die root composer.json durch die Anwendung manipuliert wird, speichert das Skript ein Backup der composer.json in system/tmp
* Bei der Erstellung des Bundles wird im Verzeichnis system/tmp zusätzlich ein zip-package, das per Knopfdruck herutnergeladen werden kann, erstellt.

## Last but not least
Der Anwender sollte wissen, was er tut ;-)

Im dümmsten Fall überschreibt man bereits bestehende Erweiterungen und beschädigt so die Installation.
