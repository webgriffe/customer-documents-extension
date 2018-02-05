<?php

class Webgriffe_CustomerDocuments_Model_Observer
{
    public function sendMailAfterDocumentCreation(Varien_Event_Observer $event)
    {
        $document = $event->getData('data_object');
        if (!$document instanceof Webgriffe_CustomerDocuments_Model_Document) {
            return;
        }

        if (!$document->isObjectNew()) {
            return;
        }

        // TODO send new document transactional email
    }
}
