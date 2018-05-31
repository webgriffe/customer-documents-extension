<?php

class Webgriffe_CustomerDocuments_Block_List extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();

        $documents = Mage::getModel('webgriffe_customerdocuments/document')
            ->getCollection()
            ->addFieldToFilter(
                'customer_id',
                Mage::getSingleton('customer/session')->getCustomerId()
            )
            ->setOrder('created_at', 'DESC');

        $this->setDocuments($documents);
    }
    
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $pager = $this->getLayout()->createBlock('page/html_pager', 'webgriffe.customerdocuments.list.pager')
            ->setCollection($this->getDocuments());
        $this->setChild('pager', $pager);
        $this->getDocuments()->load();

        return $this;
    }
    
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}
