<?php

class Webgriffe_CustomerDocuments_Model_Document_Api extends Mage_Api_Model_Resource_Abstract
{
    const ORDER_INCREMENT_ID_KEY = '_order_increment_id';
    const PDF_BLOB_KEY = 'pdf';
    const FILENAME_KEY = 'filename';
    const ORDER_ID_KEY = 'order_id';
    const CUSTOMER_ID_KEY = 'customer_id';

    /**
     * @param string $orderIncrementId
     * @param array $documentData
     * @return array
     * @throws Mage_Api_Exception
     * @throws Exception
     */
    public function createForOrder($orderIncrementId, $documentData)
    {
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        if (!$order->getId()) {
            $this->_fault('fault-404', sprintf('Order increment ID "%s" does not exists.', $orderIncrementId));
        }
        if (!$order->getCustomerId()) {
            $this->_fault(
                'fault-400',
                sprintf(
                    'Order "%s" does not belong to a customer so is not possible to create a customer document.',
                    $orderIncrementId
                )
            );
        }

        /** @var Webgriffe_CustomerDocuments_Model_Document $document */
        $document = Mage::getModel('webgriffe_customerdocuments/document');
        $document->addData($documentData);
        $document->setCustomerId($order->getCustomerId());
        $document->setOrderId($order->getId());
        $document->save();
        $document->unsetData(Webgriffe_CustomerDocuments_Model_Document::FILE_DATA_KEY);
        return $document->getData();
    }
}
