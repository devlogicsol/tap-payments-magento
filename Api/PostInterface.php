<?php
namespace devlogicsol\TapPay\Api;
interface PostInterface
{   
    /**
     * get_token
     *
     * @api
     * @param string|null $paymentMethodId
     *
     * @return mixed
     */
    
    public function get_token($paymentMethodId = null);

    /**
     * GET for Post api
     * @param string $value
     * @return string
     */
 
    public function getPost();
}