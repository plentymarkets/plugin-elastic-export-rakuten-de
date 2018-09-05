# Release Notes for Elastic Export Rakuten.de

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
