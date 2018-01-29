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

    const TYPE_INVOICE = 'invoice';
    const TYPE_DELIVERY_NOTE = 'delivery_note';

    public static function getAllowedTypes()
    {
        return [self::TYPE_INVOICE, self::TYPE_DELIVERY_NOTE];
    }

    /**
     * @return string
     */
    public static function getDocumentBasePath()
    {
        return rtrim(Mage::getBaseDir('var'), DS) . DS . self::BASE_DIR_NAME;
    }

    public function getFileContent()
    {
        return file_get_contents($this->getAbsoluteFilepath());
    }

    public function getFileName()
    {
        return basename($this->getAbsoluteFilepath());
    }

    protected function getAbsoluteFilepath()
    {
        return self::getDocumentBasePath() . DS . $this->getFilepath();
    }

    protected function _construct()
    {
        $this->_init('webgriffe_customerdocuments/document');
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
        if (!in_array($this->getType(), self::getAllowedTypes(), true)) {
            throw new \RuntimeException(
                sprintf('Invalid type "%s". Allowed types are: %s.', $this->getType(), implode(', ', self::getAllowedTypes()))
            );
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
        $fullDestinationPath = self::getDocumentBasePath() . DS . $filepath;
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
