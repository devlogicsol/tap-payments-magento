<?php

namespace devlogicsol\TapPay\Controller;

// use Amasty\RecurringPayments\Api\Data\TransactionInterface;
use Amasty\RecurringPayments\Api\Generators\RecurringTransactionGeneratorInterface;

abstract class Tap extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Tap\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $_quote = false;

    protected $_tapModel;

    protected $_tapHelper;
	
	protected $_orderHistoryFactory;

    // protected $paymentTokenFactory;

    protected $customer;
	protected $customerFactory;

    protected $customerRepository;

    /**
     * @var RecurringTransactionGeneratorInterface
     */
    private $recurringTransactionGenerator;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \devlogicsol\TapPay\Model\tap $twotapModel
     * @param \devlogicsol\TapPay\Helper\tap $tapHelper
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
		\Magento\Sales\Model\Order\Status\HistoryFactory $orderHistoryFactory,
        \devlogicsol\TapPay\Model\Tap $tapModel,
        \devlogicsol\TapPay\Helper\Data $tapHelper,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \Magento\Customer\Model\Customer $customer,
		\Magento\Customer\Model\ResourceModel\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        RecurringTransactionGeneratorInterface $recurringTransactionGenerator,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->logger = $logger;
		$this->_orderHistoryFactory = $orderHistoryFactory;
        $this->_tapModel = $tapModel;
        $this->_tapHelper = $tapHelper;	
        $this->_invoiceService = $invoiceService;
        $this->transaction        = $transaction;
        $this->transactionBuilder        = $transactionBuilder;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->customer = $customer;
		$this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        //$this->paymentTokenFactory = $paymentTokenFactory;
        $this->recurringTransactionGenerator = $recurringTransactionGenerator;
        $this->eventManager = $eventManager;
        parent::__construct($context);
    }

    /**
     * Write the logs to the devlogicsol logger.
     */
    protected function writeLog()
    {
        $calledClass = get_called_class();
        $this->logger->debug('- BEGIN: ' . $calledClass);
        if (method_exists($this->getRequest(), 'getPostValue')) {
            $this->logger->debug('-- PostValue --');
            $this->logger->debug(print_r($this->getRequest()->getPostValue(), true));
        }
        $this->logger->debug('-- Params --');
        $this->logger->debug(print_r($this->getRequest()->getParams(), true));
        $this->logger->debug('- END: ' . $calledClass);
    }

     
    protected function _cancelPayment($errorMsg = '')
    {
        $gotoSection = false;
        $this->_tapHelper->cancelCurrentOrder($errorMsg);
        if ($this->_checkoutSession->restoreQuote()) {
            //Redirect to payment step
            $gotoSection = 'paymentMethod';
        }

        return $gotoSection;
    }

    /**
     * Get order object
     *
     * @return \Magento\Sales\Model\Order
     */
    protected function getOrderById($order_id)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->get('Magento\Sales\Model\Order');
        $order_info = $order->loadByIncrementId($order_id);
        return $order_info;
    }

    protected function refund() {
        exit;
    }
    /**
     * Get order object
     *
     * @return \Magento\Sales\Model\Order
     */
    protected function getOrder()
    {
        return $this->_orderFactory->create()->loadByIncrementId(
            $this->_checkoutSession->getLastRealOrderId()
        );
    }

    /**
     * @param $event
     * @param int $status
     * @return TransactionInterface
     */

	protected function addOrderHistory($order,$comment){
		$history = $this->_orderHistoryFactory->create()
			->setComment($comment)
            ->setEntityName('order')
            ->setOrder($order);
			$history->save();
		return true;
	}
	
    protected function getQuote()
    {
        if (!$this->_quote) {
            $this->_quote = $this->getCheckoutSession()->getQuote();
        }
        return $this->_quote;
    }

    protected function getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

    protected function getCustomerSession()
    {
        return $this->_customerSession;
    }

    protected function getTapModel()
    {
        return $this->_tapModel;
    }

    protected function getTapHelper()
    {
        return $this->_tapHelper;
    }
}
