<?php

/**
 * Class Webgriffe_CustomerDocuments_Model_Document
 *
 * @method int getDocumentId()
 * @method Webgriffe_CustomerDocuments_Model_Document setDocumentId(int $id)
 * @method int getCustomerId()
 * @method Webgriffe_CustomerDocuments_Model_Document setCustomerId(int $id)
 * @method int getOrderId()
 * @method Webgriffe_CustomerDocuments_Model_Document setOrderId(int $id)
 * @method string getExternalId()
 * @method Webgriffe_CustomerDocuments_Model_Document setExternalId(string $id)
 * @method string getType()
 * @method Webgriffe_CustomerDocuments_Model_Document setType(string $type)
 * @method string getFilepath()
 * @method Webgriffe_CustomerDocuments_Model_Document setFilepath(string $filepath)
 * @method string getCreatedAt()
 * @method Webgriffe_CustomerDocuments_Model_Document setCreatedAt(string $time)
 * @method string getUpdatedAt()
 * @method Webgriffe_CustomerDocuments_Model_Document setUpdatedAt(string $time)
 */
class Webgriffe_CustomerDocuments_Model_Document extends Mage_Core_Model_Abstract
{
    const BASE_DIR_NAME = 'customer_document';
    const FILE_DATA_KEY = '_file_data';

    /**
     * @deprecated These constants should not be statically defined here
     */
    const TYPE_INVOICE = 'invoice';

    /**
     * @deprecated These constants should not be statically defined here
     */
    const TYPE_DELIVERY_NOTE = 'delivery_note';

    /**
     * @var Mage_Customer_Model_Customer
     */
    protected $customer = null;

    protected function _construct()
    {
        $this->_init('webgriffe_customerdocuments/document');
    }

    /**
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        if (!$this->customer || !$this->customer->getId()) {
            $this->customer =  Mage::getModel('customer/customer')->load($this->getCustomerId());
        }
        return $this->customer;
    }

    public function getFileContent()
    {
        return file_get_contents($this->getAbsoluteFilepath());
    }

    public function getFileName()
    {
        return basename($this->getAbsoluteFilepath());
    }

    public function getAbsoluteFilepath()
    {
        return $this->getDocumentBasePath() . DS . $this->getFilepath();
    }

    /**
     * @return string
     */
    protected function getDocumentBasePath()
    {
        return rtrim(Mage::getBaseDir('var'), DS) . DS . self::BASE_DIR_NAME;
    }

    /**
     * @return Mage_Core_Model_Abstract
     * @throws \RuntimeException
     */
    protected function _beforeSave()
    {
        if (!$this->getType()) {
            throw new \RuntimeException('Please specify a type for the document.');
        }

        $this->setUpdatedAt(Mage::getSingleton('core/date')->gmtDate());
        if ($this->isObjectNew() && null === $this->getCreatedAt()) {
            $this->setCreatedAt(Mage::getSingleton('core/date')->gmtDate());
        }

        if (array_key_exists(self::FILE_DATA_KEY, $this->_data)) {
            $fileData = $this->_data[self::FILE_DATA_KEY];
            $this->handleFileData($fileData);
        }

        return parent::_beforeSave();
    }

    /**
     * @param array $fileData
     * @throws \RuntimeException
     */
    protected function handleFileData(array $fileData)
    {
        if (!array_key_exists('binary', $fileData) || !array_key_exists('filename', $fileData)) {
            throw new \RuntimeException('Invalid document file data.');
        }
        $binary = base64_decode($fileData['binary']);
        $filename = $fileData['filename'];
        $destinationFilename = date('YmdHis') . '_' . $filename;
        $filepath = $this->getCustomerId() . DS . $destinationFilename;
        $fullDestinationPath = $this->getDocumentBasePath() . DS . $filepath;

        if (!is_dir(dirname($fullDestinationPath))) {
            if (!mkdir(dirname($fullDestinationPath), 0777, true) && !is_dir(dirname($fullDestinationPath))) {
                throw new \RuntimeException(
                    sprintf('Cannot create document directory at path "%s".', dirname($fullDestinationPath))
                );
            }
        }

        if (!file_put_contents($fullDestinationPath, $binary)) {
            throw new \RuntimeException(
                sprintf('Cannot save document file to disk at path "%s"', $fullDestinationPath)
            );
        }

        $this->setFilepath($filepath);
    }
}
