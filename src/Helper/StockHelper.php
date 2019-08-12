<?php

namespace ElasticExportRakutenDE\Helper;

use Plenty\Modules\StockManagement\Stock\Contracts\StockRepositoryContract;
use Plenty\Modules\StockManagement\Stock\Models\Stock;
use Plenty\Repositories\Models\PaginatedResult;
use Plenty\Modules\Helper\Models\KeyValue;

/**
 * Class StockHelper
 *
 * @package ElasticExportRakutenDE\Helper
 */
class StockHelper
{
	private $stockBuffer = 0;

	private $stockForVariationsWithoutStockLimitation = null;

	private $stockForVariationsWithoutStockAdministration = null;

	/**
	 * @var StockRepositoryContract
	 */
	private $stockRepository;

    /**
	 * StockHelper constructor.
	 * @param StockRepositoryContract $stockRepository
	 */
	public function __construct(StockRepositoryContract $stockRepository)
	{
		$this->stockRepository = $stockRepository;
    }

	/**
	 * Get all information that depend on stock settings and stock volume
	 * (inventoryManagementActive, $variationAvailable, $stock)
     *
	 * @param array $item
	 * @return array
	 */
	public function getStockList($item):array
	{
		$stockNet = 0;
		$stockUpdatedAt = '';

		$this->stockRepository->setFilters(['variationId' => $item['id']]);
		$stockResult = $this->stockRepository->listStockByWarehouseType('sales', ['*'], 1, 1);

		if($stockResult instanceof PaginatedResult)
		{
			$stockList = $stockResult->getResult();
			foreach($stockList as $stock)
			{
				if($stock instanceof Stock)
				{
					$stockNet = $stock->stockNet;
					$stockUpdatedAt = $stock->updatedAt->timestamp;
					break;
				}
			}
		}
		else
		{
			$stockNet = 0;
		}

		$inventoryManagementActive = 0;
		$variationAvailable = 0;
		$stock = 0;

		// stock limitation do not stock inventory
		if($item['data']['variation']['stockLimitation'] == 2)
		{
			$variationAvailable = 1;
			$inventoryManagementActive = 0;

			if(!is_null($this->stockForVariationsWithoutStockAdministration))
			{
				$stock = $this->stockForVariationsWithoutStockAdministration;
			}
			else
			{
				$stock = 999;
			}
		}

		// stock limitation use nett stock
		elseif($item['data']['variation']['stockLimitation'] == 1)
		{
			$inventoryManagementActive = 1;

			if($stockNet > 0)
			{
				if($stockNet > 999)
				{
					$stock = 999;
				}
				else
				{
					$stock = $stockNet - $this->stockBuffer;
				}

				if($stock < 0)
				{
					$stock = 0;
				}
				else
				{
					$variationAvailable = 1;
				}
			}
		}

		// no limitation
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
				elseif(!is_null($this->stockForVariationsWithoutStockLimitation))
				{
				$stock = $this->stockForVariationsWithoutStockLimitation;
				}
				else
				{
					$stock = 999;
				}
			}
		}

		return array (
			'updatedAt'					=>	$stockUpdatedAt,
			'stock'                     =>  $stock,
			'variationAvailable'        =>  $variationAvailable,
			'inventoryManagementActive' =>  $inventoryManagementActive,
		);
	}

	/**
	 * @param KeyValue $settings
	 */
	public function setAdditionalStockInformation(KeyValue $settings)
	{
		if(!is_null($settings->get('stockBuffer')) && $settings->get('stockBuffer') > 0)
		{
			$this->stockBuffer = $settings->get('stockBuffer');
		}

		if(!is_null($settings->get('stockForVariationsWithoutStockAdministration')) && $settings->get('stockForVariationsWithoutStockAdministration') > 0)
		{
			$this->stockForVariationsWithoutStockAdministration = $settings->get('stockForVariationsWithoutStockAdministration');
		}

		if(!is_null($settings->get('stockForVariationsWithoutStockLimitation')) && $settings->get('stockForVariationsWithoutStockLimitation') > 0)
		{
			$this->stockForVariationsWithoutStockLimitation = $settings->get('stockForVariationsWithoutStockLimitation');
		}
	}

    /**
     * @param array $item
     * @param array $preloadedStockData
     * @return array
     */
	public function getStockByPreloadedValue($item, $preloadedStockData)
    {
        $stockNet = 0;
        $stockUpdatedAt = '';

        foreach($preloadedStockData as $warehouse) {
        	if($warehouse['warehouseId'] == 0) {
        		$stockNet = $warehouse['stockNet'];
                $stockUpdatedAt = $warehouse['updatedAt'];

        		break;
			}
        }

        $inventoryManagementActive = 0;
        $variationAvailable = 0;
        $stock = 0;

        // stock limitation do not stock inventory
        if($item['data']['variation']['stockLimitation'] == 2)
        {
            $variationAvailable = 1;
            $inventoryManagementActive = 0;

            if(!is_null($this->stockForVariationsWithoutStockAdministration))
            {
                $stock = $this->stockForVariationsWithoutStockAdministration;
            }
            else
            {
                $stock = 999;
            }
        }

        // stock limitation use nett stock
        elseif($item['data']['variation']['stockLimitation'] == 1)
        {
            $inventoryManagementActive = 1;

            if($stockNet > 0)
            {
                if($stockNet > 999)
                {
                    $stock = 999;
                }
                else
                {
                    $stock = $stockNet - $this->stockBuffer;
                }

                if($stock < 0)
                {
                    $stock = 0;
                }
                else
                {
                    $variationAvailable = 1;
                }
            }
        }

        // no limitation
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
                elseif(!is_null($this->stockForVariationsWithoutStockLimitation))
                {
                    $stock = $this->stockForVariationsWithoutStockLimitation;
                }
                else
                {
                    $stock = 999;
                }
            }
        }

        return array (
            'updatedAt'					=>	strtotime($stockUpdatedAt),
            'stock'                     =>  $stock,
            'variationAvailable'        =>  $variationAvailable,
            'inventoryManagementActive' =>  $inventoryManagementActive,
        );
    }

}