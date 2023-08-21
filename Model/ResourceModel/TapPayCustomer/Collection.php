<?php
/**
 * File is used for TapPay module in Magento 2
 * devlogicsol TapPay
 *
 * @category TapPay
 * @package  devlogicsol
 */
namespace devlogicsol\TapPay\Model\ResourceModel\TapPayCustomer;

/**
 * Class Collection 
 *
 * @package devlogicsol\TapPay\Model\ResourceModel\TapPayCustomer
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    public $_idFieldName = 'id';
    public $eventPrefix = 'devlogicsol_tappay_customer';
    public $eventObject = 'devlogicsol_tappay_customer';

    /**
     * Define resource model
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            'devlogicsol\TapPay\Model\TapPayCustomer',
            'devlogicsol\TapPay\Model\ResourceModel\TapPayCustomer'
        );
    }
}
