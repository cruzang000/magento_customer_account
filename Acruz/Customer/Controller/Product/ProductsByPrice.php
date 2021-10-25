<?php

namespace Acruz\Customer\Controller\Product;

use Acruz\Customer\Model\ProductsSearch;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;

class ProductsByPrice implements HttpGetActionInterface {

    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var ProductsSearch
     */
    protected $productsSearch;
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @param RequestInterface $request
     * @param ProductsSearch $productsSearch
     * @param JsonFactory $resultJsonFactory
     * constructor
     */
    public function __construct(
        RequestInterface $request,
        ProductsSearch   $productsSearch,
        JsonFactory      $resultJsonFactory
    ) {
        $this->productsSearch = $productsSearch;
        $this->request = $request;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * handles get requests for product searches
     * returns response
     * @inheritdoc
     */
    public function execute() {
        // get params
        $params = $this->request->getParams();

        // create response and validate params
        $response = [
            "errors" => $this->hasErrors($params),
            "products" => null
        ];

        // if params are valid query products
        if (empty($response["errors"])) {
            $min = $params["lowRange"];
            $max = $params["highRange"];
            $sortDesc = isset($params["sort"]) && $params["sort"] == "descending";

            $response["products"] = $this->productsSearch->productsByPriceRange($min, $max, $sortDesc);
        }

        return $this->resultJsonFactory->create()->setData($response);
    }

    /**
     * checks params have errors
     * @param $params
     * @return string
     */
    protected function hasErrors($params): string
    {
        $error = "";

        //low range
        $invalidLowRange = !isset($params["lowRange"]) || !is_numeric($params["lowRange"]) || $params["lowRange"] < 0;

        // high range
        $maxLimit = $invalidLowRange || $params["lowRange"] == 0 ? 5 : $params["lowRange"] * 5;
        $invalidMaxRange = empty($params["highRange"]) || !is_numeric($params["highRange"])
            || $params["highRange"] <= $params["lowRange"] || $params["highRange"] > $maxLimit;

        // set errors if invalid
        if ($invalidLowRange) {
            $error = "Low Price must be any positive number.";
        } else if ($invalidMaxRange) {
            $error = "Max Price must be greater than min price and no more than 5x the min.";
        }

        return $error;
    }
}
