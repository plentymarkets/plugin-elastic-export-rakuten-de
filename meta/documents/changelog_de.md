# Release Notes für Elastic Export Rakuten.de

## 1.3.4 (2018-02-09)

### Behoben
- Ein Fehler wurde behoben, bei dem der Prefix und Suffix für die Parent-SKU bei Einzelartikel nicht gesetzt wurde.

## 1.3.3 (2018-01-19)

### Behoben
- Ein Fehler wurde behoben, bei dem Attributswerte mit dem Trennzeichen **"|"** im Namen Fehler bei Rakuten verursachten.

## v1.3.2 (2017-01-11)

### Geändert
- Es werden nun alle Kategoriepfade einer Variante übertragen, wenn sie für den Mandanten freigegeben sind und die dementsprechenden Spracheinstellungen haben.

## v1.3.1 (2017-01-09)

### Geändert
- Inaktive Varianten werden jetzt nur noch einmal an Rakuten im Bestandsabgleich übertragen.

## v1.3.0 (2017-12-28)

### Hinzugefügt
- Der StockHelper berücksichtigt die neuen Felder "Bestandspuffer", "Bestand für Varianten ohne Bestandsbeschränkung" und "Bestand für Varianten ohne Bestandsführung".

## 1.2.15 (2017-12-28)

### Geändert
- Für den Bestands- und Preisabgleich wurde die delta Zeit auf 2 Stunden verkürzt.

## 1.2.14 (2017-12-05)

### Geändert
- Die Logs für den Bestands- und Preisabgleich und den Export werden nun in 100er Bündel gespeichert und in das Log geschrieben.

## 1.2.13 (2017-12-05)

### Behoben
- Ein Fehler wurde behoben, welcher dazu führte, dass der Bestands- und Preisabgleich nicht funktionierte.
- Ein Fehler wurde behoben, welcher dazu führte, dass Hauptvarianten, trotz vorhandenen Artikelvarianten, als Einzelartikel übertragen wurden.

## 1.2.12 (2017-11-24)

### Behoben
- Ein Fehler wurde behoben, welcher dazu führte, dass der Artikel-Update nicht funktionierte, wenn es mehrere Varianten gab.

## 1.2.11 (2017-11-13)

### Geändert
- URLs in der Plugin-Beschreibung wurden aktualisiert.

### Behoben
- Ein Fehler wurde behoben, welcher dazu führte, dass Produkt- oder Variantenaktualisierungen während des Bestands- oder Preisabgleichs abgelehnt wurden.

## 1.2.10 (2017-11-03)

### Behoben
- Ein Fehler wurde behoben, welcher dazu führte, dass zweimal die gleiche Elternzeile erstellt wurde.

## 1.2.9 (2017-10-27)

### Behoben
- Ein Fehler wurde behoben, welcher dazu führte, dass der Kontakt zu Elasticsearch unterbrochen wurde.

## v1.2.8 (2017-10-20)

### Geändert
- Der Export bekommt nun die Ergebnisfelder vom ResultFieldDataProvider aus dem ElasticExport-Plugin.

### Behoben
- Ein Fehler wurde behoben, welche zu Problemen bei dem Preis- und Bestandsabgleich geführt hat.

## v1.2.7 (2017-09-27)

### Hinzugefügt
- Die Felder **available** und **stock_policy** wurden dem **Bestandsabgleich** hinzugefügt.

## v1.2.6 (2017-09-18)

### Geändert
- Der **Bestandsabgleich** wurde optimiert.

## v1.2.5 (2017-09-11)

### Behoben
- Es wurde ein Fehler behoben, der dazu geführt hat, dass die **Bestandsverwaltung** in manchen Fällen nicht aktiv war.

## v1.2.4 (2017-07-29)

### Hinzugefügt
- Es ist nun möglich Energieetikette zu übergeben. Es wird das Bild mit der Position gemäß der Formateinstellung **Bildposition des Energieetikettes** als Energieetikette übergeben.

### Geändert
- Der User Guide wurde erweitert.

## v1.2.3 (2017-07-20)

### Hinzugefügt
- Für Parent-SKUs können unter den Plugin-Einstellungen im Tab "Parent-SKU" Pre- und Suffixe angelegt werde. Diese werden dann automatisch gesetzt, sofern nicht schon eine Parent-SKU existiert.

### Behoben
- Es wurde ein Fehler behoben der dazu geführt hat, dass die Freitextfelder nicht mehr ausgelesen werden konnten.

### Geändert
- Der Bestands- und Preisabgleich wird nur noch durchgeführt wenn es Änderungen innerhalb der letzten 2 Tage gab.

## v1.2.2 (2017-07-13)

### Behoben
- Es wurden Fehler behoben die dazu geführt haben, dass die Artikel- und Artikelvariantenzuordnung fehlerhaft war.

## v1.2.1 (2017-07-11)

### Behoben
- Es werden jetzt zusätzlich zu den normalen SKUs auch Parent-SKUs erstellt, um das Anlegen doppelter Artikel zu vermeiden.

## v1.2.0 (2017-06-30)

### Hinzugefügt
- Wir haben einen Prozess zur automatischen Bestandsübermittlung integriert.

## v1.1.11 (2017-06-02)

### Behoben
- Durch einen Fehler wurden in der Preisermittlungen Mandanten nicht korrekt berücksichtigt.

## v1.1.10 (2017-05-18)

### Behoben
- Es wurde ein Fehler behoben, der dazu geführt hat, dass bei dem Barcode die Markplatzfreigabe ignoriert wurde.

## v1.1.9 (2017-05-15)

### Behoben
- Es wurde ein Fehler behoben, der dazu geführt hat, dass Artikelnummern doppelt übertragen wurden.

## v1.1.8 (2017-05-12)

### Behoben
- Es wurde ein Fehler behoben, der dazu geführt hat, dass die Varianten nicht richtig sortiert wurden.

## v1.1.7 (2017-05-10)

### Behoben
- Bildpositionen werden nun korrekt interpretiert.

## v1.1.6 (2017-05-09)

### Behoben
- Die Beschreibung wird nun in der konfigurierten Sprache exportiert.

## v1.1.5 (2017-05-05)

### Behoben
- Es wurde ein Fehler behoben, der dazu geführt hat, dass das Exportformat teilweise nicht geladen werden konnte.

## v1.1.4 (2017-05-04)

### Behoben
- Es wurde ein Fehler behoben, der dazu geführt hat, dass SKUs teilweise nicht abgespeichert werden konnten..

## v1.1.3 (2017-05-02)

### Behoben
- Es wurde ein Fehler behoben, der dazu geführt hat, dass die SKU-Ermittlung immer auf den Account 0 bezogen wurde.

### Geändert
- Die Bestandsfilterlogik wurde in das Elastic Export-Plugin ausgelagert.

## v1.1.2 (2017-04-28)

### Behoben
- Es wurde ein Fehler behoben, der dazu geführt hat, dass wenn keine Preise an der Variation hinterlegt wurden
der Preis 0.00 übertragen wurde.

## v1.1.1 (2017-04-18)

### Behoben
- Es wurde ein Fehler behoben, der dazu geführt hat, dass das Plugin nicht mehr gebaut werden konnte.

## v1.1.0 (2017-04-06)

### Behoben
- Es wurde ein Fehler behoben, der dazu geführt hat, dass der Artikelfilter "Bestand" nicht richtig funktioniert hat.

## v1.0.9 (2017-04-04)

### Geändert
- Der aktuelle Variantenbestand wird nun anhand der Vertriebslager ermittelt.
- Die Logik wurde angepasst um die Stabilität des Exports zu verbessern.

## v1.0.8 (2017-03-30)

### Hinzugefügt
- Es wurde ein neuer Mutator hinzugefügt, welcher verhindern soll das auf nicht existente Arraykeys zugegeriffen werden.

## v1.0.7 (2017-03-29)

### Geändert
- Es wurde die Performance verbessert.

## v1.0.6 (2017-03-27)

### Hinzugefügt
- Es wurden Validatoren hinzugefügt.

## v1.0.5 (2017-03-27)

### Hinzugefügt
- Es wurden Logs hinzugefügt.

### Geändert
- Die Artikelsuche von Elastic Search findet nun im Generator statt.

## v1.0.4 (2017-03-23)

### Geändert
- Der ItemDataLayer wurde entfernt um die Performance zu steigern.

## v1.0.3 (2017-03-22)

### Behoben
- Es wird nun ein anderes Feld genutzt um die Bild-URLs auszulesen für Plugins die elastic search benutzen.

## v1.0.2 (2017-03-13)

### Hinzugefügt
- Marketplace Namen hinzugefügt.

### Geändert
- Plugin Icons angepasst.

## v1.0.1 (2017-03-03)
- Die ResultFields wurden angepasst, sodass der imageMutator nicht mehr greift falls "ALLE" als Referrer ausgewählt wurde

## v1.0.0 (2017-02-20)

### Hinzugefügt
- Initiale Plugin-Dateien hinzugefügt
