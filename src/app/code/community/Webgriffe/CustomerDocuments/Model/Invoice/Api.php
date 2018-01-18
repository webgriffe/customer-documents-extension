<?php

class Webgriffe_CustomerDocuments_Model_Invoice_Api extends Mage_Api_Model_Resource_Abstract
{
    const ORDER_INCREMENT_ID_KEY = '_order_increment_id';
    const PDF_BLOB_KEY = 'pdf';
    const FILENAME_KEY = 'filename';
    const ORDER_ID_KEY = 'order_id';
    const CUSTOMER_ID_KEY = 'customer_id';

    /**
     * This API method allows to create an invoice PDF document. Attributes "filename" and "pdf" are required.
     * Moreover a "customer_id" is required but
     *
     * @param $invoiceData
     * @return array|void
     * @throws Exception
     * @throws Mage_Api_Exception
     */
    public function create($invoiceData)
    {
        if (!$this->fieldIsSet($invoiceData, self::PDF_BLOB_KEY)) {
            $this->_fault('fault-400', 'Base64 encoded PDF binary is required.');
        }
        if (!$this->fieldIsSet($invoiceData, self::FILENAME_KEY)) {
            $this->_fault('fault-400', 'Invoice filename is required.');
        }
        if (!$this->fieldIsSet($invoiceData, self::ORDER_INCREMENT_ID_KEY) &&
            !$this->fieldIsSet($invoiceData, self::ORDER_ID_KEY) &&
            !$this->fieldIsSet($invoiceData, self::CUSTOMER_ID_KEY)) {
            $this->_fault(
                'fault-400',
                sprintf(
                    'One of the following invoice attributes is required, none given: %s.',
                    implode(', ', [self::ORDER_INCREMENT_ID_KEY, self::ORDER_ID_KEY, self::CUSTOMER_ID_KEY])
                )
            );
        }

        $invoiceData[self::PDF_BLOB_KEY] = base64_decode($invoiceData[self::PDF_BLOB_KEY]);

        $order = $this->getOrderFromInvoiceData($invoiceData);
        if ($order) {
            $invoiceData['order_id'] = $order->getId();
            if (!$this->fieldIsSet($invoiceData, self::CUSTOMER_ID_KEY)) {
                $invoiceData[self::CUSTOMER_ID_KEY] = $order->getCustomerId();
            }
        }

        $invoice = Mage::getModel('webgriffe_customerdocuments/invoice');
        $invoice->setData($invoiceData);
        $invoice->save();
        return $invoice->getData();
    }

    /**
     * @param $invoiceData
     * @param $field
     * @return bool
     */
    private function fieldIsSet($invoiceData, $field): bool
    {
        return array_key_exists($field, $invoiceData) && $invoiceData[$field];
    }

    /**
     * @param $invoiceData
     * @return null|Mage_Sales_Model_Order
     * @throws Mage_Api_Exception
     */
    private function getOrderFromInvoiceData($invoiceData)
    {
        if (array_key_exists(self::ORDER_INCREMENT_ID_KEY, $invoiceData)) {
            $incrementId = $invoiceData[self::ORDER_INCREMENT_ID_KEY];
            $order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);
            if (!$order->getId()) {
                $this->_fault('fault-404', sprintf('Order increment ID "%s" does not exists.', $incrementId));
            }
            return $order;
        }
        if (array_key_exists(self::ORDER_ID_KEY, $invoiceData)) {
            $orderId = $invoiceData[self::ORDER_ID_KEY];
            $order = Mage::getModel('sales/order')->load($orderId);
            if (!$order->getId()) {
                $this->_fault('fault-404', sprintf('Order ID "%s" does not exists.', $orderId));
            }
            return $order;
        }
        return null;
    }
}
