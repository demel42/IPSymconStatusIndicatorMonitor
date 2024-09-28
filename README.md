[![IPS-Version](https://img.shields.io/badge/Symcon_Version-6.0+-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
![Code](https://img.shields.io/badge/Code-PHP-blue.svg)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)

## Dokumentation

**Inhaltsverzeichnis**

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Installation](#3-installation)
4. [Funktionsreferenz](#4-funktionsreferenz)
5. [Konfiguration](#5-konfiguration)
6. [Anhang](#6-anhang)
7. [Versions-Historie](#7-versions-historie)

## 1. Funktionsumfang

## 2. Voraussetzungen

- IP-Symcon ab Version 6.0

## 3. Installation

### a. Installation des Moduls

Im [Module Store](https://www.symcon.de/service/dokumentation/komponenten/verwaltungskonsole/module-store/) ist das Modul unter dem Suchbegriff *StatusIndicatorMonitor* zu finden.<br>
Alternativ kann das Modul über [Module Control](https://www.symcon.de/service/dokumentation/modulreferenz/module-control/) unter Angabe der URL `https://github.com/demel42/IPSymconStatusIndicatorMonitor` installiert werden.

### b. Einrichtung in IPS

## 4. Funktionsreferenz

## 5. Konfiguration

### StatusIndicatorMonitor Device

#### Properties

| Eigenschaft               | Typ      | Standardwert | Beschreibung |
| :------------------------ | :------  | :----------- | :----------- |
|                           |          |              | |

#### Aktionen

| Bezeichnung                | Beschreibung |
| :------------------------- | :----------- |

### Variablenprofile

Es werden folgende Variablenprofile angelegt:
* Boolean<br>
* Integer<br>
* Float<br>
* String<br>

## 6. Anhang

### GUIDs
- Modul: `{C359DCFA-C34F-80BD-1C50-67C3A55A1257}`
- Instanzen:
  - StatusIndicatorMonitor: `{A54DAEA0-DFE7-D8DE-7BDD-803381C23EDF}`
- Nachrichten:

### Quellen

## 7. Versions-Historie

- 1.7 @ 28.09.2024 08:59
  - Verbesserung: mehr Debug
  - update submodule CommonStubs

- 1.6 @ 06.02.2024 09:46
  - Verbesserung: Angleichung interner Bibliotheken anlässlich IPS 7
  - update submodule CommonStubs

- 1.5 @ 03.11.2023 11:06
  - Neu: Ermittlung von Speicherbedarf und Laufzeit (aktuell und für 31 Tage) und Anzeige im Panel "Information"
  - update submodule CommonStubs

- 1.4 @ 06.07.2023 09:41
  - Vorbereitung auf IPS 7 / PHP 8.2
  - update submodule CommonStubs
    - Absicherung bei Zugriff auf Objekte und Inhalte

- 1.3 @ 14.05.2023 14:33
  - "Instanz ist inaktiv" beachten

- 1.2 @ 31.03.2023 15:14
  - mehr Debug für den Ergebnisempfang

- 1.1 @ 15.03.2023 10:17
  - Fix: Fehler bei der Datenverarbeitung vor dem ersten Datenempfang

- 1.0 @ 14.03.2023 11:09
  - Initiale Version
