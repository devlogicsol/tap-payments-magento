<?php
/**
 * File is used for TapPay module in Magento 2
 * devlogicsol TapPay
 *
 * @category TapPay
 * @package  devlogicsol
 */
namespace devlogicsol\TapPay\Model;

/**
 * Class TapPayCustomer 
 * @package devlogicsol\TapPay\Model
 */
class TapPayCustomer extends \Magento\Framework\Model\AbstractModel implements
    \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'devlogicsol_tappay_customer';

    public $cacheTag = 'devlogicsol_tappay_customer';

    public $eventPrefix = 'devlogicsol_tappay_customer';

    public function _construct()
    {
        $this->_init('devlogicsol\TapPay\Model\ResourceModel\TapPayCustomer');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues()
    {
        $values = [];
        return $values;
    }
}
