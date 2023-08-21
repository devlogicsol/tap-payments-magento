<?php
namespace devlogicsol\TapPay\Model\Config\Source;

class TransactionMode implements \Magento\Framework\Option\ArrayInterface
{
 	public function toOptionArray()
 	{
  		return [
    		['value' => 'authorize', 'label' => __('Authorize')],
    		['value' => 'capture', 'label' => __('Capture')],
    		
  		];
 	}
}