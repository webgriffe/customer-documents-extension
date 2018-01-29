<?php

class Webgriffe_CustomerDocuments_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function downloadAction()
    {
        $documentId = $this->getRequest()->getParam('id');
        $document = Mage::getModel('webgriffe_customerdocuments/document')->load($documentId);
        if (!$document->getId()) {
            $this->norouteAction();
            return;
        }
        if ((int)$document->getCustomerId() !== (int)Mage::getSingleton('customer/session')->getCustomerId()) {
            $this->getResponse()->setHeader('HTTP/1.1','403 Forbidden')->setBody('Access denied');
            return;
        }
        $this->_prepareDownloadResponse($document->getFileName(), $document->getFileContent());
    }
}
