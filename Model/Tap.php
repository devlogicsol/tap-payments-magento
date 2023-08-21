<?php

namespace devlogicsol\TapPay\Model;

use devlogicsol\TapPay\Helper\Data as DataHelper;
use devlogicsol\TapPay\Controller\Standard;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote;

class Tap extends \Magento\Payment\Model\Method\AbstractMethod
{
    const CODE = 'tap';
    protected $_code = self::CODE;
    protected $_isGateway = true;
    protected $_isOffline = false;
    protected $_canRefund = true;
    protected $_canCapture = true;
    protected $_canAuthorize = true;
    protected $helper;
    protected $_minAmount = null;
    protected $_maxAmount = null;
    protected $_supportedCurrencyCodes = array('PKR', 'KWD');
    protected $urlBuilder;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $tapLogger;

    /**
     * @var Quote
     */
    private $quote;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\UrlInterface $urlBuilder,
        \devlogicsol\TapPay\Helper\Data $helper,
        \Magento\Sales\Model\Order $order,
        \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory,
        \Magento\Sales\Model\Order\Invoice $invoice,
        \Magento\Sales\Model\Service\CreditmemoService $creditmemoService,
        \Magento\Framework\App\Request\Http $request,
        \Psr\Log\LoggerInterface $customLogger,
        Quote $quote
    ) {
        $this->helper = $helper;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger
        );

        $this->_minAmount = "0.100";
        $this->_maxAmount = "1000000";
        $this->urlBuilder = $urlBuilder;
        $this->order = $order;
        $this->creditmemoFactory = $creditmemoFactory;
        $this->creditmemoService = $creditmemoService;
        $this->invoice = $invoice;
        $this->request = $request;
        $this->tapLogger = $customLogger;
        $this->quote = $quote;
    }
    //$active_sk = $this->getConfigData('test_secret_key');

    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount){
        $mode = $this->getConfigData('debug');
        if ($mode) {
            $active_sk = $this->getConfigData('test_secret_key');
        }
        else {
            $active_sk = $this->getConfigData('live_secret_key');
        }

        $order_id = $this->request->getParam('order_id');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->create('Magento\Sales\Model\Order')->load($order_id );
        $currency_code = $order->getOrderCurrencyCode();
        $transactionId = $payment->getParentTransactionId();
        $refund_request = [];
        $refund_request['charge_id']    =   $transactionId;
        $refund_request['amount']       =   $amount;
        $refund_request['currency']     =   $currency_code;
        $refund_request['description']  =   '';
        $refund_request['reason']       =   'Requested by customer';
        $refund_request['reference']['merchant']    =   'txn_0001';
        $refund_request['metadata']['udf1']         =   'test1';
        $refund_request['metadata']['udf1']         =   'test2'; 
        $refund_request['post']['url']              =   $this->getConfigData('post_url');

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.tap.company/v2/refunds",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($refund_request),
            CURLOPT_HTTPHEADER => array(
                                    "authorization: Bearer ".$active_sk,
                                    "content-type: application/json"
                                ),
        ));


        $response = curl_exec($curl);
        $obj = json_decode($response);
        $transactionId = $obj->id;
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            echo $response;
        }
        
    
            $payment
            ->setTransactionId($transactionId . '-' . \Magento\Sales\Model\Order\Payment\Transaction::TYPE_REFUND)
            ->setParentTransactionId($transactionId)
            ->setIsTransactionClosed(1)
            ->setShouldCloseParentTransaction(1);
            return $this;
    }


    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if ($quote && (
                $quote->getBaseGrandTotal() < $this->_minAmount
                || ($this->_maxAmount && $quote->getBaseGrandTotal() > $this->_maxAmount))
        ) {
            return false;
        }

        return parent::isAvailable($quote);
    }

    public function deleteCustomerCard($custId, $cardId){
        $mode = $this->getConfigData('debug');
        if ($mode) {
            $active_sk = $this->getConfigData('test_secret_key');
        } else {
            $active_sk = $this->getConfigData('live_secret_key');
        }
        $cardList = [];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.tap.company/v2/card/$custId/$cardId",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "DELETE",
            CURLOPT_HTTPHEADER => array(
                "authorization: Bearer $active_sk"
            ),
        ));

        $response = curl_exec($curl);
        // var_dump($response);exit;
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $response = json_decode($response);
            if (isset($response->delete) && $response->delete == true) {
                return $response->id;
            }
        }
        return false;
    }

    public function fetchCustomerCards($custId){
        $mode = $this->getConfigData('debug');
        if ($mode) {
            $active_sk = $this->getConfigData('test_secret_key');
        } else {
            $active_sk = $this->getConfigData('live_secret_key');
        }
        $cardList = [];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.tap.company/v2/card/$custId?limit=50&starting_after=0",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "authorization: Bearer $active_sk"
            ),
        ));

        $response = curl_exec($curl);
        // var_dump($response);exit;
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $response = json_decode($response);
            if (isset($response->object) && $response->object == "list" && isset($response->data) && $response->data) {
                $cardList = $response->data;
            }
        }
        return $cardList;
    }

    public function getTokenBySavedCard($source_id){
        // exit($source_id);
        $mode = $this->getConfigData('debug');
        if ($mode) {
            $active_sk = $this->getConfigData('test_secret_key');
        } else {
            $active_sk = $this->getConfigData('live_secret_key');
        }
        $trans_object = [];

        $request_url = "https://api.tap.company/v2/tokens";

        $trans_object["saved_card"]["card_id"] = $source_id;
        $trans_object["saved_card"]["customer_id"] = $this->helper->getTapCustomerId();
        $trans_object["client_ip"] = 'xxx.xxx.xxx.xx';

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $request_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($trans_object),
            CURLOPT_HTTPHEADER => array(
                "authorization: Bearer ".$active_sk,
                "content-type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $response = json_decode($response);
        // print_r($response);exit;

        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
        }
        //var_dump();exit;
        if (isset($response->id)) {
            return $response->id;
        } else {
            return 'bad request';
        }
    }


    public function redirectMode($order,$source_id){
        $this->tapLogger->debug('Preparing fn -> redirectMode()');
        $mode = $this->getConfigData('debug');
        if ($mode) {
            $active_sk = $this->getConfigData('test_secret_key');
        } else {
            $active_sk = $this->getConfigData('live_secret_key');
        }
        $ui_mode = $this->getConfigData('ui_mode');
        $transaction_mode = $this->getConfigData('transaction_mode');
        $amount = $order->getGrandTotal();
        $currencyCode = $order->getOrderCurrencyCode();  
        $orderid = $order->getEntityId();
        $orderIncrementId = $order->getIncrementId();
        $CstFName = $order->getBillingAddress()->getFirstName();
        $CstLName = $order->getBillingAddress()->getLastName();
        $CstEmail = $order->getCustomerEmail();
        $CstMobile = $order->getBillingAddress()->getTelephone();
        $post_url = $this->getConfigData('post_url');
        $redirectUrl = $this->urlBuilder->getUrl('tap/Standard/Response');
        $trans_object = [];
    }
}
