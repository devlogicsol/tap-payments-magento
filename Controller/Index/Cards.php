<?php
/**
 * File is used for Order Approval module in Magento 2
 * Devlogisol TapPay 
 *
 * @category TapPay
 * @package  Devlogisol
 */
namespace devlogicsol\TapPay\Controller\Index;

/**
 * Class Cards 
 *
 * @package devlogicsol\TapPay\Controller\Index
 */
class Cards extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory)
    {
        $this->_pageFactory = $pageFactory;
        return parent::__construct($context);
    }

    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->_pageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('My Cards'));

        $block = $resultPage->getLayout()->getBlock('customer.account.link.back');
        if ($block) {
            $block->setRefererUrl($this->_redirect->getRefererUrl());
        }

        return $resultPage;
    }
}
