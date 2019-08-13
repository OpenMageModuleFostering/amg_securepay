<?php
class AMG_SecurePay_Model_Api extends Mage_Core_Model_Abstract
{
 	protected $_helper;
	protected $_payment;
	protected $_order;
	protected $_amount;
		
 	public function _construct()
	{
		parent::_construct();
		$this->_helper = Mage::helper('securepay');
	}
	
	public function isPaymentMethodAvailable()
	{
		return $this->_helper->isPaymentMethodAvailable();
	}
	
	public function setPayment(Varien_Object $payment)
	{
		if($payment){
			$this->_payment = $payment;
			$order = $payment->getOrder();
			if($order && $order->getId()){
				$this->_order = $order;
			}
		}
	}
	
	public function setAmount($amount)
	{
		$this->_amount = $amount;
	}
	
	protected function getBillingAddress()
	{
		$billingAddressArr['firstname'] 	= '';
		$billingAddressArr['lastname'] 		= '';
		$billingAddressArr['company'] 		= '';
		$billingAddressArr['address1'] 		= '';
		$billingAddressArr['address2'] 		= '';
		$billingAddressArr['city'] 			= '';
		$billingAddressArr['state'] 		= '';
		$billingAddressArr['zip'] 			= '';
		$billingAddressArr['country'] 		= '';
		$billingAddressArr['phone'] 		= '';
		$billingAddressArr['fax'] 			= '';
		$billingAddressArr['email'] 		= '';

		if($this->_order){
			$billingAddress = $this->_order->getBillingAddress();
			if($billingAddress && $billingAddress->getId()){
				$billingAddressArr['firstname'] 	= $billingAddress->getData('firstname');
				$billingAddressArr['lastname'] 		= $billingAddress->getData('lastname');
				$billingAddressArr['company'] 		= $billingAddress->getData('company');
				$billingAddressArr['address1'] 		= $billingAddress->getStreet1();
				$billingAddressArr['address2'] 		= $billingAddress->getStreet2();
				$billingAddressArr['city'] 			= $billingAddress->getData('city');
				$billingAddressArr['state'] 		= $billingAddress->getData('region');
				$billingAddressArr['zip'] 			= $billingAddress->getData('postcode');
				$billingAddressArr['country'] 		= $billingAddress->getData('country_id');
				$billingAddressArr['phone'] 		= $billingAddress->getData('telephone');
				$billingAddressArr['fax'] 			= $billingAddress->getData('fax');
				$billingAddressArr['email'] 		= $billingAddress->getData('email');
			}	
		}
		
		return $billingAddressArr;
	}

	protected function getShippingAddress()
	{
		$shippingAddressArr['shipping_firstname'] 		= '';
		$shippingAddressArr['shipping_lastname'] 		= '';
		$shippingAddressArr['shipping_company'] 		= '';
		$shippingAddressArr['shipping_address1'] 		= '';
		$shippingAddressArr['shipping_address2'] 		= '';
		$shippingAddressArr['shipping_city'] 			= '';
		$shippingAddressArr['shipping_state'] 			= '';
		$shippingAddressArr['shipping_zip'] 			= '';
		$shippingAddressArr['shipping_country'] 		= '';
		$shippingAddressArr['shipping_phone'] 			= '';
		$shippingAddressArr['shipping_fax'] 			= '';
		$shippingAddressArr['shipping_email'] 			= '';

		if($this->_order){
			if(!$this->_order->getIsVirtual()){
				$shippingAddress = $this->_order->getShippingAddress();
			}
			else{
				$shippingAddress = $this->_order->getBillingAddress();
			}
			
			if($shippingAddress && $shippingAddress->getId()){
				$shippingAddressArr['shipping_firstname'] 		= $shippingAddress->getData('firstname');
				$shippingAddressArr['shipping_lastname'] 		= $shippingAddress->getData('lastname');
				$shippingAddressArr['shipping_company'] 		= $shippingAddress->getData('company');
				$shippingAddressArr['shipping_address1'] 		= $shippingAddress->getStreet1();
				$shippingAddressArr['shipping_address2'] 		= $shippingAddress->getStreet2();
				$shippingAddressArr['shipping_city'] 			= $shippingAddress->getData('city');
				$shippingAddressArr['shipping_state'] 			= $shippingAddress->getData('region');
				$shippingAddressArr['shipping_zip'] 			= $shippingAddress->getData('postcode');
				$shippingAddressArr['shipping_country'] 		= $shippingAddress->getData('country_id');
				$shippingAddressArr['shipping_phone'] 			= $shippingAddress->getData('telephone');
				$shippingAddressArr['shipping_fax'] 			= $shippingAddress->getData('fax');
				$shippingAddressArr['shipping_email'] 			= $shippingAddress->getData('email');
			}	
		}
		
		return $shippingAddressArr;
	}

	protected function getCreditCardData()
	{
		$creditCardArr['ccnumber'] 	= '';
		$creditCardArr['ccexp'] 	= '';
		$creditCardArr['cvv']		= '';
		
		if($this->_payment){
			$creditCardArr['ccnumber'] 	= $this->_payment->getCcNumber();
			$creditCardArr['ccexp'] 	= $this->_payment->getCcExpMonth().'-'.$this->_payment->getCcExpYear();
			if($this->_helper->getUseCcv())
				$creditCardArr['cvv'] 	= $this->_payment->getCcCid();
		}
		
		return $creditCardArr;
	}
	
	protected function getApiCredentials()
	{
		$apiCredentialsArr['username'] = $this->_helper->getAmgApiId();
		$apiCredentialsArr['password'] = $this->_helper->getAmgTransKey();
		
		return $apiCredentialsArr;
	}
	
	protected function getOrderAmounts()
	{
		$orderAmounts['shipping'] 			=	 0;
		$orderAmounts['discount_amount']	=	 0;
		$orderAmounts['tax'] 				=	 0;
		
		if($this->_payment && $this->_order){	
			$orderAmounts['shipping'] 			=	 $this->_order->getShippingAmount();
			$orderAmounts['discount_amount']	=	 $this->_order->getDiscountAmount();
			$orderAmounts['tax'] 				=	 $this->_order->getTaxAmount();
		}
		
		return $orderAmounts;
	}
	
	protected function getApiCommonFields()
	{
		$commonFieldsArr['amount'] 					= '';
		$commonFieldsArr['ipaddress'] 				= '';
		$commonFieldsArr['orderid'] 				= '';
		$commonFieldsArr['merchant_ref_number'] 	= '';
		$commonFieldsArr['currency'] 				= '';
		$commonFieldsArr['payment'] 				= $this->_helper->getApiPaymentType();
		
		if($this->_helper->getDuplicateTransSecondsFeatureEnabled())
			$commonFieldsArr['dup_seconds'] 			= $this->_helper->getDuplicateTransSeconds();
		
		if($this->_payment && $this->_order){
			$commonFieldsArr['amount'] 					= $this->_amount;
			$commonFieldsArr['ipaddress'] 				= $_SERVER['REMOTE_ADDR'];
			$commonFieldsArr['orderid'] 				= $this->_order->getIncrementId();
			$commonFieldsArr['merchant_ref_number'] 	= $this->_order->getIncrementId();
			$commonFieldsArr['currency'] 				= $this->_order->getBaseCurrencyCode();
		}
		
		return $commonFieldsArr;
	}
	
	protected function getApiCustomerValutFields()
	{
			
		$customerVault = $this->getCustomerVaultIfApplicable();
		if($customerVault){
			$customerValutFieldsArr['customer_vault'] 			= $this->_helper->getAddCustomerVaultType();	
			if($customerVault->getAmgCustomerVaultId()){
				$customerValutFieldsArr['customer_vault'] 		= $this->_helper->getUpdateCustomerVaultType();
				$customerValutFieldsArr['customer_vault_id'] 	= $customerVault->getAmgCustomerVaultId();
				$customerValutFieldsArr['customer_id'] 			= $customerVault->getAmgCustomerVaultId();
			}
			return $customerValutFieldsArr;
		}
		return array();
	}
	
	public function getAuthorizationRequest()
	{
		$billingAddressArr 		= $this->getBillingAddress();
		$shippingAddressArr 	= $this->getShippingAddress();
		$creditCardArr 			= $this->getCreditCardData();
		$apiCredentialsArr 		= $this->getApiCredentials();
		$orderAmounts			= $this->getOrderAmounts();
		$commonFieldsArr 		= $this->getApiCommonFields();
		$customerValutFieldsArr = $this->getApiCustomerValutFields();
		
		$authorizeRequest = array_merge($billingAddressArr,$shippingAddressArr,$creditCardArr,$apiCredentialsArr,$orderAmounts,$commonFieldsArr,$customerValutFieldsArr);
		
		$authorizeRequest['type'] = $this->_helper->getApiAuthorizationType();
		
		return $authorizeRequest;
	}
	
	public function getSaleRequest()
	{
		$saleRequest = $this->getAuthorizationRequest();
		
		$saleRequest['type'] = $this->_helper->getApiSaleType();
		
		return $saleRequest;
	}
	
	
	public function getCustomerVaultAuthorizationRequest($amgCustomerVaultId)
	{
		$apiCredentialsArr 		= $this->getApiCredentials();
			
		$commonFieldsArr['amount'] 				= $this->_amount;
		$commonFieldsArr['customer_vault_id'] 	= $amgCustomerVaultId;
		$commonFieldsArr['orderid'] 			= $this->_order->getIncrementId();
		$commonFieldsArr['merchant_ref_number'] = $this->_order->getIncrementId();
			
		$authorizeRequest = array_merge($apiCredentialsArr,$commonFieldsArr);

		$authorizeRequest['type'] 				= $this->_helper->getApiAuthorizationType();
			
		return $authorizeRequest;
	}
	
	public function getCustomerVaultSaleRequest($amgCustomerVaultId)
	{
		$saleRequest = $this->getCustomerVaultAuthorizationRequest($amgCustomerVaultId);
		
		$saleRequest['type'] = $this->_helper->getApiSaleType();
			
		return $saleRequest;
	}
	
	public function getCaptureRequest($transactionId)
	{
		$apiCredentialsArr = $this->getApiCredentials();
		
		$commonFieldsArr['amount'] 			= $this->_amount;
		$commonFieldsArr['transactionid'] 	= $transactionId;
		$commonFieldsArr['orderid'] 		= $this->_order->getIncrementId();
		
		$captureRequest = array_merge($apiCredentialsArr,$commonFieldsArr);	
		
		$captureRequest['type'] 			= $this->_helper->getApiCaptureType();
		
		return $captureRequest;
	}
	
	public function getRefundRequest()
	{
		$apiCredentialsArr = $this->getApiCredentials();
		
		$commonFieldsArr['amount'] 			= $this->_amount;
		$commonFieldsArr['transactionid'] 	= $this->_payment->getLastTransId();
		
		$refundRequest = array_merge($apiCredentialsArr,$commonFieldsArr);	

		$refundRequest['type'] 				= $this->_helper->getApiRefundType();
		
		return $refundRequest;
	}
	
	protected function getCustomerVaultIfApplicable()
    {
		$paymentMethodCode = $this->_payment->getMethod();
		$directPostPaymentMethodCode = Mage::getModel('securepay/directpost')->getCode();
		if($paymentMethodCode==$directPostPaymentMethodCode){
			if(Mage::helper('securepay')->isCcSaveAllowed()){	
				if($this->_payment->hasAdditionalInformation('cc_save_future_directpost') && ($this->_payment->getAdditionalInformation('cc_save_future_directpost') == 'Y')) {
					$customerVaultModel = Mage::getModel('securepay/customervault');
					$customerVaultModel->loadByCreditCard(
												$this->_payment->getOrder()->getData('customer_id'),
												$this->_payment->getData('cc_type'),
												$this->_payment->getData('cc_last4'),
												$this->_payment->getData('cc_exp_month'),
												$this->_payment->getData('cc_exp_year')
											);
					return $customerVaultModel;					
				}
			}
		}
		return false;
	}	
	
	public function _postApiRequest($requestArr)
	{
		$this->log($requestArr);
		
		$requestString = "";
        foreach($requestArr as $key=>$value) {
     	   $requestString .= $key.'='. urlencode($value).'&';
        }
        $requestString = substr($requestString,0,-1);
		
		$gatewayUrl = $this->_helper->getAmgGatewayUrl();
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$gatewayUrl);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $requestString);
		curl_setopt($ch, CURLOPT_POST, 1);
		if (!($data = curl_exec($ch))) {
			 Mage::throwException(Mage::helper('securepay')->__('Payment Gateway Request Timeout.'));
		}
		curl_close($ch);
		unset($ch);
		
		$responseArr = array();
		$data = explode("&",$data);
		for($i=0;$i<count($data);$i++) {
			$rdata = explode("=",$data[$i]);
			$responseArr[$rdata[0]] = $rdata[1];
		}
		
		$this->log($responseArr);
		
		return $responseArr;
	}

	protected function log($message)
    {
		if($this->_helper->isLogEnabled()){
			$orderNumber = $this->_order->getIncrementId();	
			$file = $orderNumber.'.log';	
	
			if($message && $orderNumber){
				$logDir  = $this->_helper->getLogDir();
				$logFile = $logDir . DS . $file;
				$level  = Zend_Log::DEBUG;
				if(!is_dir($logDir)){
					mkdir($logDir);
					chmod($logDir, 0777);
				}
				if(!file_exists($logFile)){
					file_put_contents($logFile, '');
					chmod($logFile, 0777);
				}
				$currentTime = Mage::getModel('core/date')->date('Y-m-d H:i:s');
				$format = "$currentTime %priorityName% (%priority%) : %message%" . PHP_EOL;
				$formatter = new Zend_Log_Formatter_Simple($format);
				$writerModel = (string)Mage::getConfig()->getNode('global/log/core/writer_model');
				if(!$writerModel){
					$writer = new Zend_Log_Writer_Stream($logFile);
				}
				else{
					$writer = new $writerModel($logFile);
				}
				$writer->setFormatter($formatter);
				$logger = new Zend_Log($writer);
				if(is_array($message) || is_object($message)){
					$message = print_r($message, true);
				}
				$logger->log($message, $level);
			}
		}
	}
}
