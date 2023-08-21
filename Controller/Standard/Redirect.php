<?php

namespace devlogicsol\TapPay\Controller\Standard;

class Redirect extends \devlogicsol\TapPay\Controller\Tap
{
    public function execute()
    {
        $this->writeLog();
		$popup = false;
        if (isset($_GET['token'])) {
			//$amount = $_GET['amount'];
            $source_id = $_GET['token'];
        }
        else if (isset($_GET['knet'])) {
            $source_id = 'src_kw.knet';
        }
        else if (isset($_GET['benefit'])) {
            $source_id = 'src_bh.benefit';
        }
        else if(isset($_GET['redirect'])){
            $source_id = 'src_all';
        }
        else {
            $source_id = 'src_all';
            $popup = true;
        }
        $order = $this->getOrder();
        $orderId = $order->getIncrementId();
        if ($source_id == 'src_all' && $popup == true) {
            $data = $this->getTapModel()->redirectMode($order,$source_id);
            $result = $this->jsonResultFactory->create();
            $result->setData($data);
            return $result;
        }

        $order = $this->getOrder();
		$orderId = $order->getIncrementId(); 

        if ($order->getBillingAddress())
        {
            $charge_url = $this->getTapModel()->redirectMode($order,$source_id);
            if ($charge_url == 'bad request') {
                $quote = $this->getQuote();
                $this->getCheckoutSession()->restoreQuote($quote);
                $quote->setIsActive(true);
                $order->cancel();
                $order->save();
                $url = $this->getTapHelper()->getUrl('checkout/cart');
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setUrl($url);
                $this->messageManager->addError(__("Transaction Failed."." Please check payment method and currency"));
                $this->logger->debug('BAD REQUEST');
                return $resultRedirect;
            }
            $this->addOrderHistory($order,'<br/>The customer was redirected to Tap Payments.');
            
        }
        // return change redirect funciton
    }
    

    // implement change redirect function herr

}
