<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$conn = $installer->getConnection();
$conn->addColumn(
    $installer->getTable('webgriffe_customerdocuments/document'),
    'document_date',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        'nullable' => false,
        'after' => 'external_id',
        'comment' => 'Document generation date',
    )
);

$installer->endSetup();
