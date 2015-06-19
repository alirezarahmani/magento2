<?php
/**
 * Created by PhpStorm.
 * User: alireza
 * Date: 6/19/15
 * Time: 5:55 PM
 */

namespace Magento\Promotions\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}