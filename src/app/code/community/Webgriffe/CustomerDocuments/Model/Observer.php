<?php

class Webgriffe_CustomerDocuments_Model_Observer
{
    const XML_PATH_NEW_DOCUMENT_EMAIL_TEMPLATE      = 'customer/documents/new_document_email_template';
    const XML_PATH_NEW_DOCUMENT_EMAIL_SENDER        = 'customer/documents/new_document_email_sender';
    const XML_PATH_NEW_DOCUMENT_EMAIL_COPY_TO       = 'customer/documents/new_document_email_copy_to';
    const XML_PATH_NEW_DOCUMENT_EMAIL_COPY_METHOD   = 'customer/documents/new_document_email_copy_method';

    const CAN_SEND_EMAIL_EVENT_KEY                  = 'canSend';

    public function sendMailAfterDocumentCreation(Varien_Event_Observer $event)
    {
        $document = $event->getData('data_object');
        if (!$document instanceof Webgriffe_CustomerDocuments_Model_Document) {
            return;
        }

        if (!$document->isObjectNew()) {
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
     * This is public because someone may want to send a document email in more situations other than the creation of
     * the document itself. So this may be called from the outside.
     *
     * @param Webgriffe_CustomerDocuments_Model_Document $document
     *
     * @return bool
     *
     * @throws Exception
     */
    public function sendEmail(Webgriffe_CustomerDocuments_Model_Document $document)
    {
        $customer = $document->getCustomer();
        $storeId = $customer->getStore()->getId();

        // Get the destination email addresses to send copies to
        $copyTo = $this->getEmails(self::XML_PATH_NEW_DOCUMENT_EMAIL_COPY_TO, $storeId);
        $copyMethod = Mage::getStoreConfig(self::XML_PATH_NEW_DOCUMENT_EMAIL_COPY_METHOD, $storeId) ?: 'bcc';
        $templateId = Mage::getStoreConfig(self::XML_PATH_NEW_DOCUMENT_EMAIL_TEMPLATE, $storeId);
        $customerName = $customer->getName();

        /* @var Mage_Core_Model_Email_Template $mailTemplate */
        $mailTemplate = Mage::getModel('core/email_template');

        if ($copyTo && $copyMethod == 'bcc') {
            foreach ($copyTo as $email) {
                $mailTemplate->addBcc($email);
            }
        }

        $dataContainer = new Varien_Object(
            array(
                self::CAN_SEND_EMAIL_EVENT_KEY => true,
                'vars' => array(
                    'user' => $customer,
                    'document' => $document,
                )
            )
        );

        Mage::dispatchEvent('customerdocument_send_email_before',
            array(
                'data'   => $dataContainer,
                'copyTo' => $copyTo
            )
        );

        if (!$dataContainer->getData(self::CAN_SEND_EMAIL_EVENT_KEY)) {
            return true;
        }

        $templateVars = $dataContainer->getData('vars');

        $mailTemplate->setDesignConfig(array('area' => 'frontend', 'store' => $storeId));

        $result = true;
        $result = $result && $this->sendDocumentEmail(
            $document,
            $mailTemplate,
            $templateId,
            $storeId,
            $customer->getEmail(),
            $customerName,
            $templateVars
        );

        // Email copies are sent as separated emails if their copy method is 'copy'
        if ($copyTo && $copyMethod == 'copy') {
            foreach ($copyTo as $to) {
                $result = $result && $this->sendDocumentEmail(
                    $document,
                    $mailTemplate,
                    $templateId,
                    $storeId,
                    $to,
                    $customerName,
                    $templateVars
                );
            }
        }

        return $result;
    }

    /**
     * @param string $configPath
     * @param int $storeId
     * @return array
     */
    protected function getEmails($configPath, $storeId)
    {
        $data = Mage::getStoreConfig($configPath, $storeId);
        if (!empty($data)) {
            return explode(',', $data);
        }
        return [];
    }


    /**
     * @param Webgriffe_CustomerDocuments_Model_Document $document
     * @param Mage_Core_Model_Email_Template $mailer
     * @param int|string $templateId
     * @param int $storeId
     * @param string $recipientEmail
     * @param string $recipientName
     * @param array $templateVars
     *
     * @return bool
     */
    private function sendDocumentEmail(
        Webgriffe_CustomerDocuments_Model_Document $document,
        Mage_Core_Model_Email_Template $mailer,
        $templateId,
        $storeId,
        $recipientEmail,
        $recipientName,
        array $templateVars
    ) {
        if ($document) {
            //Attache document PDF
            $filePath = $document->getAbsoluteFilepath();

            //search for file
            if (file_exists($filePath)) {
                $mailer->getMail()->createAttachment(
                    file_get_contents($filePath),
                    'application/pdf',
                    Zend_Mime::DISPOSITION_ATTACHMENT,
                    Zend_Mime::ENCODING_BASE64,
                    $document->getFilepath()
                );
            }
        }

        $mailer->sendTransactional(
            $templateId,
            Mage::getStoreConfig(self::XML_PATH_NEW_DOCUMENT_EMAIL_SENDER, $storeId),
            $recipientEmail,
            $recipientName,
            $templateVars,
            $storeId
        );

        return (bool)$mailer->getSentSuccess();
    }
}
