
# User Guide für das Elastic Export Rakuten.de Plugin

<div class="container-toc"></div>

## 1 Bei Rakuten.de registrieren

Auf dem Marktplatz Rakuten bieten Sie Ihre Artikel zum Verkauf an. Weitere Informationen zu diesem Marktplatz finden Sie auf der Handbuchseite [Rakuten einrichten](https://www.plentymarkets.eu/handbuch/multi-channel/rakuten/). Um das Plugin für Rakuten.de einzurichten, registrieren Sie sich zunächst als Händler.

## 2 Das Format RakutenDE-Plugin in plentymarkets einrichten

Um dieses Format nutzen zu können, benötigen Sie das Plugin Elastic Export.

Auf der Handbuchseite [Daten exportieren](https://www.plentymarkets.eu/handbuch/datenaustausch/daten-exportieren/#4) werden allgemein die einzelnen Formateinstellungen beschrieben.

In der folgenden Tabelle finden Sie spezifische Hinweise zu den Einstellungen, Formateinstellungen und empfohlenen Artikelfiltern für das Format **RakutenDE-Plugin**. 

<table>
    <tr>
        <th>
            Einstellung
        </th>
        <th>
            Erläuterung
        </th>
    </tr>
    <tr>
        <th colspan="2">
            Einstellungen
        </th>
    </tr>
    <tr>
        <td>
            Format
        </td>
        <td>
            Das Format <b>RakutenDE-Plugin</b> wählen.
        </td>        
    </tr>
    <tr>
        <td>
            Bereitstellung
        </td>
        <td>
            Die Bereitstellung <b>URL</b> wählen.
        </td>        
    </tr>
    <tr>
        <td>
            Dateiname
        </td>
        <td>
            Der Dateiname muss auf <b>.csv</b> enden, damit Rakuten.de die Datei erfolgreich importieren kann.
        </td>        
    </tr>
    <tr>
        <th colspan="2">
            Artikelfilter
        </th>
    </tr>
    <tr>
        <td>
            Aktiv
        </td>
        <td>
            <b>Aktiv</b> auswählen.
        </td>        
    </tr>
    <tr>
        <td>
            Märkte
        </td>
        <td>
            <b>Rakuten.de</b> auswählen.
        </td>        
    </tr>
    <tr>
        <th colspan="2">
            Formateinstellungen
        </th>
    </tr>
    <tr>
        <td>
            Produkt-URL
        </td>
        <td>
            Diese Option ist für dieses Format nicht relevant.
        </td>        
    </tr>
    <tr>
        <td>
            URL-Parameter
        </td>
        <td>
            Diese Option ist für dieses Format nicht relevant.
        </td>        
    </tr>
    <tr>
        <td>
            Bild
        </td>
        <td>
            <b>Erstes Bild</b> auswählen.
        </td>        
    </tr>
    <tr>
        <td>
            Versandkosten
        </td>
        <td>
            Diese Option ist für dieses Format nicht relevant.
        </td>        
    </tr>
    <tr>
        <td>
            MwSt.-Hinweis
        </td>
        <td>
            Diese Option ist für dieses Format nicht relevant.
        </td>        
    </tr>
    <tr>
        <td>
            Artikelverfügbarkeit überschreiben
        </td>
        <td>
            Dies muss aktiviert sein, da Rakuten.de nur spezifische Werte akzeptiert, die hier eingetragen werden müssen.<br> 
            Weitere Informationen dazu im Abschnitt <b>3 Übersicht der verfügbaren Spalten</b> bei der <b>lieferzeit</b>.
        </td>        
    </tr>
</table>


## 3 Übersicht der verfügbaren Spalten

<table>
    <tr>
        <th>
            Spaltenbezeichnung
        </th>
        <th>
            Erläuterung
        </th>
    </tr>
    <tr>
        <td>
            id
        </td>
        <td>
            <b>Pflichtfeld</b> bei Variantenartikeln<br>
            <b>Inhalt:</b> Die <b>Artikel-ID</b> des Artikels mit dem Präfix <b>#</b>.
        </td>        
    </tr>
    <tr>
        <td>
            variante_zu_id
        </td>
        <td>
            <b>Pflichtfeld</b> bei Variantenartikeln<br>
            <b>Inhalt:</b> Die <b>Artikel-ID</b> des Artikels mit dem Präfix <b>#</b>.
        </td>        
    </tr>
    <tr>
        <td>
            artikelnummer
        </td>
        <td>
            <b>Pflichtfeld</b><br>
            <b>Beschränkung:</b> max. 30 Zeichen<br>
            <b>Inhalt:</b> Die <b>SKU</b> des Artikels.
        </td>        
    </tr>
    <tr>
        <td>
            produkt_bestellbar
        </td>
        <td>
            <b>Pflichtfeld</b><br>
            <b>Inhalt:</b> <b>1</b>, wenn für die Variante Bestand vorhanden oder diese nicht auf den Nettowarebestand beschränkt ist, ansonsten <b>0</b>.
        </td>        
    </tr>
    <tr>
        <td>
            produktname
        </td>
        <td>
            <b>Pflichtfeld</b><br>
            <b>Beschränkung:</b> max. 100 Zeichen<br>
            <b>Inhalt:</b> Entsprechend der Formateinstellung <b>Artikelname</b>.
        </td>        
    </tr>
    <tr>
        <td>
            hersteller
        </td>
        <td>
            <b>Beschränkung:</b> max. 30 Zeichen<br>
            <b>Inhalt:</b> Der <b>Name des Herstellers</b> des Artikels. Der <b>Externe Name</b> unter <b>Einstellungen » Artikel » Hersteller</b> wird bevorzugt, wenn vorhanden.
        </td>        
    </tr>
    <tr>
        <td>
            beschreibung
        </td>
        <td>
            <b>Pflichtfeld</b><br>
            <b>Inhalt:</b> Entsprechend der Formateinstellung <b>Beschreibung</b>.
        </td>        
    </tr>
    <tr>
        <td>
            variante
        </td>
        <td>
            <b>Pflichtfeld</b> bei Variantenartikeln<br>
            <b>Inhalt:</b> Die <b>Namen der Attribute</b>, die mit den Varianten verknüpft sind.
        </td>        
    </tr>
    <tr>
        <td>
            variantenwert
        </td>
        <td>
            <b>Pflichtfeld</b> bei Variantenartikeln<br>
            <b>Inhalt:</b> Die <b>Namen der Attributswerte</b> die mit den Varianten verknüpft sind.
        </td>        
    </tr>
    <tr>
        <td>
            isbn_ean
        </td>
        <td>
            <b>Beschränkung:</b> ISBN-10, ISBN-13, GTIN-13<br>
            <b>Inhalt:</b> Entsprechend der Formateinstellung <b>Barcode</b>.
        </td>        
    </tr>
    <tr>
        <td>
            lagerbestand
        </td>
        <td>
            <b>Beschränkung:</b> 0 bis 9999<br>
            <b>Inhalt:</b> Der <b>Nettowarenbestand der Variante</b>. Bei Artikeln, die nicht auf den Nettowarenbestand beschränkt sind, wird <b>999</b> übertragen.
        </td>        
    </tr>
    <tr>
        <td>
            preis
        </td>
        <td>
            <b>Pflichtfeld</b><br>
            <b>Beschränkung:</b> max. 5 Vorkommastellen<br>
            <b>Inhalt:</b> Der <b>Verkaufspreis</b> der Variante. Wenn der <b>UVP</b> in den Formateinstellungen aktiviert wurde und höher ist als der Verkaufspreis, wird dieser hier eingetragen.
        </td>        
    </tr>
    <tr>
        <td>
            grundpreis_inhalt
        </td>
        <td>
            <b>Beschränkung:</b> max. 3 Vor- und Nachkommastellen<br>
            <b>Inhalt:</b> Der <b>Inhalt</b> der Variante.
        </td>        
    </tr>
    <tr>
        <td>
            grundpreis_einheit
        </td>
        <td>
            <b>Beschränkung:</b> ml, l, kg, g, m, m², m³<br>
            <b>Inhalt:</b> Die <b>Einheit</b> des Inhalts der Variante.
        </td>        
    </tr>
    <tr>
        <td>
            reduzierter_preis
        </td>
        <td>
            <b>Beschränkung:</b> max. 5 Vorkommastellen<br>
            <b>Inhalt:</b> Wenn die Formateinstellung <b>UVP</b> und/oder <b>Angebotspreis</b> aktiviert wurde, steht hier der Verkaufspreis oder Angebotspreis.
        </td>        
    </tr>
    <tr>
        <td>
            bezug_reduzierter_preis
        </td>
        <td>
            <b>UVP</b> oder <b>VK</b>. Wird in Abhängigkeit der Spalte <b>preis</b> gefüllt.
        </td>        
    </tr>
    <tr>
        <td>
            mwst_klasse
        </td>
        <td>
            <b>Pflichtfeld</b><br>
            <b>Beschränkung:</b> 1,2,3,4 (1 = 19%, 2 = 7%, 3 = 0%, 4 = 10,7%)
            <b>Inhalt:</b> Wird entsprechend des Mehrwertseutersatzes übersetzt.
        </td>        
    </tr>
    <tr>
        <td>
            bestandsverwaltung_aktiv
        </td>
        <td>
            <b>Inhalt:</b> <b>1</b>, wenn die Variante auf den Nettowarenbestand beschränkt ist, ansonsten <b>0</b>. 
        </td>        
    </tr>
    <tr>
        <td>
            bild1
        </td>
        <td>
            <b>Pflichtfeld</b><br>
            <b>Inhalt:</b> Erstes Bild der Variante.
        </td>        
    </tr>
    <tr>
        <td>
            bild2-10
        </td>
        <td>
            <b>Inhalt:</b> Entsprechende Bilder der Variante.
        </td>        
    </tr>
    <tr>
        <td>
            kategorien
        </td>
        <td>
            <b>Pflichtfeld</b><br>
            <b>Inhalt:</b> <b>Kategoriepfad der Standard-Kategorie</b> für den, in den Formateinstellungen definierten <b>Mandanten</b>.
        </td>        
    </tr>
    <tr>
        <td>
            lieferzeit
        </td>
        <td>
            <b>Pflichtfeld</b><br>
            <b>Beschränkung:</b> 0,3,5,7,10,15,20,30,40,50,60
            <b>Inhalt:</b> Übersetzung gemäß der Formateinstellung <b>Artikelverfügbarkeit überschreiben</b>.
        </td>        
    </tr>
    <tr>
        <td>
            tradoria_kategorie
        </td>
        <td>
            <b>Inhalt:</b> <b>Kategorieverknüpfung der Standard-Kategorie</b> für den, in den Formateinstellungen definierten <b>Mandanten</b>.
        </td>        
    </tr>
    <tr>
        <td>
            sichtbar
        </td>
        <td>
            <b>Inhalt:</b> Immer <b>1</b>.
        </td>        
    </tr>
    <tr>
        <td>
            free_var_1-20
        </td>
        <td>
            <b>Inhalt:</b> Steht für das jeweilige <b>Freitextfeld</b>.
        </td>        
    </tr>
    <tr>
        <td>
            MPN
        </td>
        <td>
            <b>Inhalt:</b> Das <b>Model</b> der Variante.
        </td>        
    </tr>
    <tr>
        <td>
            technical_data
        </td>
        <td>
            <b>Inhalt:</b> <b>Technische Daten</b> des Artikels.
        </td>        
    </tr>
    <tr>
        <td>
            energie_klassen_gruppe
        </td>
        <td>
            <b>Beschränkung:</b> Festgelegte Werte als Merkmalwert vorgegeben.
            <b>Inhalt:</b> Wert wird über ein Merkmal vom Typ <b>Kein</b> verknüpft. Wert muss unter <b>Einstellungen » Artikel » Merkmale » Merkmal öffnen</b> über die Option <b>Rakuten.de-Merkmal</b> festgelegt werden.
        </td>        
    </tr>
    <tr>
        <td>
            energie_klasse
        </td>
        <td>
            <b>Beschränkung:</b> Festgelegte Werte als Merkmalwert vorgegeben.
            <b>Inhalt:</b> Wert wird über ein Merkmal vom Typ <b>Kein</b> verknüpft. Wert muss unter <b>Einstellungen » Artikel » Merkmale » Merkmal öffnen</b> über die Option <b>Rakuten.de-Merkmal</b> festgelegt werden.
        </td>        
    </tr>
    <tr>
        <td>
            energie_klasse_bis
        </td>
        <td>
            <b>Beschränkung:</b> Festgelegte Werte als Merkmalwert vorgegeben.
            <b>Inhalt:</b> Wert wird über ein Merkmal vom Typ <b>Kein</b> verknüpft. Wert muss unter <b>Einstellungen » Artikel » Merkmale » Merkmal öffnen</b> über die Option <b>Rakuten.de-Merkmal</b> festgelegt werden.
        </td>        
    </tr>
    <tr>
        <td>
            energie_klassen_bild
        </td>
        <td>
            <b>Inhalt:</b> Das Bild mit der Position gemäß der Formateinstellung <b>Bildposition des Energieetikette</b> wird als Energieetikette übertragen, falls vorhanden.
        </td>        
    </tr>
</table>

## 4 Lizenz

Das gesamte Projekt unterliegt der GNU AFFERO GENERAL PUBLIC LICENSE – weitere Informationen finden Sie in der [LICENSE.md](https://github.com/plentymarkets/plugin-elastic-export-rakuten-de/blob/master/LICENSE.md).
