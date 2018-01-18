<?php

class Webgriffe_CustomerDocuments_Model_Invoice_Api extends Mage_Api_Model_Resource_Abstract
{
    public function create($invoiceData)
    {
        $invoice = Mage::getModel('webgriffe_customerdocuments/invoice');
        $invoice->setData($invoiceData);
        $invoice->save();
        return $invoice->getData();
    }
}
