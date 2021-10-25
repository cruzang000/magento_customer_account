<?php

namespace Acruz\Customer\Controller\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;

class Index extends Action implements HttpGetActionInterface {

    public function execute() {

        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }

}
