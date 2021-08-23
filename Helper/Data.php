<?php
namespace Esparksinc\GuestToCustomer\Helper;
use Magento\Sales\Model\OrderFactory;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_scopeConfig;
    protected $orderFactory;
    public function __construct(
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->_scopeConfig = $scopeConfig; 
        $this->orderFactory = $orderFactory;       
        parent::__construct($context);
    }

    public function isEnabled() : bool
    {
        return (bool) $this->scopeConfig->getValue('myemail/general/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    public function isRegsiterCustomer($incId) 
    {
        $order=$this->orderFactory->create()->loadByIncrementId($incId);
        return $order->getCustomerId();
    }

}