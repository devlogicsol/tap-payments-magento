<?php

namespace devlogicsol\TapPay\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends AbstractHelper
{
    protected $icons = [];
    protected $themeModel = null;
    protected $_urlInterface;
    protected $tapPayCustomer;
    protected $customerSession;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Design\Theme\ThemeProviderInterface $themeProvider,
        \devlogicsol\TapPay\Model\TapPayCustomer $tapPayCustomer,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\UrlInterface $urlInterface
    ) {
        $this->request = $request;
        $this->assetRepo = $assetRepo;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->themeProvider = $themeProvider;
        $this->tapPayCustomer = $tapPayCustomer;
        $this->customerSession = $customerSession;
        $this->_urlInterface = $urlInterface;
    }

    public function getUrl($route, $params = []) 
    {
        return $this->_urlInterface->getUrl($route, $params);
    }

    public function getConfiguration($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getTapCustomerId()
    {
        //@todo Write logic to fetch custId dynamically.
        $customerId = $this->customerSession->getCustomer()->getId();
        if ($customerId) {
            return $this->tapPayCustomer->load($customerId, 'customer_id')->getTapCustomerId();
        }

        return "";
    }

    public function getCardIcon($brand)
    {
        $icons = $this->getPaymentMethodIcons();

        if (isset($icons[$brand]))
            return $icons[$brand];

        return $icons['generic'];
    }

    public function getCardLabel($card, $hideLastFour = false)
    {
        if (!empty($card->last_four) && !$hideLastFour)
            return __("•••• %1", $card->last_four);

        if (!empty($card->brand))
            return $this->getCardName($card->brand);

        return __("Card");
    }

    protected function getCardName($brand)
    {
        switch ($brand) {
            case 'VISA': return "Visa";
            case 'AMEX': return "American Express";
            case 'MASTERCARD': return "MasterCard";
            case 'MADA': return "Mada";
            case null:
            case "":
                return "Card";
            default:
                return ucfirst($brand);
        }
    }
    public function getPaymentMethodIcons()
    {
        if (!empty($this->icons))
            return $this->icons;

        return $this->icons = [
            // Cards
            'AMEX' => $this->getViewFileUrl("devlogicsol_TapPay::images/cards/amex.svg"),
            'MASTERCARD' => $this->getViewFileUrl("devlogicsol_TapPay::images/cards/mastercard.svg"),
            'generic' => $this->getViewFileUrl("devlogicsol_TapPay::images/cards/generic.svg"),
            'VISA' => $this->getViewFileUrl("devlogicsol_TapPay::images/cards/visa.svg")
        ];
    }
    

    public function formatSavedCards($cardList)
    {
        $savedCards = [];
        foreach ($cardList as $card)
        {
            $key = $card->fingerprint;
            $savedCards[$key] = [
                "label" => $this->getCardLabel($card),
                "value" => $card->id,
                "icon" => $this->getCardIcon($card->brand)
            ];
        }

        return $savedCards;
    }

    protected function getViewFileUrl($fileId)
    {
        try
        {
            $params = [
                '_secure' => $this->request->isSecure(),
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'themeModel' => $this->getThemeModel()
            ];
            return $this->assetRepo->getUrlWithParams($fileId, $params);
        }
        catch (LocalizedException $e)
        {
            return null;
        }
    }


    protected function getThemeModel()
    {
        if ($this->themeModel)
            return $this->themeModel;

        $themeId = $this->scopeConfig->getValue(
            \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );

        /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
        $this->themeModel = $this->themeProvider->getThemeById($themeId);

        return $this->themeModel;
    }

}
