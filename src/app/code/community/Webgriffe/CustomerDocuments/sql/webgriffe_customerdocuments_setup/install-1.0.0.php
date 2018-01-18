<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('webgriffe_customerdocuments/invoice'))
    ->addColumn(
        'invoice_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ),
        'Invoice Id'
    )
    ->addColumn(
        'customer_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array('nullable' => false),
        'Customer Id'
    )
    ->addColumn(
        'order_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'unsigned' => true,
            'nullable' => true,
            'default' => null,
        ),
        'Order Id'
    )
    ->addColumn(
        'external_id',
        Varien_Db_Ddl_Table::TYPE_TEXT,
        255,
        array('nullable' => true),
        'Increment Id'
    )
    ->addColumn(
        'filename',
        Varien_Db_Ddl_Table::TYPE_TEXT,
        255,
        array('nullable' => false),
        'Filename'
    )
    ->addColumn(
        'pdf',
        Varien_Db_Ddl_Table::TYPE_BLOB,
        '64M',
        array('nullable' => false),
        'Pdf Blob'
    )
    ->addColumn(
        'created_at',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        array('nullable' => false),
        'Created At'
    )
    ->addColumn(
        'updated_at',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        array('nullable' => false),
        'Updated At'
    )
    ->addForeignKey(
        $installer->getFkName('webgriffe_customerdocuments/invoice', 'customer_id', 'customer/entity', 'entity_id'),
        'customer_id',
        $installer->getTable('customer/entity'),
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('webgriffe_customerdocuments/invoice', 'order_id', 'sales/order', 'entity_id'),
        'order_id',
        $installer->getTable('sales/order'),
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Webgriffe Customer Documents Invoices')
;

$installer->getConnection()->createTable($table);


$installer->endSetup();
