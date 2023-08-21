<?php

namespace devlogicsol\TapPay\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Framework\UrlInterface as UrlInterface;
use devlogicsol\TapPay\Helper\Data as TapHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;

class TapConfigProvider implements ConfigProviderInterface
{
    protected $methodCode = "tap";

    protected $method;
    
    protected $urlBuilder;
    protected $checkoutSession;
    protected $_tapModel;
    protected $_tapHelper;


    public function __construct(
        PaymentHelper $paymentHelper, 
        UrlInterface $urlBuilder,
        TapHelper $tapHelper,
        \devlogicsol\TapPay\Model\Tap $tapModel,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->method = $paymentHelper->getMethodInstance($this->methodCode);
        $this->urlBuilder = $urlBuilder;
        $this->_checkoutSession = $checkoutSession;
        $this->_tapModel = $tapModel;
        $this->_tapHelper = $tapHelper;
    }

    public function getConfig()
    {
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->get('Magento\Sales\Model\Order');
        $current_order = $this->_checkoutSession->getLastRealOrder();
        $orderId = $current_order->getEntityId();

        $customerCards = $this->_tapModel->fetchCustomerCards($this->_tapHelper->getTapCustomerId());
        $formatedCards = $this->_tapHelper->formatSavedCards($customerCards);

        $test_public_key = $this->method->getConfigData('test_public_key');
        $live_public_key = $this->method->getConfigData('live_public_key');
        $post_url = $this->method->getConfigData('post_url');
        $mode = $this->method->getConfigData('debug');
        $uimode = $this->method->getConfigData('ui_mode');
        $knet = $this->method->getConfigData('knet');
        $benefit = $this->method->getConfigData('benefit');
        if ($mode) {
            $active_pk = $test_public_key;
        }
        else {
            $active_pk = $live_public_key;
        }
        $transaction_mode = $this->method->getConfigData('transaction_mode');

       

        return $this->method->isAvailable() ? [
            'payment' => [
                'tap' => [
                    'responseUrl' => $this->urlBuilder->getUrl('tap/Standard/Response', ['_secure' => true]),
                    'redirectUrl' => $this->urlBuilder->getUrl('tap/Standard/Redirect'),
                    'active_pk' => $active_pk,
                    'transaction_mode' => $transaction_mode,
                    'saved_cards' => $formatedCards,
                    'post_url' => $post_url,
                    'uimode' => $uimode,
                    'knet'  => $knet,
                    'benefit' => $benefit,
                    'orderId' => $orderId+1
                ]
            ],
         
        ] : [];
    }

}
