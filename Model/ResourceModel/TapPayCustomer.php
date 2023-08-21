<?php
/**
 * File is used for TapPay module in Magento 2
 * devlogicsol TapPay
 *
 * @category TapPay
 * @package  devlogicsol
 */
namespace devlogicsol\TapPay\Model\ResourceModel;

/**
 * Class TapPayCustomer 
 *
 * @package devlogicsol\TapPay\Model\ResourceModel
 */
class TapPayCustomer extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function _construct()
    {
        $this->_init('devlogicsol_tappay_customer', 'id');
    }
}
