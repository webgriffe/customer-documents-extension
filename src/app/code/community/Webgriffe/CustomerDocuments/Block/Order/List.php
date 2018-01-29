<?php


class Webgriffe_CustomerDocuments_Block_Order_List extends Mage_Core_Block_Template
{
    /**
     * @return Webgriffe_CustomerDocuments_Model_Resource_Document_Collection
     */
    public function getDocuments()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        return Mage::getModel('webgriffe_customerdocuments/document')
            ->getCollection()
            ->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomerId())
            ->addFieldToFilter('order_id', $orderId)
            ->setOrder('created_at', 'DESC')
            ;
    }
}
