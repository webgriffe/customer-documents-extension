<?php
 
class Webgriffe_CustomerDocuments_Model_Resource_Invoice extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('webgriffe_customerdocuments/invoice', 'invoice_id');
    }

}
