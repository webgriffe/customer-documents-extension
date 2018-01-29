<?php

class Webgriffe_CustomerDocuments_Block_List extends Mage_Core_Block_Template
{
    /**
     * @return Mage_Eav_Model_Entity_Collection_Abstract|Webgriffe_CustomerDocuments_Model_Resource_Document_Collection
     */
    public function getDocuments()
    {
        return Mage::getModel('webgriffe_customerdocuments/document')
            ->getCollection()
            ->addFieldToFilter(
                'customer_id',
                Mage::getSingleton('customer/session')->getCustomerId()
            )
            ->setOrder('created_at', 'DESC')
        ;
    }
}
