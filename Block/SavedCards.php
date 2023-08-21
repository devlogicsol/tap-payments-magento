<?php
/**
 * File is used for Order Approval module in Magento 2
 * Devlogisol TapPay 
 *
 * @category TapPay
 * @package  Devlogisol
 */
namespace devlogicsol\TapPay\Block;

use Magento\Sales\Model\Order;
use devlogicsol\TapPay\Helper\Data as TapHelper;

/**
 * Class SavedCards 
 *
 * @package devlogicsol\TapPay\Block
 */
class SavedCards extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \devlogicsol\TapPay\Model\TapPayCustomer
     */
    public $tapPayCustomer;
    /**
     * @var \Magento\Sales\Model\Order $order
     */
    public $order;

    /**
     * @var \Magento\Customer\Model\Session
     */
    public $session;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;
    protected $_tapHelper;
    protected $_tapModel;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $session,
        Order $order,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \devlogicsol\TapPay\Model\TapPayCustomer $tapPayCustomer,
        TapHelper $tapHelper,
        \devlogicsol\TapPay\Model\Tap $tapModel,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        parent::__construct($context);
        $this->order = $order;
        $this->_session = $session;
        $this->storeManager = $storeManager;
        $this->tapPayCustomer = $tapPayCustomer;
        $this->_tapModel = $tapModel;
        $this->_tapHelper = $tapHelper;
    }

    /**
     * Method to get Approval Details
     */
    public function getAllTapSavedCards()
    {   
        $page = ($this->getRequest()->getParam('p'))? $this->getRequest()->getParam('p') : 1;
        $formatedCards = [];
        $customerCards = $this->_tapModel->fetchCustomerCards($this->_tapHelper->getTapCustomerId());
        $formatedCards = json_encode($this->_tapHelper->formatSavedCards($customerCards));
        $mainArr = [];
        if ($formatedCards) {
           $formatedCards = json_decode($formatedCards, true);
        }
        return $formatedCards;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        return $this;
    }
}
