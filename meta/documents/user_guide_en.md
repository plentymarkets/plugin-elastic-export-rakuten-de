
# Rakuten.de plugin user guide

<div class="container-toc"></div>

## 1 Registering with Rakuten.de

Items are sold on the market Rakuten.de. For further information about this market, refer to the [Setting up Rakuten](https://www.plentymarkets.co.uk/manual/multi-channel/rakuten/) page of the manual.

## 2 Setting up the data format RakutenDE-Plugin in plentymarkets

To use this format, you need the Elastic Export plugin.

Refer to the [Exporting data formats for price search engines](https://knowledge.plentymarkets.com/en/basics/data-exchange/exporting-data#30) page of the manual for further details about the individual format settings.

The following table lists details for settings, format settings and recommended item filters for the format **RakutenDE-Plugin**.

<table>
    <tr>
        <th>
            Settings
        </th>
        <th>
            Explanation
        </th>
    </tr>
    <tr>
        <th colspan="2">
            Settings
        </th>
    </tr>
    <tr>
        <td>
            Format
        </td>
        <td>
            Choose <b>RakutenDE-Plugin</b>.
        </td>        
    </tr>
    <tr>
        <td>
            Provisioning
        </td>
        <td>
            Choose <b>URL</b>.
        </td>        
    </tr>
    <tr>
        <td>
            File name
        </td>
        <td>
            The file name must have the ending <b>.csv</b> for Rakuten.de to be able to import the file successfully.
        </td>        
    </tr>
    <tr>
        <th colspan="2">
            Item filter
        </th>
    </tr>
    <tr>
        <td>
            Active
        </td>
        <td>
            Choose <b>active</b>.
        </td>        
    </tr>
    <tr>
        <td>
            Markets
        </td>
        <td>
            Choose <b>Rakuten.de</b>.
        </td>        
    </tr>
    <tr>
        <th colspan="2">
            Format settings
        </th>
    </tr>
    <tr>
        <td>
            Product URL
        </td>
        <td>
            This option does not affect this format.
        </td>        
    </tr>
    <tr>
        <td>
            URL parameter
        </td>
        <td>
            This option does not affect this format.
        </td>        
    </tr>
    <tr>
        <td>
            Image
        </td>
        <td>
            Choose <b>First image</b>.
        </td>        
    </tr>
    <tr>
        <td>
            Shipping costs
        </td>
        <td>
            This option does not affect this format.
        </td>        
    </tr>
    <tr>
        <td>
            MwSt.-Hinweis
        </td>
        <td>
            This option does not affect this format.
        </td>        
    </tr>
    <tr>
        <td>
            Override item availabilty
        </td>
        <td>
            This option has to be activated because Rakuten.de only accepts specific values which have to be set here.<br> 
            Additional information are provided in <b>3 Overview of available columns</b> for the column <b>lieferzeit</b>.
        </td>        
    </tr>
</table>

## 3 Overview of available columns
<table>
    <tr>
        <th>
            Column description
        </th>
        <th>
            Explanation
        </th>
    </tr>
    <tr>
        <td>
            id
        </td>
        <td>
            <b>Required</b> for variations<br>
            <b>Content:</b> The <b>item ID</b> of the item with the prefix <b>#</b>.
        </td>        
    </tr>
    <tr>
        <td>
            variante_zu_id
        </td>
        <td>
            <b>Required</b> for variations<br>
            <b>Content:</b> The <b>item ID</b> of the item with the prefix <b>#</b>.
        </td>        
    </tr>
    <tr>
        <td>
            artikelnummer
        </td>
        <td>
            <b>Required</b><br>
            <b>Limitation:</b> max. 30 characters<br>
            <b>Content:</b> The <b>SKU</b> of the item. For parent rows the parent SKU will be exported.
        </td>        
    </tr>
    <tr>
        <td>
            produkt_bestellbar
        </td>
        <td>
            <b>Required</b><br>
            <b>Content:</b> <b>1</b>, if the variation has stock and is not limited to net stock, else <b>0</b>.
        </td>        
    </tr>
    <tr>
        <td>
            produktname
        </td>
        <td>
            <b>Required</b><br>
            <b>Limitation:</b> max. 100 characters<br>
            <b>Content:</b> According to the format setting <b>item name</b>.
        </td>        
    </tr>
    <tr>
        <td>
            hersteller
        </td>
        <td>
            <b>Limitation:</b> max. 30 characters<br>
            <b>Content:</b> The <b>name of the manufacturer</b> of the item. The <b>external name</b> within <b>Settings » Items » Manufacturer</b> will be preferred if existing.
        </td>        
    </tr>
    <tr>
        <td>
            beschreibung
        </td>
        <td>
            <b>Required</b><br>
            <b>Content:</b> According to the format setting <b>description</b>.
        </td>        
    </tr>
    <tr>
        <td>
            variante
        </td>
        <td>
            <b>Required</b> for variations<br>
            <b>Content:</b> The <b>names of the attributes</b>, linked to the item.
        </td>        
    </tr>
    <tr>
        <td>
            variantenwert
        </td>
        <td>
            <b>Required</b> for variations<br>
            <b>Content:</b> The <b>names of the attributevalues</b> linked to the variation.
        </td>        
    </tr>
    <tr>
        <td>
            isbn_ean
        </td>
        <td>
            <b>Limitation:</b> ISBN-10, ISBN-13, GTIN-13<br>
            <b>Content:</b> According to the format setting <b>Barcode</b>.
        </td>        
    </tr>
    <tr>
        <td>
            lagerbestand
        </td>
        <td>
            <b>Limitation:</b> from 0 to 9999<br>
            <b>Content:</b> The <b>net stock of the variation</b>. If the variation is not limited to net stock <b>999</b> will be set as value.
        </td>        
    </tr>
    <tr>
        <td>
            preis
        </td>
        <td>
            <b>Required</b><br>
            <b>Limitation:</b> max. 5 predecimals<br>
            <b>Content:</b> The <b>sales price</b> of the variation. If the <b>RRP</b> was activated in the format settings and is higher than the sales price the RRP will be used here.
        </td>        
    </tr>
    <tr>
        <td>
            grundpreis_inhalt
        </td>
        <td>
            <b>Limitation:</b> max. 3 predecimals and decimals <br>
            <b>Content:</b> The <b>lot</b> of the variation.
        </td>        
    </tr>
    <tr>
        <td>
            grundpreis_einheit
        </td>
        <td>
            <b>Limitation:</b> ml, l, kg, g, m, m², m³<br>
            <b>Content:</b> The <b>unit</b> of the lot of the variation.
        </td>        
    </tr>
    <tr>
        <td>
            reduzierter_preis
        </td>
        <td>
            <b>Limitation:</b> max. 5 predecimals<br>
            <b>Content:</b> If the format setting <b>RRP</b> and/or <b>Offer price</b> was activated, the sales price or offer price will be used here.
        </td>        
    </tr>
    <tr>
        <td>
            bezug_reduzierter_preis
        </td>
        <td>
            <b>UVP</b> or <b>VK</b>. This will be set in dependency of the column <b>preis</b>.
        </td>
    </tr>
    <tr>
        <td>
            mwst_klasse
        </td>
        <td>
            <b>Required</b><br>
            <b>Limitation:</b> 1,2,3,4 (1 = 19%, 2 = 7%, 3 = 0%, 4 = 10,7%)
            <b>Content:</b> Will be translated on basis of the vate rate.
        </td>        
    </tr>
    <tr>
        <td>
            bestandsverwaltung_aktiv
        </td>
        <td>
            <b>Content:</b> <b>1</b>, if the variation is limited to net stock, else <b>0</b>. 
        </td>        
    </tr>
    <tr>
        <td>
            bild1
        </td>
        <td>
            <b>Required</b><br>
            <b>Content:</b> The first image of the variation.
        </td>        
    </tr>
    <tr>
        <td>
            bild2-10
        </td>
        <td>
            <b>Content:</b> The corresponding images of the variation.
        </td>        
    </tr>
    <tr>
        <td>
            kategorien
        </td>
        <td>
            <b>Required</b><br>
            <b>Content:</b> <b>category path of the standard category</b> for the <b>Client</b> configured in the format settings.
        </td>        
    </tr>
    <tr>
        <td>
            lieferzeit
        </td>
        <td>
            <b>Required</b><br>
            <b>Limitation:</b> 0,3,5,7,10,15,20,30,40,50,60
            <b>Content:</b> Translation according to the format setting <b>Override item availabilty</b>.
        </td>        
    </tr>
    <tr>
        <td>
            tradoria_kategorie
        </td>
        <td>
            <b>Content:</b> The <b>category path of the default cateogory</b> for the defined <b>client</b> in the format settings.
        </td>        
    </tr>
    <tr>
        <td>
            sichtbar
        </td>
        <td>
            <b>Content:</b> Always set to <b>1</b>.
        </td>        
    </tr>
    <tr>
        <td>
            free_var_1-20
        </td>
        <td>
            <b>Content:</b> The corresponding <b>Free text field</b>.
        </td>        
    </tr>
    <tr>
        <td>
            MPN
        </td>
        <td>
            <b>Content:</b> The <b>model</b> of the variations.
        </td>        
    </tr>
    <tr>
        <td>
            technical_data
        </td>
        <td>
            <b>Content:</b> The <b>Technical data</b> of the item.
        </td>        
    </tr>
    <tr>
        <td>
            energie_klassen_gruppe
        </td>
        <td>
            <b>Limitation:</b> Fixed value as given for the property link.
            <b>Content:</b> Value is set through a property of type <b>No</b>. The Value has to be set over <b>Settings » Item » Properties » open property</b> through the option <b>Rakuten.de property</b>.
        </td>        
    </tr>
    <tr>
        <td>
            energie_klasse
        </td>
        <td>
            <b>Limitation:</b> Fixed value as given for the property link.
            <b>Content:</b> Value is set through a property of type <b>No</b>. The Value has to be set over <b>Settings » Item » Properties » open property</b> through the option <b>Rakuten.de property</b>.        </td>        
    </tr>
    <tr>
        <td>
            energie_klasse_bis
        </td>
        <td>
            <b>Limitation:</b> Fixed value as given for the property link.
            <b>Content:</b> Value is set through a property of type <b>No</b>. The Value has to be set over <b>Settings » Item » Properties » open property</b> through the option <b>Rakuten.de property</b>.        </td>        
    </tr>
    <tr>
        <td>
            energie_klassen_bild
        </td>
        <td>
            <b>Content:</b> The image with the position corresponding to the format settings <b>Image position of the energy efficiency label</b> if given.
        </td>        
    </tr>
</table>

## 4 Licence

This project is licensed under the GNU AFFERO GENERAL PUBLIC LICENSE.- find further information in the [LICENSE.md](https://github.com/plentymarkets/plugin-elastic-export-rakuten-de/blob/master/LICENSE.md).
