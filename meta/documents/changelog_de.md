# Release Notes für Elastic Export Rakuten.de

## v1.3.30 (2019-11-28)

### Fixed
- Die Mehrwertsteuerklasse wurde immer mit der ID 1 exportiert, was 19% auf Rakuten.de entspricht.

## v1.3.29 (2019-11-27)

### Behoben
- Die Spalte "energie_klassen_gruppe" wird bei Varianten-Artikeln nur noch in den Eltern-Zeilen und nicht mehr in den Kind-Zeilen übertragen.

## v1.3.28 (2019-11-15)

### Behoben
- Der letzte Artikel in jedem verarbeiteten ES-Stapel wird nicht mehr entfernt oder ohne Preis und Bestand exportiert.

## v1.3.27 (2019-10-31)

### Geändert
- Der Preis- und Bestandabgleich hat ein Performance-Upgrade erhalten.
**Achtung!** Diese Version benötigt das Plugin Elastischer Export Version 1.6.3 oder höher.

## v1.3.26 (2019-10-08)

### Geändert
- Der User Guide wurde aktualisiert (Form der Anrede geändert, fehlerhafte Links korrigiert).

## v1.3.25 (2019-09-26)

### Behoben
- Es wurden mehrere curl-Sessions geöffnet, statt bereits geöffnete Sessions wiederzuverwenden. Dies konnte zu systemübergreifenden Problemen führen, wenn dadurch zu viele Sessions gleichzeitig geöffnet wurden.

## v1.3.24 (2019-08-15)

### Behoben
- Die interne Markierung an der Variante zur Bestandsaktualisierung wurde nicht gesetzt.

## v1.3.23 (2019-08-12)

### Behoben
- In manchen Fällen wurden Bestände nicht aktualisiert.

## v1.3.22 (2019-06-05)

### Geändert
- Um unnötigen Resourcenverbrauch zu vermeiden werden Caches für Attribute, Kategorien und Freitextfelder regelmäßig bereinigt.

## v1.3.21 (2019-01-23)

### Geändert
- Ein fehlerhafter Link im User Guide wurde korrigiert.

## v1.3.20 (2019-01-16)

### Behoben
- Übermittlung von überflüssigen Daten im Bestandsabgleich entfernt.

## v1.3.19 (2019-01-11)

### Geändert
- Stabilitätsverbesserungen für den Bestands- und Preisabgleich.

## v1.3.18 (2018-11-08)

### Behoben
- Beim Bestandsupdate wurde der Nettowarenbestand der einzelnen Lager zu dem virtuellen Lagerbestand hinzugefügt.

## v1.3.17 (2018-10-31)

### Geändert
- Stabilitätsverbesserungen für den Bestands- und Preisabgleich.

## v1.3.16 (2018-10-18)

### Geändert
- Stabilitätsverbesserungen für den Bestands- und Preisabgleich.

## v1.3.15 (2018-10-02)

### Geändert
- Performance-Verbesserungen für Bestands- und Preisabgleich.

## v1.3.14 (2018-09-28)

### Geändert
- Performance-Verbesserungen für Bestands- und Preisabgleich.

## v1.3.13 (2018-09-05)

### Behoben
- Bei Artikeln ohne Varianten wird die Parent-SKU richtig erstellt.

## v1.3.12 (2018-09-03)

### Behoben
- Das Preisupdate berücksichtigt und überträgt den reduzierten Preis.

## v1.3.11 (2018-07-24)

### Geändert
- Dem User Guide wurden weitere Informationen zur Einrichtung des Plugins hinzugefügt.

## v1.3.10 (2018-05-08)

### Geändert
- Die Plugin-Config ist multilingual.

## v1.3.9 (2018-04-30)

### Geändert
- Laravel 5.5 Update.

## v1.3.8 (2018-04-05)

### Hinzugefügt
- Der PriceHelper berücksichtigt die Einstellung **Währung live umrechnen**.

### Geändert
- Die Klasse FiltrationService übernimmt die Filtrierung der Varianten.
- Die SKU-Logik verwendet nur Daten aus der Datenbank.
- Vorschaubilder aktualisiert.

## v1.3.7 (2018-03-06)

### Behoben
- Die Tabellen des User Guides wurden angepasst.

## v1.3.6 (2018-02-21)

### Geändert
- Plugin-Kurzbeschreibung wurde angepasst.

## v1.3.5 (2018-02-13)

### Hinzugefügt
- Der PriceHelper berücksichtigt nun die Einstellung "Verkaufspreis".

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
