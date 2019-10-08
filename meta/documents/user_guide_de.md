
# User Guide für das Elastic Export Rakuten.de Plugin

<div class="container-toc"></div>

## 1 Bei Rakuten.de registrieren

Auf dem Marktplatz Rakuten bietest du deine Artikel zum Verkauf an. Weitere Informationen zu diesem Marktplatz findest du auf der Handbuchseite [Rakuten einrichten](https://knowledge.plentymarkets.com/maerkte/rakuten/rakuten-einrichten). Um das Plugin für Rakuten.de einzurichten, registriere dich zunächst als Händler.

## 2 Das Format RakutenDE-Plugin in plentymarkets einrichten

Mit der Installation dieses Plugins erhältst du das Exportformat **RakutenDE-Plugin**, mit dem du Daten über den elastischen Export zu Rakuten überträgst. Um dieses Format für den elastischen Export nutzen zu können, installiere zunächst das Plugin **Elastic Export** aus dem plentyMarketplace, wenn noch nicht geschehen. 

Sobald beide Plugins in deinem System installiert sind, kann das Exportformat **RakutenDE-Plugin** erstellt werden. Weitere Informationen findest du auf der Handbuchseite [Elastischer Export](https://knowledge.plentymarkets.com/daten/daten-exportieren/elastischer-export).

Neues Exportformat erstellen:

1. Öffne das Menü **Daten » Elastischer Export**.
2. Klicke auf **Neuer Export**.
3. Nimm die Einstellungen vor. Beachte dazu die Erläuterungen in Tabelle 1.
4. **Speichere** die Einstellungen.<br/>
→ Eine ID für das Exportformat **RakutenDE-Plugin** wird vergeben und das Exportformat erscheint in der Übersicht **Exporte**.

In der folgenden Tabelle findest du Hinweise zu den einzelnen Formateinstellungen und empfohlenen Artikelfiltern für das Format **RakutenDE-Plugin**.

| **Einstellung**                                     | **Erläuterung**|
| :---                                                | :--- |                                            
| **Einstellungen**                                   | |
| **Name**                                            | Name eingeben. Unter diesem Namen erscheint das Exportformat in der Übersicht im Tab **Exporte**. |
| **Typ**                                             | Typ **Artikel** aus der Dropdown-Liste wählen. |
| **Format**                                          | Das Format **RakutenDE-Plugin** wählen. |
| **Limit**                                           | Zahl eingeben. Wenn mehr als 9999 Datensätze an Rakuten übertragen werden sollen, wird die Ausgabedatei für 24 Stunden nicht noch einmal neu generiert, um Ressourcen zu sparen. Wenn mehr mehr als 9999 Datensätze benötigt werden, muss die Option **Cache-Datei generieren** aktiv sein. |
| **Cache-Datei generieren**                          | Häkchen setzen, wenn mehr als 9999 Datensätze an Rakuten übertragen werden sollen. Um eine optimale Perfomance des elastischen Exports zu gewährleisten, darf diese Option bei maximal 20 Exportformaten aktiv sein. |
| **Bereitstellung**                                  | Die Bereitstellung **URL** wählen. Mit dieser Option kann ein Token für die Authentifizierung generiert werden, damit ein externer Zugriff möglich ist. |
| **Dateiname**                                       | Der Dateiname muss auf **.csv** enden, damit Rakuten.de die Datei erfolgreich importieren kann. |
| **Token, URL**                                      | Wenn unter Bereitstellung die Option **URL** gewählt wurde, auf **Token generieren** klicken. Der Token wird dann automatisch eingetragen. Die URL wird automatisch eingetragen, wenn unter **Token** der Token generiert wurde. |
| **Artikelfilter**                                   | |
| **Artikelfilter hinzufügen**                        | Artikelfilter aus der Dropdown-Liste wählen und auf **Hinzufügen** klicken. Standardmäßig sind keine Filter voreingestellt. Es ist möglich, alle Artikelfilter aus der Dropdown-Liste nacheinander hinzuzufügen.<br/> **Varianten** = **Alle übertragen** oder **Nur Hauptvarianten übertragen** wählen.<br/> **Märkte** = **Rakuten.de** wählen.<br/> **Währung** = Währung wählen.<br/> **Kategorie** = Aktivieren, damit der Artikel mit Kategorieverknüpfung übertragen wird. Es werden nur Artikel, die dieser Kategorie zugehören, übertragen.<br/> **Bild** = Aktivieren, damit der Artikel mit Bild übertragen wird. Es werden nur Artikel mit Bildern übertragen.<br/> **Mandant** = Mandant wählen.<br/> **Markierung 1 - 2** = Markierung wählen.<br/> **Hersteller** = Einen, mehrere oder **ALLE** Hersteller wählen.<br/> **Aktiv** = Nur aktive Varianten werden übertragen. |
| **Formateinstellungen**                             | |
| **Produkt-URL**                                     | Diese Option ist für dieses Format nicht relevant. |
| **Mandant**                                         | Mandant wählen. Diese Einstellung wird für den URL-Aufbau verwendet. |
| **URL-Parameter**                                   | Diese Option ist für dieses Format nicht relevant. |
| **Auftragsherkunft**                                | Aus der Dropdown-Liste die Auftragsherkunft wählen, die beim Auftragsimport zugeordnet werden soll. Die Produkt-URL wird um die gewählte Auftragsherkunft erweitert, damit die Verkäufe später analysiert werden können. |
| **Marktplatzkonto**                                 | Marktplatzkonto aus der Dropdown-Liste wählen. |
| **Sprache**                                         | Sprache aus der Dropdown-Liste wählen. |
| **Artikelname**                                     | **Name 1**, **Name 2** oder **Name 3** wählen. Die Namen sind im Tab **Texte** eines Artikels gespeichert.<br/> Im Feld **Maximale Zeichenlänge (def. Text)** optional eine Zahl eingeben, wenn Rakuten eine Begrenzung der Länge des Artikelnamen beim Export vorgibt. |
| **Vorschautext**                                    | Wählen, ob und welcher Text als Vorschautext übertragen werden soll.<br/> Im Feld **Maximale Zeichenlänge (def. Text)** optional eine Zahl eingeben, wenn Rakuten eine Begrenzung der Länge des Vorschautextes beim Export vorgibt.<br/> Option **HTML-Tags entfernen** aktivieren, damit die HTML-Tags beim Export entfernt werden.<br/> Im Feld **Erlaubte HTML-Tags, kommagetrennt (def. Text)** optional die HTML-Tags eingeben, die beim Export erlaubt sind. Wenn mehrere Tags eingegeben werden, mit Komma trennen. |
| **Beschreibung**                                    | Wählen, welcher Text als Beschreibungstext übertragen werden soll.<br/> Im Feld **Maximale Zeichenlänge (def. Text)** optional eine Zahl eingeben, wenn Rakuten eine Begrenzung der Länge der Beschreibung beim Export vorgibt.<br/> Option **HTML-Tags entfernen** aktivieren, damit die HTML-Tags beim Export entfernt werden.<br/> Im Feld **Erlaubte HTML-Tags, kommagetrennt (def. Text)** optional die HTML-Tags eingeben, die beim Export erlaubt sind. Wenn mehrere Tags eingegeben werden, mit Komma trennen. |
| **Zielland**                                        | Zielland aus der Dropdown-Liste wählen. |
| **Barcode**                                         | ASIN, ISBN oder eine EAN aus der Dropdown-Liste wählen. Der gewählte Barcode muss mit der oben gewählten Auftragsherkunft verknüpft sein. Andernfalls wird der Barcode nicht exportiert. |
| **Bild**                                            | **Erstes Bild** wählen. |
| **Bildposition des Energieetiketts**                | Position des Energieetiketts eintragen. Alle Bilder, die als Energieetikette übertragen werden sollen, müssen diese Position haben. |
| **Bestandspuffer**                                  | Der Bestandspuffer für Varianten mit der Beschränkung auf den Netto-Warenbestand. |
| **Bestand für Varianten ohne Bestandsbeschränkung** | Der Bestand für Varianten ohne Bestandsbeschränkung. |
| **Bestand für Varianten ohne Bestandsführung**      | Der Bestand für Varianten ohne Bestandsführung. |
| **Währung live umrechnen**                          | Aktivieren, damit der Preis je nach eingestelltem Lieferland in die Währung des Lieferlandes umgerechnet wird. Der Preis muss für die entsprechende Währung freigegeben sein. |
| **Verkaufspreis**                                   | Brutto- oder Nettopreis aus der Dropdown-Liste wählen. |
| **Angebotspreis**                                   | Aktivieren, um den Angebotspreis zu übertragen. |
| **UVP**                                             | Aktivieren, um den UVP zu übertragen. |
| **Versandkosten**                                   | Diese Option ist für dieses Format nicht relevant. |
| **MwSt.-Hinweis**                                   | Diese Option ist für dieses Format nicht relevant. |
| **Artikelverfügbarkeit überschreiben**              | Diese Einstellung muss aktiviert sein, da Rakuten.de nur spezifische Werte akzeptiert, die hier eingetragen werden müssen. <br /> Weitere Informationen dazu im Abschnitt **3 Verfügbare Spalten der Exportdatei** bei **lieferzeit**. |

_Tab. 1: Einstellungen für das Datenformat **RakutenDE-Plugin**_
 
 ## 3 Verfügbare Spalten der Exportdatei
 
| **Spaltenbezeichnung**   | **Erläuterung** |
| :---                     | :--- |
| id                       | **Pflichtfeld** bei Variantenartikeln <br /> Die **Artikel-ID** des Artikels mit dem Präfix **#**. |
| variante_zu_id           | **Pflichtfeld** bei Variantenartikeln <br /> Die **Artikel-ID** des Artikels mit dem Präfix **#**. |
| artikelnummer            | **Pflichtfeld** <br />**Beschränkung**: max. 30 Zeichen <br /> Die SKU des Artikels. |
| produkt_bestellbar       | **Pflichtfeld** <br /> **1**, wenn für die Variante Bestand vorhanden oder diese nicht auf den Netto-Warenbestand beschränkt ist, ansonsten **0**. |
| produktname              | **Pflichtfeld** <br />**Beschränkung**: max. 100 Zeichen <br /> Entsprechend der Formateinstellung **Artikelname**. |
| hersteller               | **Beschränkung**: max. 30 Zeichen <br /> Der **Name des Herstellers** des Artikels. Der **Externe Name** unter **Einstellungen » Artikel » Hersteller** wird bevorzugt, wenn vorhanden. |
| beschreibung             | **Pflichtfeld** <br /> Entsprechend der Formateinstellung **Beschreibung**. |
| variante                 | **Pflichtfeld** bei Variantenartikeln <br /> Die **Namen der Attribute**, die mit den Varianten verknüpft sind. |
| variantenwert            | **Pflichtfeld** bei Variantenartikeln <br /> Die **Namen der Attributswerte**, die mit den Varianten verknüpft sind. |
| isbn_ean                 | **Beschränkung**: ISBN-10, ISBN-13, GTIN-13 <br /> Entsprechend der Formateinstellung **Barcode**. |
| lagerbestand             | **Beschränkung**: 0 bis 9999 <br />**Inhalt**: Der **Netto-Warenbestand der Variante**. Bei Artikeln, die nicht auf den Netto-Warenbestand beschränkt sind, wird **999** übertragen. |
| preis                    | **Pflichtfeld** <br />**Beschränkung**: max. 5 Vorkommastellen <br /> Der **Verkaufspreis** der Variante. Wenn der **UVP** in den Formateinstellungen aktiviert wurde und höher ist als der Verkaufspreis, wird dieser hier eingetragen. |
| grundpreis_inhalt        | **Beschränkung**: max. 3 Vor- und Nachkommastellen <br /> Der **Inhalt** der Variante. |
| grundpreis_einheit       | **Beschränkung**: ml, l, kg, g, m, m², m³<br /> Die **Einheit** des Inhalts der Variante. |
| reduzierter_preis        | **Beschränkung**: max. 5 Vorkommastellen <br /> Wenn die Formateinstellung **UVP** und/oder **Angebotspreis** aktiviert wurde, steht hier der Verkaufspreis oder Angebotspreis. |
| bezug_reduzierter_preis  | **UVP** oder **VK**. Wird in Abhängigkeit der Spalte **preis** gefüllt. |
| mwst_klasse              | **Pflichtfeld** <br />**Beschränkung**: 1,2,3,4 (1 = 19%, 2 = 7%, 3 = 0%, 4 = 10,7%) <br /> Wird entsprechend des Mehrwertsteuersatzes übersetzt. |
| bestandsverwaltung_aktiv | **1**, wenn die Variante auf den Netto-Warenbestand beschränkt ist, ansonsten **0**. |
| bild1                    | **Pflichtfeld**: <br /> Erstes Bild der Variante. |
| bild2-10                 | Entsprechende Bilder der Variante. |
| kategorien               | **Pflichtfeld** <br /> **Kategoriepfad der Standard-Kategorie** für den in den Formateinstellungen definierten **Mandanten**. |
| lieferzeit               | **Pflichtfeld** <br />**Beschränkung**: 0,3,5,7,10,15,20,30,40,50,60 <br /> Übersetzung gemäß der Formateinstellung **Artikelverfügbarkeit überschreiben**. |
| tradoria_kategorie       | **Kategorieverknüpfung der Standard-Kategorie** für den in den Formateinstellungen definierten **Mandanten**. |
| sichtbar                 | Immer **1**. |
| free_var_1-20            | Steht für das **Freitextfeld**. |
| MPN                      | Das **Model** der Variante. |
| technical_data           | **Technische Daten** des Artikels. |
| energie_klassen_gruppe   | **Beschränkung**: Festgelegte Werte als Merkmalwert vorgegeben. <br /> Wert wird über ein Merkmal vom Typ **Kein** verknüpft. Wert muss unter **Einrichtung » Artikel » Merkmale » Merkmal öffnen** über die Option **Rakuten.de-Merkmal** festgelegt werden. |
| energie_klasse           | **Beschränkung**: Festgelegte Werte als Merkmalwert vorgegeben. <br /> Wert wird über ein Merkmal vom Typ **Kein** verknüpft. Wert muss unter **Einrichtung » Artikel » Merkmale » Merkmal öffnen** über die Option **Rakuten.de-Merkmal** festgelegt werden. |
| energie_klasse_bis       | **Beschränkung**: Festgelegte Werte als Merkmalwert vorgegeben. <br /> Wert wird über ein Merkmal vom Typ **Kein** verknüpft. Wert muss unter **Einrichtung » Artikel » Merkmale » Merkmal öffnen** über die Option **Rakuten.de-Merkmal** festgelegt werden. |
| energie_klassen_bild     | Das Bild mit der Position gemäß der Formateinstellung **Bildposition der Energieetikette** wird als Energieetikette übertragen, falls vorhanden. |

## 4 Lizenz

Das gesamte Projekt unterliegt der GNU AFFERO GENERAL PUBLIC LICENSE – weitere Informationen finden Sie in der [LICENSE.md](https://github.com/plentymarkets/plugin-elastic-export-rakuten-de/blob/master/LICENSE.md).

