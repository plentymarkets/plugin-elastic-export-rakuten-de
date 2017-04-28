<?php
/**
 * Created by IntelliJ IDEA.
 * User: max-nils-bruschke
 * Date: 24.04.17
 * Time: 14:46
 */

namespace ElasticExportRakutenDE\Helper;


use Plenty\Modules\StockManagement\Stock\Contracts\StockRepositoryContract;
use Plenty\Repositories\Models\PaginatedResult;

class StockHelper
{
	/**
	 * Gets the virtual stock for a specific variation
	 * @param $variation
	 * @return int $stock
	 */
	public function getStock($variation)
	{
		$stockNet = 0;
		$stockRepositoryContract = pluginApp(StockRepositoryContract::class);

		if($stockRepositoryContract instanceof StockRepositoryContract)
		{
			$stockRepositoryContract->setFilters(['variationId' => $variation['id']]);
			$stockResult = $stockRepositoryContract->listStockByWarehouseType('sales', ['stockNet'], 1, 1);

			if($stockResult instanceof PaginatedResult)
			{
				$stockList = $stockResult->getResult();
				foreach($stockList as $stock)
				{
					$stockNet = $stock->stockNet;
					break;
				}
			}
			else
			{
				$stockNet = 0;
			}
		}

		$stock = 0;

		if($variation['data']['variation']['stockLimitation'] == 2)
		{
			$stock = 999;
		}
		elseif($variation['data']['variation']['stockLimitation'] == 1 && $stockNet > 0)
		{
			if($stockNet > 999)
			{
				$stock = 999;
			}
			else
			{
				$stock = $stockNet;
			}
		}
		elseif($variation['data']['variation']['stockLimitation'] == 0)
		{
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

		return $stock;
	}
}