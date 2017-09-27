<?php
/**
 * Created by IntelliJ IDEA.
 * User: max-nils-bruschke
 * Date: 26.09.17
 * Time: 09:36
 */

namespace ElasticExportRakutenDE\Helper;


use Plenty\Modules\StockManagement\Stock\Contracts\StockRepositoryContract;
use Plenty\Repositories\Models\PaginatedResult;

class StockHelper
{
	/**
	 * Get all informations that depend on stock settings and stock volume
	 * (inventoryManagementActive, $variationAvailable, $stock)
	 * @param array $item
	 * @return array
	 */
	public function getStockList($item):array
	{
		$stockNet = 0;
		$stockModel = '';
		$stockRepositoryContract = pluginApp(StockRepositoryContract::class);

		if($stockRepositoryContract instanceof StockRepositoryContract)
		{
			$stockRepositoryContract->setFilters(['variationId' => $item['id']]);
			$stockResult = $stockRepositoryContract->listStockByWarehouseType('sales', ['stockNet'], 1, 1);

			if($stockResult instanceof PaginatedResult)
			{
				$stockList = $stockResult->getResult();
				foreach($stockList as $stock)
				{
					$stockNet = $stock->stockNet;
					$stockModel = $stock;
					break;
				}
			}
			else
			{
				$stockNet = 0;
			}
		}

		$inventoryManagementActive = 0;
		$variationAvailable = 0;
		$stock = 0;

		if($item['data']['variation']['stockLimitation'] == 2)
		{
			$variationAvailable = 1;
			$inventoryManagementActive = 0;
			$stock = 999;
		}
		elseif($item['data']['variation']['stockLimitation'] == 1)
		{
			$inventoryManagementActive = 1;

			if($stockNet > 0)
			{
				$variationAvailable = 1;

				if($stockNet > 999)
				{
					$stock = 999;
				}
				else
				{
					$stock = $stockNet;
				}
			}
		}
		elseif($item['data']['variation']['stockLimitation'] == 0)
		{
			$variationAvailable = 1;
			$inventoryManagementActive = 0;

			if($stockNet > 999)
			{
				$stock = 999;
			}
			else
			{
				if($stockNet > 0)
				{
					$stock = $stockNet;
				}
				else
				{
					$stock = 999;
				}
			}
		}

		return array (
			'stockModel'				=>	$stockModel,
			'stock'                     =>  $stock,
			'variationAvailable'        =>  $variationAvailable,
			'inventoryManagementActive' =>  $inventoryManagementActive,
		);
	}
}