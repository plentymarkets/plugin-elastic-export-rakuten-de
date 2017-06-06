
# User Guide für das Elastic Export Rakuten.de Plugin

<div class="container-toc"></div>

##1 Bei Rakuten.de registrieren

Auf dem Marktplatz Rakuten bieten Sie Ihre Artikel zum Verkauf an. Weitere Informationen zu diesem Marktplatz finden Sie auf der Handbuchseite [Rakuten einrichten](https://www.plentymarkets.eu/handbuch/multi-channel/rakuten/). Um das Plugin für Rakuten.de einzurichten, registrieren Sie sich zunächst als Händler.

##2 Rakuten.de-Plugin und vorausgesetzte Plugins in plentymarkets installieren

Um dieses Format nutzen zu können, benötigen Sie das Plugin Elastic Export.

Kaufen Sie das Plugin Rakuten.de und alle vorausgesetzten Plugins im plentyMarketplace.
Folgen Sie zur Installation und zum Updaten der Plugins Elastic Export und Rakuten.de der Anleitung unter "Erste Schritte und Anforderungen".

##3 Das Elastic-Export-Format RakutenDE-Plugin in plentymarkets einrichten.

###3.1 Allgemeine Hinweise

Auf der Handbuchseite [Daten exportieren](https://www.plentymarkets.eu/handbuch/datenaustausch/daten-exportieren/#4) sind die einzelnen Bereiche der Einstellungen der Formate beschrieben. Die grundsätzliche Erläuterung zu den entsprechenden Einstellungen finden Sie dort.

###3.2 Spezifische Hinweise

####3.2.1 Einstellungen

|Einstellung|Besonderheit|
|:---|:---|
|Format|Wählen Sie das Format **RakutenDE-Plugin**.|
|Bereitstellung|Wählen Sie das Format **URL**.|
|Dateiname|Der Dateiname muss auf **.csv** enden, damit Rakuten.de die Datei erfolgreich importieren kann.|

####3.2.2 Artikelfilter

Die Artikelfilter können grundsätzlich frei gewählt werden. Folgende Artikelfiltereinstellungen werden aber mindestens empfohlen:

|Artikelfilter|Empfohlene Einstellung|
|:---|:---|
|Aktiv|Aktiv|
|Märkte|Wählen Sie die Auftragsherkunft **Rakuten.de**.|

####3.2.3 Formateinstellungen

#####3.2.3.1 inaktive Formateinstellungen
Da die angezeigten Formateinstellungen allgemein für alle Exporte angezeigt werden, gibt es einzelne Formateinstellungen, die für bestimmte Formate keinen Einfluss auf den Export haben. Diese können entsprechend bei der Konfiguration vernachlässigt werden.

1. Produkt-URL
2. URL-Parameter
3. Versandkosten
4. MwSt.-Hinweis

#####3.2.3.2 Besonderheiten Formateinstellungen

|Formateinstellung|Besonderheit|
|:---|:---|
|Bild|Wählen Sie die Option **Erstes Bild**. Es werden bis zu 10 Bilder pro Artikel exportiert.|
|Artikelverfügbarkeit überschreiben|Diese Option muss aktiviert sein, da Rakuten.de nur spezifische Werte akzeptiert, die hier eingetragen werden müssen. Weitere Informationen dazu finden Sie im Abschnitt **4 Übersicht der verfügbaren Spalten** bei der Spalte **lieferzeit**|

##4 Übersicht der verfügbaren Spalten

| Spaltenname               | Pflichtfeld   | Zeile                                     | Beschränkung                                  | Inhalt |
|:---                       |:---           |:---                                       |:---                                           |:---|
| id                        | Ja            | Elternartikel                             |                                               | Die **Artikel-ID** des Artikels mit dem Präfix **#**. |
| variante_zu_id            | Ja            | Varianten                                 |                                               | Die **Artikel-ID** des Artikels mit dem Präfix **#**. |
| artikelnummer             | Ja            | Enzelartikel, Varianten                   | max. 30 Zeichen                               | Die **SKU** des Artikels. Einzusehen unter Artikel » Artikel bearbeiten » "Artikel" » "Variante" » Verfügbarkeit*| 
| produkt_bestellbar        | Ja            | Einzelartikel, Varianten                  |                                               | **1**, wenn für die Variante Bestand vorhanden oder diese nicht auf den Nettowarebestand beschränkt ist, ansonsten **0**. |
| produktname               | Ja            | Einzelartikel, Elternartikel              | max. 100 Zeichen                              | Entsprechend der Formateinstellung **Artikelname**. |
| hersteller                | Nein          | Einzelartikel, Elternartikel              | max. 30 Zeichen                               | Der **Name des Herstellers** des Artikels. Der **Externe Name** unter *Einstellungen » Artikel » Hersteller* wird bevorzugt, wenn vorhanden. |
| beschreibung              | Ja            | Einzelartikel, Elternartikel              |                                               | Entsprechend der Formateinstellung **Beschreibung**. |
| variante                  | Ja            | Elternartikel                             |                                               | Die **Namen der Attribute** die mit den Varianten verknüpft sind. |
| variantenwert             | Ja            | Varianten                                 |                                               | Die **Namen der Attributswerte** die mit den Varianten verknüpft sind. |
| isbn_ean                  | Nein          | Einzelartikel, Varianten                  | ISBN-10, ISBN-13, GTIN-13                     | Entsprechend der Formateinstellung **Barcode**. |
| lagerbestand              | Nein          | Einzelartikel, Varianten                  | 0 bis 9999                                    | Der **Nettowarenbestand der Variante**. Bei Artikeln, die nicht auf den Nettowarenbestand beschränkt sind, wird **999** übertragen. |
| preis                     | Ja            | Einzelartikel, Varianten                  | max. 5 Vorkommastellen                        | Der **Verkaufspreis** der Variante. Wenn der **UVP** in den Formateinstellungen aktiviert wurde und höher ist als der Verkaufspreis, wird dieser hier eingetragen. |
| grundpreis_inhalt         | Nein          | Einzelartikel, Varianten                  | max. 3 Vorkomma- und Nachkommastellen         | Der **Inhalt** der Variante. |
| grundpreis_einheit        | Nein          | Einzelartikel, Varianten                  | Mögliche Werte: ml, l, kg, g, m, m², m³       | Die **Einheit** des Inhalts der Variante. |
| reduzierter_preis         | Nein          | Einzelartikel, Varianten                  | max. 5 Vorkommastellen                        | Wenn die Formateinstellung **UVP** und/oder **Angebotspreis** aktiviert wurde, steht hier der Verkaufspreis oder Angebotspreis, je nachdem welcher niedriger ist und ob ein UVP vorhanden ist. |
| bezug_reduzierter_preis   | Nein          | Einzelartikel, Varianten                  |                                               | **UVP** oder **VK**. Wird in Abhängigkeit der Spalte **preis** gefüllt. |
| mwst_klasse               | Ja            | Einzelartikel, Elternartikel              | 1 = 19% <br>2 = 7%<br>3 = 0%<br>4 = 10,7%     | Wird entsprechend des Mehrwertseutersatzes übersetzt. |
| bestandsverwaltung_aktiv  | Nein          | Einzelartikel, Elternartikel              |                                               | **1**, wenn die Variante auf den Nettowarenbestand beschränkt ist, ansonsten **0** |
| bild1                     | Ja            | Einzelartikel, Elternartikel              |                                               | Erstes Bild des Artikels |
| bild2-10                  | Nein          | Einzelartikel, Elternartikel              |                                               | Entsprechende Bilder des Artikels |
| kategorien                | Ja            | Einzelartikel, Elternartikel              |                                               | **Kategoriepfad der Standard-Kategorie** für den, in den Formateinstellungen definierten **Mandanten** |
| lieferzeit                | Ja            | Einzelartikel, Variante                   | Erlaubte Werte: 0,3,5,7,10,15,20,30,40,50,60  | Übersetzung gemäß der Formateinstellung **Artikelverfügbarkeit überschreiben** |
| tradoria_kategorie        | Nein          | Einzelartikel, Elternartikel              |                                               | **Kategorieverknüpfung der Standard-Kategorie** für den, in den Formateinstellungen definierten **Mandanten** |
| sichtbar                  | Ja            | Einzelartikel, Elternartikel              |                                               | Immer **1** |
| free_var_1-20             | Nein          | Einzelartikel, Elternartikel, Varianten   |                                               | Steht für das jeweilige **Freitextfeld**. Diese Felder können bei Rakuten gemapped werden, um für unterschiedliche Dinge genutzt zu werden. Langfristig werden diese durch Merkmale abgelöst werden. |
| MPN                       | Nein          | Einzelartikel, Varianten                  |                                               | Das **Model** der Variante. |
| technical_data            | Nein          | Einzelartikel, Elternartikel              |                                               | **Technische Daten** des Artikels. |
| energie_klassen_gruppe    | Nein          | Einzelartikel, Elternartikel              | Festgelegte Werte als Merkmalwert vorgegeben. | Wert wird über ein Merkmal vom Typ **Kein** verknüpft. Wert muss unter Einstellungen » Artikel » Merkmale » "Merkmal" über die Option **Rakuten.de-Merkmal** festgelegt werden.|
| energie_klasse            | Nein          | Einzelartikel, Variante                   | Festgelegte Werte als Merkmalwert vorgegeben. | Wert wird über ein Merkmal vom Typ **Kein** verknüpft. Wert muss unter Einstellungen » Artikel » Merkmale » "Merkmal" über die Option **Rakuten.de-Merkmal** festgelegt werden.|
| energie_klasse_bis        | Nein          | Einzelartikel, Variante                   | Festgelegte Werte als Merkmalwert vorgegeben. | Wert wird über ein Merkmal vom Typ **Kein** verknüpft. Wert muss unter Einstellungen » Artikel » Merkmale » "Merkmal" über die Option **Rakuten.de-Merkmal** festgelegt werden.|
| energie_klassen_bild      | Nein          | Einzelartikel, Variante                   |                                               | Wert wird aktuell nicht befüllt.|

##5 Lizenz

Das gesamte Projekt unterliegt der GNU AFFERO GENERAL PUBLIC LICENSE – weitere Informationen finden Sie in der [LICENSE.md](https://github.com/plentymarkets/plugin-elastic-export-rakuten-de/blob/master/LICENSE.md).
