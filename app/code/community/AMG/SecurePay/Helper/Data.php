<?php
class AMG_SecurePay_Helper_Data extends Mage_Core_Helper_Abstract
{
	const TYPE_SALE						= 'sale';
	const TYPE_AUTHORIZATION 			= 'auth';
	const TYPE_CAPTURE					= 'capture';
	const TYPE_REFUND					= 'refund';
	const TYPE_ADD_CUSTOMER_VAULT 		= 'add_customer';
	const TYPE_UPDATE_CUSTOMER_VAULT 	= 'update_customer';
	
	const DEFAULT_DUP_SECONDS			= 0;
	const DEFAULT_AMG_PAYMENT_TYPE		= 'creditcard';
	
	protected $_ccTypes = array();
	
	public function isPaymentMethodAvailable()
	{
		$apiId = trim($this->getAmgApiId());
		$transKey = trim($this->getAmgTransKey());
		$gatewayUrl = trim($this->getAmgGatewayUrl());
		
		if($apiId!='' && $transKey!='' && $gatewayUrl!='')
			return true;
	
		return false;
	}	
	
	public function getAmgApiId()
	{
		return Mage::getStoreConfig('payment/securepay_directpost/api_id');
	}
	
	public function getAmgTransKey()
	{
		return Mage::getStoreConfig('payment/securepay_directpost/trans_key');
	}
	
	public function getAmgGatewayUrl()
	{
		return Mage::getStoreConfig('payment/securepay_directpost/gateway_url');
	}
	
	public function getUseCcv()
	{
		return Mage::getStoreConfigFlag('payment/securepay_directpost/useccv');
	}
	
	public function isLogEnabled()
	{
		return Mage::getStoreConfigFlag('payment/securepay_directpost/log');
	}
	
	public function getDuplicateTransSeconds()
	{
		if(Mage::getStoreConfig('payment/securepay_directpost/dup_seconds'))
			return Mage::getStoreConfig('payment/securepay_directpost/dup_seconds'); 
		return self::DEFAULT_DUP_SECONDS;
	}
	
	public function getDuplicateTransSecondsFeatureEnabled()
	{
		return Mage::getStoreConfigFlag('payment/securepay_directpost/check_dup_seconds');
	}
	
	public function getApiPaymentType()
	{
		return self::DEFAULT_AMG_PAYMENT_TYPE;
	}
	
	public function getApiAuthorizationType()
	{
		return self::TYPE_AUTHORIZATION;
	}
	
	public function getAddCustomerVaultType()
	{
		return self::TYPE_ADD_CUSTOMER_VAULT;
	}
	
	public function getUpdateCustomerVaultType()
	{
		return self::TYPE_UPDATE_CUSTOMER_VAULT;
	}
	
	public function getApiCaptureType()
	{
		return self::TYPE_CAPTURE;
	}
	
	public function getApiRefundType()
	{
		return self::TYPE_REFUND;
	}
	
	public function getApiSaleType()
	{
		return self::TYPE_SALE;
	}
	
	public function getPaymentMethodAdditionalInfoFields()
	{
		return array('cc_save_future_directpost','customervault_id','amg_customer_vault_id');
	}

	public function getLogDir()
	{
		return Mage::getBaseDir().DS.'var'.DS.'log'.DS.'AMG'.DS.'SecurePay';
	}

	protected function _getCcTypes()
    {
        if(empty($this->_ccTypes)){
            $this->_ccTypes = Mage::getSingleton('payment/config')->getCcTypes();
        }

        return $this->_ccTypes;
    }
	
	public function translateCcType($ccTypeShort)
    {
        $ccTypes = $this->_getCcTypes();
        if ( !empty($ccTypes) && $ccTypeShort && isset($ccTypes[$ccTypeShort]) ) {
            return $ccTypes[$ccTypeShort];
        }

        return '';
    }
	
	public function getCurrentQuoteCustomerId()
	{
		if(Mage::getSingleton('admin/session')->isLoggedIn()){
			$quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
			if($quote){
				if($quote->getId()){
					if($quote->getCustomerId()){
						return $quote->getCustomerId();
					}
				}
			}
		}	
		else{
			if(Mage::getSingleton('customer/session')->isLoggedIn()){
				$loggedInCustomerId = Mage::getSingleton('customer/session')->getCustomer()->getId();	
				if($loggedInCustomerId>0)
					return $loggedInCustomerId;
			}
		}
		
		return 0;
	}	
	
	public function isCcSaveAllowed()
    {
		if(Mage::getStoreConfigFlag('payment/securepay_directpost/allowsavecc')){
		    $customerId = $this->getCurrentQuoteCustomerId();	       
			if($customerId>0)
				return true;
		}
		return false;
    }
}

