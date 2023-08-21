<?php
/**
 * File is used for Order Approval module in Magento 2
 * devlogicsol TapPay 
 *
 * @category TapPay
 * @package  devlogicsol
 */
namespace devlogicsol\TapPay\Controller\Index;

use Magento\Sales\Model\Order;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use devlogicsol\TapPay\Helper\Data as TapHelper;

/**
 * Class Delete 
 *
 * @package devlogicsol\TapPay\Controller\Index
 */
class Delete extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    protected $_tapHelper;
    protected $_tapModel;

    /**
     * @var \Magento\Customer\Model\Session
     */
    public $session;

    /**
     * @var \devlogicsol\TapPay\Model\TapPayCustomer
     */
    public $tapPayCustomer;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $session,
        \devlogicsol\TapPay\Model\TapPayCustomer $tapPayCustomer,
        TapHelper $tapHelper,
        \devlogicsol\TapPay\Model\Tap $tapModel
    ) {
        $this->_session = $session;
        $this->tapPayCustomer = $tapPayCustomer;
        $this->_tapModel = $tapModel;
        $this->_tapHelper = $tapHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->getRequest()->getParam('uid')) {
            $this->messageManager->addError(__('You entered wrong URL.'));
            return $this->resultRedirectFactory->create()->setPath('tap/index/cards');
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $cardId = $this->getRequest()->getParam('uid');
        $customerTokenCol = $this->tapPayCustomer->load($this->_session->getCustomer()->getId(), 'customer_id');

        try {
            $response = $this->_tapModel->deleteCustomerCard($customerTokenCol->getTapCustomerId(), $cardId);
            if ($response) {
                $this->messageManager->addSuccess(__('Card deleted successfully'));
            } else {
                $this->messageManager->addError(__('We can\'t delete your card right now.'));
            }
        } catch (\Exception $e) {
            print_r($e->getMessage());die;
            $this->messageManager->addException($e, __('We can\'t delete your card right now.'));
            return $resultRedirect->setPath('tap/index/cards/');
        }

        return $resultRedirect->setPath('tap/index/cards/');
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
