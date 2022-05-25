# Release Notes for Elastic Export Rakuten.de

## v1.3.34 (2022-05-25)

### Changed
- UPDATE - Additional updates to ensure compatibility with PHP 8.

## v1.3.33 (2020-06-19)

### Changed
- For the period of 01/07/2020 to 31/12/2020, the mapping of tax classes for Rakuten.de will be adjusted based on the tax rate of the merchant. During this period, a VAT rate of 16% will be mapped to tax class 1 and a VAT rate of 5% to tax class 2.

## v1.3.32 (2020-02-07)

### Fixed
- The stock update only deactivates variations on Rakuten that were actually deactivated.

## v1.3.31 (2020-02-06)

### Changed
- All VAT classes supported by Rakuten are now exported with the correct IDs. Now the VAT classes for 10% / 20% / 13% / 2,1% / 5% / 5,5% are supported too.

## v1.3.30 (2019-11-28)

### Fixed
- The VAT class was always exported with ID 1. This is equivalent to 19% on Rakuten.de.

## v1.3.29 (2019-11-27)

### Fixed
- The column "energie_klassen_gruppe" for items with variants will now only be exported in the parent rows and not in the child rows.

## v1.3.28 (2019-11-15)

### Fixed
- The last item in every processed ES-Stack is no longer removed or exported without price and stock.

## v1.3.27 (2019-10-31)

### Changed
- The price and stock update got a performance upgrade.
**Attention!** This version requires the Elastic Export plugin version 1.6.3 or higher.

## v1.3.26 (2019-10-08)

### Changed
- The user guide was updated (changed form of address, corrected broken links).

## v1.3.25 (2019-09-26)

### Fixed
- Multiple curl sessions were opened instead of reusing already open sessions. This could cause cross-system issues, if too many sessions were open simultaneously.

## v1.3.24 (2019-08-15)

### Fixed
- The internal flag at the variation for the stock update was not set.

## v1.3.23 (2019-08-12)

### Fixed
- In some cases, stock was not updated.

## v1.3.22 (2019-06-05)

### Changed
- Caches for attributes, categories and free text fields will be cleaned regularly in order to avoid unnecessary resource usage.

## v1.3.21 (2019-01-23)

### Changed
- An incorrect link in the user guide was corrected.

## v1.3.20 (2019-01-16)

### Fixed
- Removed the transmission of unnecessary data within the item update.

## v1.3.19 (2019-01-11)

### Changed
- Stability improvements for stock and price synchronisation.

## v1.3.18 (2018-11-08)

### Fixed
- FIX The net stock of each individual warehouse was added to the virtual stock when stock was updated.

## v1.3.17 (2018-10-31)

### Changed
- Stability improvements for stock and price synchronisation.

## v1.3.16 (2018-10-18)

### Changed
- Stability improvements for stock and price synchronisation.

## v1.3.15 (2018-10-02)

### Changed
- Performance improvements for stock and price export.

## v1.3.14 (2018-09-28)

### Changed
- Performance improvements for stock and price export.

## v1.3.13 (2018-09-05)

### Fixed
- The parent SKU for items without variations is created correctly.

## v1.3.12 (2018-09-03)

### Fixed
- The price update considers and transmits the reduced price.

## v1.3.11 (2018-07-24)

### Changed
- Further information about setting up the plugin was added to the user guide.

## v1.3.10 (2018-05-08)

### Changed
- The plugin config is multilingual.

## v1.3.9 (2018-04-30)

### Changed
- Laravel 5.5 update.

## v1.3.8 (2018-04-05)

### Added
- The PriceHelper consider the new setting **Live currency conversion**.

### Changed
- The SKU logic uses data from the database.
- The class FiltrationService is responsible for the filtration of all variations.
- Preview images updated. 

## v1.3.7 (2018-03-06)

### Fixed
- Adjusted tables in the user guide.

## v1.3.6 (2018-02-21)

### Changed
- Updated plugin short description.

## v1.3.5 (2018-02-13)

### Added
- The PriceHelper will now consider the new setting "Retail price".

## 1.3.4 (2018-02-09)

### Fixed
- An issue was fixed which caused the prefix and suffix to be missing at the parent SKU when generating items without children.

## 1.3.3 (2018-01-19)

### Fixed
- An issue was fixed which caused the attribute values which contained the delimeter **"|"** to cause an error on Rakuten. 

## v1.3.2 (2017-01-11)

### Changed
- Every category path of a variation will be transmitted, as long as it is enabled for the client and the corresponding language is configured.

## v1.3.1 (2017-01-09)

### Changed
- Inactive variations will now be send only once within the stock update.

## v1.3.0 (2017-12-28)

### Added
- The StockHelper takes the new fields "Stockbuffer", "Stock for variations without stock limitation" and "Stock for variations with not stock administration" into account.

## 1.2.15 (2017-12-28)

### Changed
- The delta time for the stock and price update was reduced to 2h.

## 1.2.14 (2017-12-05)

### Changed
- Logs for the stock and price update as well as for the export will now be saved as batches of 100.

## 1.2.13 (2017-12-05)

### Fixed
- An issue was fixed which caused the item update to fail because of a missing endpoint.
- An issue was fixed which caused main variations to be exported as single items even though item variations exist.

## 1.2.12 (2017-11-24)

### Fixed
- An issue was fixed which caused the item update to fail if there was more then one variation.

## 1.2.11 (2017-11-13)

### Changed
- URLs were updated in the plugin description.

### Fixed
- An issue was fixed which caused product or variation updates to be denied during the stock or price update.

## 1.2.10 (2017-11-03)

### Fixed
- An issue was fixed which caused duplicate entries of parent rows.

## 1.2.9 (2017-10-27)

### Fixed
- An issue was fixed which caused the connection to elasticsearch to break.

## v1.2.8 (2017-10-20)

### Changed
- The export gets the result fields from the ResultFieldsDataProvider within the ElasticExport plugin.

### Fixed
- An issue was fixed, which caused problems regarding the price and stock update.

## v1.2.7 (2017-09-27)	

### Added
- The fields **available** and **stock_policy** were added to the **stock update**.

## v1.2.6 (2017-09-18)

### Changed
- Optimized the **stock update**.

## v1.2.5 (2017-09-11)

### Fixed
- An issue was fixed which caused the **inventory management** to not be active.

## v1.2.4 (2017-07-29)

### Added
- It is now possible to export an energy efficiency label. The image with the position corresponding the option **Image position of the energy efficiency label** in the format settings will be exported as the energy efficiency label.

### Changed
- The user guide was extended.

## v1.2.3 (2017-07-20)

### Added
- It is now possible to set a pre- and suffix for the parent SKU. You can find the setting at the plugin settings within the tab "Parent-SKU". This setting will only trigger if this item does not already have a parent SKU.

### Fixed
- An issue was fixed which caused the free text fields to not be read anymore.

### Changed
- The stock and price update has been changed so it will only trigger if the values has changed within the last 2 days.

## v1.2.2 (2017-07-13)

### Fixed
- An issue was fixed which caused wrong decisions for building the parent or child row.

## v1.2.1 (2017-07-11)

### Fixed
- To fix the problem where the same item was generated multiple times on rakuten, we now generate a parent SKU.

## v1.2.0 (2017-06-30)

### Added
- We integrated a feature which automatically updates the stock.

## v1.1.11 (2017-06-02)

### Fixed
- An issue was fixed which caused the export to use the wrong webstore client to get the price.

## v1.1.10 (2017-05-18)

### Fixed
- An issue was fixed which caused elastic search to ignore the set referrers for the barcodes. 

## v1.1.9 (2017-05-15)

### Fixed
- An issue was fixed which caused a duplicate entry for the itemnumber.

## v1.1.8 (2017-05-12)

### Fixed
- An issue was fixed which caused the variations not to be in the right order.

## v1.1.7 (2017-05-10)

### Fixed
- Image positions will now be correctly interpreted.

## v1.1.6 (2017-05-09)

### Fixed
- The description will now be exported in the configured language. 

## v1.1.5 (2017-05-05)

### Fixed
- An issue was fixed which caused errors while loading the export format.

## v1.1.4 (2017-05-02)

### Fixed
- An issue was fixed which caused errors while saving the SKU's.

## v1.1.3 (2017-05-02)

### Fixed
- An issue was fixed which caused to assign the SKU's always to the account 0.

### Changed
- Outsourced the stock filter logic to the Elastic Export plugin.

## v1.1.2 (2017-04-28)

### Fixed
- An issue was fixed which caused to transfer the price 0.00 if no price was set.

## v1.1.1 (2017-04-18)

### Fixed
- An issue was fixed which caused the plugin to fail at the build productive.

## v1.1.0 (2017-04-06)

### Fixed
- An issue was fixed which caused the item filter "stock" to not work properly.

## v1.0.9 (2017-04-04)

### Changed
- Current variation stock is calculated on the basis of the sales warehouse.
- The logic was adjusted to improve the stability of the export.

## v1.0.8 (2017-03-30)

### Added
- Added a new mutator so we will prevent trying to get access to an array key which not exists.

## v1.0.7 (2017-03-29)

### Added
- Performance has been improved.

## v1.0.6 (2017-03-28)

### Added
- Added validation checks.

## v1.0.5 (2017-03-27)

### Added
- Added a few logs.

### Changed
- Getting the item-data from elastic search now at the generator.

## v1.0.4 (2017-03-23)

### Changed
- Removed the ItemDataLayer to improve the performance.

## v1.0.3 (2017-03-22)

### Fixed
- We now use a different value to get the image URLs for plugins working with elastic search.

## v1.0.2 (2017-03-13)

### Added
- Added marketplace name.

### Changed
- Changed plugin icons.

## v1.0.1 (2017-03-03)
- Adjustment for the ResultField, so the imageMutator does not affect the image outcome anymore if the referrer "ALL" is set

## v1.0.0 (2017-02-20)
 
### Added
- Added initial plugin files
