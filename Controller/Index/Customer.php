<?php
namespace Esparksinc\GuestToCustomer\Controller\Index;

use Magento\Framework\App\RequestFactory; 
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Api\AccountManagementInterface;
use Esparksinc\GuestToCustomer\Helper\Email;

class Customer extends \Magento\Framework\App\Action\Action
{

    protected $requestFactory;
    protected $mathRandom;
    protected $orderRepository;
    protected $_resultPageFactory;
    protected $_customer;
    protected $_storemanager;
    protected $customerSession;
    protected $customerExtractor;
    protected $scopeConfig;
    protected $customerAccountManagement;
    private $helperEmail; 

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\CustomerFactory $customer,
    \Magento\Store\Model\StoreManagerInterface $storemanager,
        \Magento\Customer\Model\Session $customerSession,
        Email $helperEmail,
        RequestFactory $requestFactory,
        CustomerExtractor $customerExtractor,
        \Magento\Framework\Math\Random $mathRandom,
        AccountManagementInterface $customerAccountManagement,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_customer = $customer;
    $this->_storemanager = $storemanager;
        $this->customerSession = $customerSession;
        $this->customerExtractor = $customerExtractor;
        $this->helperEmail = $helperEmail;
        $this->requestFactory = $requestFactory;
        $this->mathRandom = $mathRandom;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->orderRepository = $orderRepository;
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Customer login form page
     *
     * @return Redirect|Page
     */
    public function execute()
    {
        $abc=$this->getRequest()->getParams()['id'];
        $configPath='myemail/general/sender_email';
        $myemail=$this->scopeConfig->getValue($configPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $orderq = $this->orderRepository->get($abc);
        $myfirstname=$orderq->getCustomerFirstname();
        $mylastname=$orderq->getCustomerLastname();
        $email = $orderq->getCustomerEmail();
        $customerData = [
            'firstname' => $myfirstname,
            'lastname' => $mylastname,
            'email' => $email,
        ];
        $password = $this->generatePassword();
        $request = $this->requestFactory->create();
        $request->setParams($customerData);

        try {
            $customer = $this->customerExtractor->extract('customer_account_create', $request);
            $customer = $this->customerAccountManagement->createAccount($customer, $password);
            $ouremail=$email;
            $myurl='Your Account Successfully register Your Email id is '.$email. ' And Your Password is '.$password;
            $this->helperEmail->sendEmail($myemail, $myurl, $email);
            $websiteID = $this->_storemanager->getStore()->getWebsiteId();
            $customer = $this->_customer->create()->setWebsiteId($websiteID)->loadByEmail($email);
            $this->customerSession->setCustomerAsLoggedIn($customer);
            $this->messageManager->addSuccess("Your Account Register Successfully ,Account Registeration Details Sent To Your Email Address");
            return $this->_redirect("customer/account/");
        } catch (\Exception $e) {
            return $this->_redirect("customer/account/");
        }
    }

    public function generatePassword($length = 10)
    {
        $chars = \Magento\Framework\Math\Random::CHARS_LOWERS
            . \Magento\Framework\Math\Random::CHARS_UPPERS
            . \Magento\Framework\Math\Random::CHARS_DIGITS;

        return $password = $this->mathRandom->getRandomString($length, $chars);
    }
}
