<?php
class AMG_SecurePay_Model_Directpost extends Mage_Payment_Model_Method_Cc
{
	protected $_code  		  			= 'securepay_directpost';
    protected $_formBlockType			= 'securepay/directpost_form';
	
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
    	$isVailable = parent::isAvailable($quote);
		if($isVailable){
			return $this->_apiModel->isPaymentMethodAvailable();
		}
		return false;
    }
	
	public function getConfigData($field, $storeId = null)
    {
    	if($field!='payment_action')
			return parent::getConfigData($field, $storeId);
		
		$path = 'payment/'.$this->getCode().'/'.$field;
		$value = Mage::getStoreConfig($path, $storeId);
		if($value==AMG_SecurePay_Model_Directpost_Source_PaymentAction::SALE){
			$value = AMG_SecurePay_Model_Directpost_Source_PaymentAction::AUTHORIZE_CAPTURE;	
		}
        return $value;
    }
	
	protected function initializeApiModel(Varien_Object $payment,$amount)
	{
		$this->_apiModel->setPayment($payment);
		$this->_apiModel->setAmount($amount);
	}
	
	protected function _shouldDoSale()
	{
		if(Mage::getStoreConfig('payment/'.$this->getCode().'/payment_action')==AMG_SecurePay_Model_Directpost_Source_PaymentAction::SALE){
			return true;
		}
		return false;
	}
	
	protected function sale(Varien_Object $payment, $amount)
	{
		if($amount <= 0){
			Mage::throwException(Mage::helper('securepay')->__('Invalid amount.'));
        }
		
		$this->initializeApiModel($payment,$amount);
			
		$saleRequest = $this->_apiModel->getSaleRequest();	
		
		$saleResponse = $this->_apiModel->_postApiRequest($saleRequest);
	
		if($saleResponse['response'] == 1){
			$this->updatePaymentWithApiResponse($payment,$saleResponse); 
    	}
    	else{
    		Mage::throwException($saleResponse['responsetext']);
        }
	}
	
	public function authorize(Varien_Object $payment, $amount)
	{
		if($amount <= 0){
			Mage::throwException(Mage::helper('securepay')->__('Invalid amount to authorize.'));
        }
			
		$this->initializeApiModel($payment,$amount);
		 
		$authorizeRequest = $this->_apiModel->getAuthorizationRequest();	
		
		$authorizationResponse = $this->_apiModel->_postApiRequest($authorizeRequest);
	
		if($authorizationResponse['response'] == 1){
			$this->updatePaymentWithApiResponse($payment,$authorizationResponse); 
    	}
    	else{
    		Mage::throwException($authorizationResponse['responsetext']);
        }
		return $authorizationResponse;
	}
	
	public function capture(Varien_Object $payment, $amount)
    {
		if($this->_shouldDoSale()){
			$this->sale($payment, $amount);
			return;
		}
		
		$this->initializeApiModel($payment,$amount);
		
		$transactionId = '';
		
		$authorizationTransaction = $payment->getAuthorizationTransaction();
		if($authorizationTransaction){
			if($authorizationTransaction->getTxnId()){
				$transactionId = $authorizationTransaction->getTxnId();
			}
		}
		if($transactionId==''){
			$authorizationResponse = $this->authorize($payment, $amount);	
			if($authorizationResponse['response'] == 1 && $authorizationResponse['transactionid']){
				$transactionId = $authorizationResponse['transactionid'];
			}
		}
		if($transactionId!=''){
			$captureRequest = $this->_apiModel->getCaptureRequest($transactionId);
			$captureResponse = $this->_apiModel->_postApiRequest($captureRequest);
			if($captureResponse['response'] == 1){
				$this->updatePaymentWithApiResponse($payment,$captureResponse);
			}
			else{
				Mage::throwException($captureResponse['responsetext']);
        	}
		}
		else{
			Mage::throwException(Mage::helper('securepay')->__('Amount is not authorized.'));
		}	
    }
	
	protected function updatePaymentWithApiResponse(Varien_Object $payment,$response, $transactionClosed = 0)
	{
		$payment->setTransactionId($response['transactionid']);
		if(isset($response['customer_vault_id'])){
			$payment->setAdditionalInformation('amg_customer_vault_id', $response['customer_vault_id']);
		}
		$payment->setIsTransactionClosed($transactionClosed);
		$payment->setTransactionAdditionalInfo(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS,$response);	
	}
	
	public function refund(Varien_Object $payment, $amount)
    {
		if($amount <= 0){
			Mage::throwException(Mage::helper('securepay')->__('Invalid amount to refund.'));
        }
		
		$this->initializeApiModel($payment,$amount);
			
		$refundRequest = $this->_apiModel->getRefundRequest();
		
		$refundResponse = $this->_apiModel->_postApiRequest($refundRequest);
		if($refundResponse['response'] == 1){
			$transactionClosed = 1;	
			$this->updatePaymentWithApiResponse($payment,$refundResponse,$transactionClosed);
		}
		else{
			Mage::throwException($captureResponse['responsetext']);
        }
		return $this;
    }
}
