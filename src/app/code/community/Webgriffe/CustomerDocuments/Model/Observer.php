<?php

class Webgriffe_CustomerDocuments_Model_Observer
{
    const XML_PATH_NEW_DOCUMENT_EMAIL_TEMPLATE = 'customer/documents/new_document_email_template';
    const XML_PATH_NEW_DOCUMENT_EMAIL_COPY_TO = 'customer/documents/new_document_email_copy_to';
    const XML_PATH_NEW_DOCUMENT_EMAIL_SENDER = 'customer/documents/new_document_email_sender';
    const CAN_SEND_EMAIL_KEY = 'can_send_email';

    public function sendMailAfterDocumentCreation(Varien_Event_Observer $event)
    {
        $document = $event->getData('data_object');
        if (!$document instanceof Webgriffe_CustomerDocuments_Model_Document) {
            return;
        }

        if (!$document->isObjectNew()) {
            return;
        }

        $document->setData(self::CAN_SEND_EMAIL_KEY, true);
        Mage::dispatchEvent('webgriffe_customerdocuments_can_send_notification_email', ['document' => $document]);
        $canSend = $document->getData(self::CAN_SEND_EMAIL_KEY);
        if (!$canSend) {
            return;
        }

        try {
            if (!$this->sendEmail($document)) {
                Mage::log(
                    sprintf(
                        'Unable to send new document email for document "%s". Unknown reason.',
                        $document->getId()
                    )
                );
            }
        } catch (Exception $e) {
            Mage::log(
                sprintf(
                    'Unable to send new document email for document "%s". Error: %s',
                    $document->getId(),
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * @param Webgriffe_CustomerDocuments_Model_Document $document
     * @return bool
     * @throws Exception
     */
    protected function sendEmail(Webgriffe_CustomerDocuments_Model_Document $document)
    {
        $customer = $document->getCustomer();
        $storeId = $customer->getStore()->getId();

        // Get the destination email addresses to send copies to
        $copyTo = $this->getEmails(self::XML_PATH_NEW_DOCUMENT_EMAIL_COPY_TO);
        $templateId = Mage::getStoreConfig(self::XML_PATH_NEW_DOCUMENT_EMAIL_TEMPLATE, $storeId);
        $customerName = $customer->getName();

        $mailTemplate = Mage::getModel('core/email_template');
        /* @var $mailTemplate Mage_Core_Model_Email_Template */
        $mailTemplate->setDesignConfig(array('area' => 'frontend'));
        $this->addPdfAttachment($mailTemplate->getMail(), $document->getAbsoluteFilepath());
        foreach ($copyTo as $email) {
            $mailTemplate->addBcc($email);
        }
        $mailTemplate->sendTransactional(
            $templateId,
            Mage::getStoreConfig(self::XML_PATH_NEW_DOCUMENT_EMAIL_SENDER, $storeId),
            $customer->getEmail(),
            $customerName,
            array(
                'user' => $customer,
                'document' => $document,
            ),
            $storeId
        );
        return (bool)$mailTemplate->getSentSuccess();
    }

    protected function getEmails($configPath)
    {
        $data = Mage::getStoreConfig($configPath);
        if (!empty($data)) {
            return explode(',', $data);
        }
        return [];
    }

    /**
     * @param Zend_Mail $mail
     * @param $filePath
     * @return Zend_Mail
     * @throws Zend_Mail_Exception
     */
    protected function addPdfAttachment(Zend_Mail $mail, $filePath)
    {
        $mail->setType(Zend_Mime::MULTIPART_MIXED);

        // @codingStandardsIgnoreStart
        if (file_exists($filePath)) {
            $mail->createAttachment(
                file_get_contents($filePath),
                'application/pdf',
                Zend_Mime::DISPOSITION_ATTACHMENT,
                Zend_Mime::ENCODING_BASE64,
                basename($filePath)
            );
        }
        // @codingStandardsIgnoreEnd
        return $mail;
    }
}
