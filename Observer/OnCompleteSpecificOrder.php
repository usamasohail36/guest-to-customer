<?php
namespace Esparksinc\GuestToCustomer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Esparksinc\GuestToCustomer\Helper\Email;
use Esparksinc\GuestToCustomer\Helper\Data;

class OnCompleteSpecificOrder implements ObserverInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $scopeConfig;
    public $_storeManager;
    protected $customerRepository;
    private $helperEmail;
    private $helperData;

    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        Email $helperEmail,
        Data $helperData,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->helperEmail = $helperEmail;
        $this->helperData = $helperData;
        $this->scopeConfig = $scopeConfig;
        $this->_storeManager=$storeManager;
        $this->customerRepository = $customerRepository;
    }
  /**
   * customer register event handler
   *
   * @param \Magento\Framework\Event\Observer $observer
   * @return void
   */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if($this->helperData->isEnabled()){
            $configPath='myemail/general/sender_email';
            $myemail=$this->scopeConfig->getValue($configPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $order = $observer->getEvent()->getOrder();
            $orderId = $order->getId();
            $email = $order->getCustomerEmail();
            $customerId = $order->getCustomerId();
            $basePath=$this->_storeManager->getStore()->getBaseUrl();
            $registerationPath=$basePath.'register/index/customer?id=';
            $myInfo=', Click on the following link to Register Your Account  '.$registerationPath.$orderId;
            $myurl='Dear '.$order->getCustomerFirstname()   .' '.$order->getCustomerLastname().$myInfo;
            $check=1;
            try {
                $customer = $this->customerRepository->get($email);
                $customerId = $customer->getId();
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                $check=0;
            }
            
            if ($customerId==null && $check==0) {
                return $this->helperEmail->sendEmail($myemail, $myurl, $email);
            }
        }
    }

}
