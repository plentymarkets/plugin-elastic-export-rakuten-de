<?php

namespace ElasticExportRakutenDE\Helper;

use Plenty\Modules\Item\VariationSku\Contracts\VariationSkuBulkRepositoryContract;

/**
 * Class SkuHelper
 *
 * @package ElasticExportRakutenDE\Helper
 */
class SkuHelper
{
    const LIMIT = 500;
    
    /** @var VariationSkuBulkRepositoryContract */
    private $variationSkuBulkRepository;
    
    /** @var array */
    private $createStack = [];
    
    /** @var array */
    private $statusStack = [];
    
    /** @var array */
    private $exportedAtStack = [];
    
    /** @var array */
    private $stockUpdatedAtStack = [];

    /**
     * SkuHelper constructor.
     *
     * @param VariationSkuBulkRepositoryContract $variationSkuBulkRepositoryContract
     */
    public function __construct(VariationSkuBulkRepositoryContract $variationSkuBulkRepositoryContract)
    {
        $this->variationSkuBulkRepository = $variationSkuBulkRepositoryContract;
    }

    /**
     * @param array $data
     */
    public function create(array $data)
    {
        $this->createStack[] = $data;
        if(count($this->createStack) == self::LIMIT) {
            $this->variationSkuBulkRepository->create($this->createStack);
            $this->createStack = [];
        }
    }

    /**
     * Runs the actions if their stacks has entries.
     */
    public function finish()
    {
        if(count($this->createStack)) {
            $this->variationSkuBulkRepository->create($this->createStack);
            $this->createStack = [];
        }

        if(count($this->statusStack)) {
            foreach($this->statusStack as $status => $ids) {
                $this->variationSkuBulkRepository->updateStatus($this->statusStack, $status);
                $this->statusStack = [];
            }
        }

        if(count($this->exportedAtStack)) {
            $this->variationSkuBulkRepository->updateExportedAt($this->exportedAtStack, date('Y-m-d H:i:s'));
            $this->exportedAtStack = [];
        }

        if(count($this->stockUpdatedAtStack)) {
            $this->variationSkuBulkRepository->updateStockUpdatedAt($this->stockUpdatedAtStack, date('Y-m-d H:i:s'));
            $this->stockUpdatedAtStack = [];
        }
    }

    /**
     * @param int $id
     * @param string $status
     */
    public function updateStatus(int $id, string $status)
    {
        $this->statusStack[$status] = $id;
        if(count($this->statusStack) == self::LIMIT) {
            foreach($this->statusStack as $status => $ids) {
                $this->variationSkuBulkRepository->updateStatus($this->statusStack, $status);
            }
            $this->createStack = [];
        }
    }

    /**
     * @param int $id
     */
    public function updateExportedAt(int $id)
    {
        $this->exportedAtStack[] = $id;
        if(count($this->exportedAtStack) == self::LIMIT) {
            $this->variationSkuBulkRepository->updateExportedAt($this->exportedAtStack, date('Y-m-d H:i:s'));
            $this->exportedAtStack = [];
        }
    }

    /**
     * @param int $id
     */
    public function updateStockUpdatedAt(int $id)
    {
        $this->stockUpdatedAtStack[] = $id;
        if(count($this->stockUpdatedAtStack) == self::LIMIT) {
            $this->variationSkuBulkRepository->updateStockUpdatedAt($this->stockUpdatedAtStack, date('Y-m-d H:i:s'));
            $this->stockUpdatedAtStack = [];
        }
    }
}