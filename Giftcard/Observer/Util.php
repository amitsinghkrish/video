<?php

namespace Magedelight\Giftcard\Observer;

use Magento\Framework\Event\ObserverInterface;

class Util implements ObserverInterface
{
    /**
     * Core store config.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var storeManager
     */
    private $store;

    /**
     * @var \Magento\Framework\Url\ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var \Magento\Framework\Url\ScopeResolverInterface
     */
    private $context;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\Store $store,
        \Magento\Framework\Url\ScopeResolverInterface $scopeResolver,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\HTTP\Client\Curl $curl
    ){
        $this->blockFactory = $blockFactory;
        $this->scopeConfig = $scopeConfig;
        $this->store = $store;
        $this->scopeResolver = $scopeResolver;
        $this->messageManager = $context->getMessageManager();
        $this->urlBuilder = $context->getUrl();
        $this->curl = $curl;
        $this->request = $context->getRequest();
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $event = $observer->getEvent()->getName();
        $errorMsg = $this->checkModuleActivation();
        if (!empty($errorMsg)) {
            foreach ($errorMsg as $msg) {
                $this->messageManager->addError($msg);
            }
        
            if ($this->request->getServer('SERVER_NAME') != 'localhost'
                    && $this->request->getServer('SERVER_ADDR') != '127.0.0.1') {
                $keys['serial_key'] = $this->scopeConfig
                        ->getValue(
                            'magedelight/license/serial_key',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                        );
                $keys['activation_key'] = $this->scopeConfig
                        ->getValue(
                            'magedelight/license/activation_key',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                        );
                $url = $this->urlBuilder->getCurrentUrl();
                // @codingStandardsIgnoreStart
                $parsedUrl = parse_url($url);
                 // @codingStandardsIgnoreEnd
                $keys['host'] = $parsedUrl['host'];
                $keys['ip'] = $this->request->getServer('SERVER_ADDR');
                $keys['product_name'] = 'Giftcard Module M2';
                $field_string = http_build_query($keys);
                try {
                    $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, 0);
                    $this->curl->setOption(CURLOPT_FOLLOWLOCATION, 1);
                    $this->curl->post('http://www.magedelight.com/ktplsys/?'.$field_string, []);
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                }
            }
        }
    }

    private function checkModuleActivation()
    {
        $messages = [];
        $serial = $this->scopeConfig
                ->getValue('magedelight/license/serial_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $activation = $this->scopeConfig
                ->getValue('magedelight/license/activation_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($this->request->getServer('SERVER_NAME') != 'localhost' &&
                $this->request->getServer('SERVER_ADDR') != '127.0.0.1') {
            if ($serial == '') {
                $messages[] = __("Serial key not found.Please enter valid serial key for 'Giftcard' extension.");
            }
            if ($activation == '') {
                $messages[] = __("Activation key not found.Please enter valid activation key for"
                        . " 'Giftcard' extension.");
            }
            $isValidActivation = $this->validateActivationKey($activation, $serial);
            if (!empty($isValidActivation)) {
                $messages[] = $isValidActivation[0];
            }
        }

        return $messages;
    }
    // @codingStandardsIgnoreStart
    private function validateActivationKey($activation, $serial)
    {
        $url = $this->urlBuilder->getCurrentUrl();
        $parsedUrl = parse_url($url);
        
        // Remove wwww., http:// or https:// from url.
        $domain = str_replace(['www.', 'http://', 'https://'], '', $parsedUrl['host']);
        $hash = $serial.''.$domain;
        $message = [];
        if (md5($hash) != $activation) {
            $devPart = strchr($domain, '.', true);
            $origPart = str_replace($devPart.'.', '', $domain);
            $hash2 = $serial.''.$origPart;
            if (md5($hash2) != $activation) {
                $message[] = "Activation key invalid of 'Giftcard' extension for this url.";
            }
        }

        return $message;
    }
        // @codingStandardsIgnoreEnd
}
