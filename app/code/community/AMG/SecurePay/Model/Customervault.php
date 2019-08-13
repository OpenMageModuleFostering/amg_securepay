<?php
class AMG_SecurePay_Model_Customervault extends Mage_Core_Model_Abstract 
{	
	public function __construct() 
	{
		$this->_init('securepay/customervault');
	}
	
	public function loadByCreditCard($customerId, $ccType, $ccLast4, $ccExpMonth, $ccExpYear)
    {
    	if($customerId && $ccType && $ccLast4 && $ccExpMonth && $ccExpYear){
			$customerVaultId = $this->_getResource()->getCustomerVaultIdByCreditCard($customerId, $ccType, $ccLast4, $ccExpMonth, $ccExpYear);
			if($customerVaultId>0){
				$this->load($customerVaultId);
			}
		}
		return $this;
	}
	
	public function loadByCode($code)
    {
    	if($code){
			$customerVaultId = $this->_getResource()->getCustomerVaultIdByCode($code);
			if($customerVaultId>0){
				$this->load($customerVaultId);
			}
		}
		return $this;
	}
	
	public function generateUniqueCode()
	{
		return $this->_getResource()->generateUniqueCode();
	}	

	protected function _beforeSave()
	{
		parent::_beforeSave();
		if(!$this->getId()){
			$this->_dataSaveAllowed = false;
			
			$customerId = $this->getData('customer_id');
			$code = $this->getData('code');
			$ccType = $this->getData('cc_type');
			$ccLast4 = $this->getData('cc_last4');
			$ccExpMonth = $this->getData('cc_exp_month');
			$ccExpYear = $this->getData('cc_exp_year');
			$amgCustomerVaultId = $this->getData('amg_customer_vault_id');
			
			if($customerId && $code && $ccType && $ccLast4 && $ccExpMonth && $ccExpYear && $amgCustomerVaultId){
				$customerVaultId = $this->_getResource()->getCustomerVaultIdByCreditCard($customerId, $ccType, $ccLast4, $ccExpMonth, $ccExpYear);
				if($customerVaultId==0){
					$customerVaultId = $this->_getResource()->getCustomerVaultIdByCode($code);
					if($customerVaultId==0)
						$this->_dataSaveAllowed = true;	
				}
			}
			
		}
		return $this;
	}
}