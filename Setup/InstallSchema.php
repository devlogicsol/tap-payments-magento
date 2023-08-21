<?php
/**
 * File is used for TapPay module in Magento 2
 * devlogicsol TapPay
 *
 * @category TapPay MIS171051
 * @package  devlogicsol
 */
namespace devlogicsol\TapPay\Setup;

/**
 * Class InstallSchema 
 *
 * @package devlogicsol\TapPay\Setup
 */
class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

    public function install(
        \Magento\Framework\Setup\SchemaSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {
    
        $contextCheck =$context;
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('devlogicsol_tappay_customer')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('devlogicsol_tappay_customer')
            )
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    10,
                    [
                    'auto_increment' => true,
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true,
                    ],
                    'ID'
                )
                ->addColumn(
                    'customer_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    62,
                    ['nullable' => false],
                    'Customer Id'
                )
                ->addColumn(
                    'tap_customer_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    150,
                    ['nullable' => false],
                    'Tap Customer Id'
                )
                ->setComment('Tap Customer');
            $installer->getConnection()->createTable($table);

            $installer->getConnection()->addIndex(
                $installer->getTable('devlogicsol_tappay_customer'),
                $setup->getIdxName(
                    $installer->getTable('devlogicsol_tappay_customer'),
                    ['tap_customer_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['tap_customer_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            );
        }

        $installer->endSetup();
    }
}
