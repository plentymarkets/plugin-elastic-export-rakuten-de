
# Rakuten.de plugin user guide

<div class="container-toc"></div>

## 1 Registering with Rakuten.de

Items are sold on the market Rakuten.de. For further information about this market, refer to the [Setting up Rakuten](https://knowledge.plentymarkets.com/en/omni-channel/multi-channel/rakuten/rakuten-setup) page of the manual.

## 2 Setting up the data format RakutenDE-Plugin in plentymarkets

By installing this plugin you will receive the export format **RakutenDE-Plugin**. Use this format to exchange data between plentymarkets and Rakuten. It is required to install the Plugin **Elastic export** from the plentyMarketplace first before you can use the format **RakutenDE-Plugin** in plentymarkets.

Once both plugins are installed, you can create the export format **RakutenDE-Plugin**. Refer to the [Exporting data with the dynamic import](https://knowledge.plentymarkets.com/en/basics/data-exchange/export-import/exporting-data#30) page of the manual for further details about the individual format settings.

Creating a new export format:

1. Go to **Data » Elastic export**.
2. Click on **New export**.
3. Carry out the settings as desired. Pay attention to the information given in table 1.
4. **Save** the settings.
→ The export format is given an ID and it appears in the overview within the **Exports** tab.

The following table lists details for settings, format settings and recommended item filters for the format **RakutenDE-Plugin**.

| **Setting**                                           | **Explanation**|
| :---                                                  | :--- |                                            
| **Settings**                                          | |
| **Name**                                              | Enter a name. The export format is listed by this name in the overview within the **Exports** tab. |
| **Type**                                              | Select the type **Item** from the drop-down list. |
| **Format**                                            | Choose **RakutenDE-Plugin**. |
| **Limit**                                             | Enter a number. If you want to transfer more than 9,999 data records to Rakuten, then the output file will not be generated again for another 24 hours. This is to save resources. If more than 9,999 data records are necessary, the setting **Generate cache file** has to be activated. |
| **Generate cache file**                               | Place a check mark if you want to transfer more than 9,999 data records to Rakuten. The output file will not be generated again for another 24 hours. We recommend that you do not activate this setting for more than 20 export formats. This is to ensure a high performance of the elastic export. |
| **Provisioning**                                      | Choose **URL**. |
| **File name**                                         | The file name must have the ending **.csv** for Rakuten.de to be able to import the file successfully. |
| **Token, URL**                                        | If you have selected the option **URL** under **Provisioning**, then click on **Generate token**. The token is entered automatically. The URL is entered automatically if the token has been generated under **Token**. |
| **Item filters**                                      | |
| **Add item filters**                                  | Select an item filter from the drop-down list and click on **Add**. There are no filters set in default. It is possible to add multiple item filters from the drop-down list one after the other.<br/> **Variations** = Select **Transfer all** or **Only transfer main variations**.<br/> **Markets** = Select **Rakuten.de**.<br/> **Currency** = Select a currency.<br/> **Category** = Activate to transfer the item with its category link. Only items belonging to this category are exported.<br/> **Image** = Activate to transfer the item with its image. Only items with images are transferred.<br/> **Client** = Select a client.<br/> **Flag 1-2** = Select the flag.<br/> **Manufacturer** = Select one, several, or **ALL** manufacturers.<br/> **Active** = Only active variations are exported. |
| **Format settings**                                   | |
| **Product URL**                                       | This option does not affect this format. |
| **Client**                                            | Select a client. This setting is used for the URL structure. |
| **URL parameter**                                     | This option does not affect this format. |
| **Order referrer**                                    | Choose the order referrer that should be assigned during the order import from the drop-down list. |
| **Marketplace account**                               | Select the market account from the drop-down list. The selected referrer is added to the product URL so that sales can be analysed later. |
| **Language**                                          | Select the language from the drop-down list. |
| **Item name**                                         | Select **Name 1**, **Name 2**, or **Name 3**. These names are saved in the **Texts** tab of the item.<br/> Enter a number into the **Maximum number of characters (def. text)** field if desired. This specifies how many characters are exported for the item name. |
| **Preview text**                                      | Select the text that you want to transfer as preview text.<br/> Enter a number into the **Maximum number of characters (def. text)** field if desired. This specifies how many characters should be exported for the preview text. Activate the option **Remove HTML tags** if you want HTML tags to be removed during the export.<br/> If you only want to allow specific HTML tags to be exported, then enter these tages into the field **Permitted HTML tags, separated by comma (def. text)**. Use commas to separate multiple tags. |
| **Description**                                       | Select the text that you want to transfer as description.<br/>  Enter a number into the **Maximum number of characters (def. text)** field if desired. This specifies how many characters should be exported for the description. Activate the option **Remove HTML tags** if you want HTML tags to be removed during the export.<br/> If you only want to allow specific HTML tags to be exported, then enter these tags into the field **Permitted HTML tags, separated by comma (def. text)**. Use commas to separate multiple tags. |
| **Target country**                                    | Select the target country from the drop-down list. |
| **Barcode**                                           | Select the ASIN, ISBN, or an EAN from the drop-down list. The barcode has to be linked to the order referrer selected above. If the barcode is not linked to the order referrer, it will not be exported. |
| **Image**                                             | Choose **First image**. |
| **Image position of the energy efficiency label**     | Enter the position of the energy label. Every image that should be transferred as an energy label must have this position. |
| **Stockbuffer**                                       | The stock buffer for variations with the limitation to the netto stock. |
| **Stock for variations without stock limitation**     | The stock for variations without stock limitation.|
| **Stock for variations without stock administration** | The stock for variations without stock administration. |
| **Live currency conversion**                          | Activate this option to convert the price into the currency of the selected country of delivery. The price has to be released for the corresponding currency. |
| **Retail price**                                      | Select the gross price or net price from the drop-down list. |
| **Offer price**                                       | Activate to transfer the offer price. |
| **RRP**                                               | Activate to transfer the RRP. |
| **Shipping costs**                                    | This option does not affect this format. |
| **VAT note**                                          | This option does not affect this format. |
| **Overwrite item availability**                       | This option has to be activated, as Rakuten.de only accepts specific values which have to be set here. <br />Additional information is provided in **3 Overview of available columns** in the column **lieferzeit**. |

_Tab. 1: Settings for the data format **RakutenDE-Plugin**_  
 
 ## 3 Available columns of the export file
 
| **Column description**   | **Explanation** |
| :---                     | :--- |
| id                       | **Required** for variations <br /> The **item ID** of the item with the prefix **#**. |
| variante_zu_id           | **Required** for variations <br /> The **item ID** of the item with the prefix **#**. |
| artikelnummer            | **Required** <br />**Limitation**: max. 30 characters <br /> The **SKU** of the item. For parent rows the parent SKU will be exported. |
| produkt_bestellbar       | **Required** <br /> **1** if the variation has stock and is not limited to net stock. Otherwise **0**. |
| produktname              | **Required** <br />**Limitation**: max. 100 characters <br /> According to the format setting **Item name**. |
| hersteller               | **Limitation**: max. 30 characters <br /> The **name of the manufacturer** of the item. The **external name** within **Settings » Items » Manufacturer** will be preferred if existing. |
| beschreibung             | **Required** <br /> According to the format setting **Description**. |
| variante                 | **Required** for variations <br /> The **names of the attributes** linked to the item. |
| variantenwert            | **Required** for variations <br /> The **names of the attribute values** linked to the variation. |
| isbn_ean                 | **Limitation**: ISBN-10, ISBN-13, GTIN-13 <br /> According to the format setting **Barcode**. |
| lagerbestand             | **Limitation**: from 0 to 9999 <br /> The **net stock of the variation**. If the variation is not limited to net stock **999** will be set as value. |
| preis                    | **Required** <br />**Limitation**: max. 5 predecimals <br /> The **sales price** of the variation. If the **RRP** was activated in the format settings and is higher than the sales price the RRP will be used here. |
| grundpreis_inhalt        | **Limitation**: max. 3 predecimals and decimals <br /> The **content** of the variation. |
| grundpreis_einheit       | **Limitation**: ml, l, kg, g, m, m², m³<br /> The **unit** of the content of the variation. |
| reduzierter_preis        | **Limitation**: max. 5 predecimals <br /> If the format setting **RRP** and/or **offer price** was activated, the sales price or offer price will be used here. |
| bezug_reduzierter_preis  | **RRP** or **RP**. This will be set in dependency to the column **preis**. |
| mwst_klasse              | **Required** <br />**Limitation**: 1,2,3,4 (1 = 19%, 2 = 7%, 3 = 0%, 4 = 10,7%) <br /> Is translated on basis of the VAT rate. |
| bestandsverwaltung_aktiv | **1**, if the variation is limited to net stock. Otherwise **0**. |
| bild1                    | **Required**: <br /> The first image of the variation. |
| bild2-10                 | **Content**: The corresponding images of the variation. |
| kategorien               | **Required** <br /> **Category path of the standard category** for the **client** configured in the format settings. |
| lieferzeit               | **Required** <br />**Limitation**: 0,3,5,7,10,15,20,30,40,50,60 <br /> Translation according to the format setting **Overwrite item availability**. |
| tradoria_kategorie       | The **category path of the default category** for the defined **client** in the default settings. |
| sichtbar                 | Always set to **1**. |
| free_var_1-20            | The corresponding **Free text field**. |
| MPN                      | The **model** of the variations. |
| technical_data           | The **technical data** of the item. |
| energie_klassen_gruppe   | **Limitation**: Fixed value as given for the property link. <br /> Value is set through a property of type **No**. The value has to be set in the menu **Settings » Item » Properties » Open property** through the option **Rakuten.de property**. |
| energie_klasse           | **Limitation**: Fixed value as given for the property link. <br /> Value is set through a property of type **No**. The value has to be set in the menu **Settings » Item » Properties » Open property** through the option **Rakuten.de property**. |
| energie_klasse_bis       | **Limitation**: Fixed value as given for the property link. <br /> Value is set through a property of type **No**. The value has to be set in the menu **Settings » Item » Properties » Open property** through the option **Rakuten.de property**. |
| energie_klassen_bild     | The image with the position corresponding to the format settings **Image position of the energy efficiency label** if given. |

## 4 Licence

This project is licensed under the GNU AFFERO GENERAL PUBLIC LICENSE.- find further information in the [LICENSE.md](https://github.com/plentymarkets/plugin-elastic-export-rakuten-de/blob/master/LICENSE.md).
