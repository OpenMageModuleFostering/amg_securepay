<?php
class AMG_SecurePay_Model_Directpost_Customervault extends AMG_SecurePay_Model_Directpost
{
	protected $_code  		  			= 'securepay_directpost_customervault';
	protected $_formBlockType			= 'securepay/directpost_customervault_form';

    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canRefund               = true;
	
	protected $_apiModel;
		
 	public function __construct()
	{
		$this->_apiModel = Mage::getModel('securepay/api');
	}
	
	public function isAvailable($quote = null)
    {
    	if(Mage::getStoreConfigFlag('payment/securepay_directpost/active') && $this->getConfigData('active')){
    		$isVailable = $this->_apiModel->isPaymentMethodAvailable();
			if($isVailable){
				$customerId = Mage::helper('securepay')->getCurrentQuoteCustomerId();
				if($customerId>0){
					$collection = Mage::getModel('securepay/customervault')->getCollection()
								->addFieldToFilter('customer_id',$customerId);
					if(count($collection)>0)
						return true;			
				}
			}
		}
		return false;
    }
	
	public function validate()
	{
		return true;
	}
	
	public function authorize(Varien_Object $payment, $amount)
	{
		if($amount <= 0){
			Mage::throwException(Mage::helper('securepay')->__('Invalid amount to authorize.'));
        }
			
		$amgCustomerVaultId = $this->getAmgCustomerVaultId($payment);
		if($amgCustomerVaultId <= 0){
			Mage::throwException(Mage::helper('securepay')->__('Could not find customer vault information.'));
        }
	
		$this->initializeApiModel($payment,$amount);
		 
		$authorizeRequest = $this->_apiModel->getCustomerVaultAuthorizationRequest($amgCustomerVaultId);	
		
		$authorizationResponse = $this->_apiModel->_postApiRequest($authorizeRequest);
	
		if($authorizationResponse['response'] == 1){
			$this->updatePaymentWithApiResponse($payment,$authorizationResponse); 
    	}
    	else{
    		Mage::throwException($authorizationResponse['responsetext']);
        }
		return $authorizationResponse;
	}
	
	protected function sale(Varien_Object $payment, $amount)
	{
		if($amount <= 0){
			Mage::throwException(Mage::helper('securepay')->__('Invalid amount.'));
        }
			
		$amgCustomerVaultId = $this->getAmgCustomerVaultId($payment);
		if($amgCustomerVaultId <= 0){
			Mage::throwException(Mage::helper('securepay')->__('Could not find customer vault information.'));
        }
	
		$this->initializeApiModel($payment,$amount);
		 
		$saleRequest = $this->_apiModel->getCustomerVaultSaleRequest($amgCustomerVaultId);	
		
		$saleResponse = $this->_apiModel->_postApiRequest($saleRequest);
	
		if($saleResponse['response'] == 1){
			$this->updatePaymentWithApiResponse($payment,$saleResponse); 
    	}
    	else{
    		Mage::throwException($saleResponse['responsetext']);
        }
	}
	
	protected function getAmgCustomerVaultId($payment)
	{
		$paymentMethod = $payment->getMethod();
		if($paymentMethod==$this->_code){
			if($payment->hasAdditionalInformation('customervault_id')){
				$customervault_id = $payment->getAdditionalInformation('customervault_id');
				if($customervault_id>0){
					$customerVaultModel = Mage::getModel('securepay/customervault');
					$customerVaultModel->load($customervault_id);
					if($customerVaultModel->getId()){
						if($customerVaultModel->getCustomerId()>0){
							if($payment->getOrder()->getData('customer_id')==$customerVaultModel->getCustomerId()){	
								return $customerVaultModel->getAmgCustomerVaultId();
							}
						}
					}
				}
			}
		}
		return 0;
	}	
}
