<?php

namespace Acruz\Customer\Model;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\Framework\Exception\NoSuchEntityException;

class ProductsSearch
{
    /**
     * @var CollectionFactory
     */
    protected $productCollection;

    /**
     * @var StockItemRepository
     */
    protected  $stockItemRepository;

    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @param CollectionFactory $productCollectionFactory
     * @param StockItemRepository $stockItemRepository
     * @param Image $imageHelper
     */
    public function __construct(
        CollectionFactory   $productCollectionFactory,
        StockItemRepository $stockItemRepository,
        Image               $imageHelper
    ) {
        $this->productCollection = $productCollectionFactory;
        $this->stockItemRepository = $stockItemRepository;
        $this->imageHelper = $imageHelper;
    }

    /**
     * create and return product collection
     * @return Collection
     */
    protected function getProducts(): Collection
    {
        return $this->productCollection->create();
    }

    /**
     * queries products by price range
     * @param $min
     * @param $max
     * @param bool $sortDesc
     * @param int $rowsToSelect
     * @return array
     */
    public function productsByPriceRange($min, $max, bool $sortDesc = false, int $rowsToSelect = 10): array
    {
        $productsFound = [];

        // get active products within range from collection
        $products = $this->getProducts()
            ->addAttributeToSelect("*")
            ->addFieldToFilter("price", array("from" => $min, "to"=> $max))
            ->addAttributeToFilter('status', Status::STATUS_ENABLED)
            ->setOrder("price", $sortDesc ? "DESC" : "ASC")
            ->setPageSize($rowsToSelect);

        // build return products
        foreach ($products as $product) {
            $id = $product->getId();
            $thumbnail = $this->getProductThumbnail($product);

            array_push($productsFound, [
                "thumbnail" => $thumbnail ?? "",
                "sku" => $product->getSku() ?? "-",
                "name" => $product->getName() ?? "-",
                "qty" => number_format($this->getProductStock($id), 2),
                "price" => $product->getPrice() ? number_format($product->getPrice(), 2): "-",
                "linkToProduct" => $product->getProductUrl() ?? "-"
            ]);
        }

        return $productsFound;
    }

    /**
     * get stock qty by product id
     * @param int $productId
     * @return float|null
     */
    protected function getProductStock(int $productId): ?float
    {
        try {
            return $this->stockItemRepository->get($productId)->getQty();
        } catch (NoSuchEntityException $e) {
            return 0;
        }
    }

    /**
     * @param $product
     * @return string
     */
    protected function getProductThumbnail ($product): string
    {
        return $this->imageHelper->init($product, 'cart_page_product_thumbnail')
            ->setImageFile($product->getThumbnailImage())
            ->getUrl();
    }

}
