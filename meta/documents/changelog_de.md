# Release Notes für Elastic Export Rakuten.de

## v1.2.1 (2017-07-11)

### Behoben
- Es werden jetzt zusätzlich zu den normalen SKUs auch ParentSKUs erstellt um somit das doppelte Anlegen der Items zu vermeiden.

## v1.2.0 (2017-06-30)

### Geändert
- Das Bestandsupdate wurde hinzugefügt.

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