<?php
 
class Webgriffe_CustomerDocuments_Model_Invoice extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('webgriffe_customerdocuments/invoice');
    }

    protected function _beforeSave()
    {
        $this->setUpdatedAt(Mage::getSingleton('core/date')->gmtDate());
        if ($this->isObjectNew() && null === $this->getCreatedAt()) {
            $this->setCreatedAt(Mage::getSingleton('core/date')->gmtDate());
        }
        return parent::_beforeSave();
    }
}
