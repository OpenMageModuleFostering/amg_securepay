<?php
class AMG_SecurePay_Model_Mysql4_Customervault extends Mage_Core_Model_Mysql4_Abstract 
{	
	public function _construct() 
	{
		$this->_init('securepay/customervault','customervault_id');
	}
	
	public function getCustomerVaultIdByCreditCard($customerId, $ccType, $ccLast4, $ccExpMonth, $ccExpYear)
	{
		if($customerId && $ccType && $ccLast4 && $ccExpMonth && $ccExpYear){
			$select = $this->_getReadAdapter()->select()->reset();
			$select->from($this->getMainTable(), array('customervault_id'))
            	->where('customer_id =? ',$customerId)
            	->where('cc_type =? ',$ccType)
            	->where('cc_last4 =? ', $ccLast4)
            	->where('cc_exp_month =? ',$ccExpMonth)
            	->where('cc_exp_year =? ',$ccExpYear);
			$rows = $this->_getReadAdapter()->fetchAll($select);	
			if(count($rows)>0)
				return $rows[0]['customervault_id'];			
		}
		return 0;
	}
	
	public function getCustomerVaultIdByCode($code)
	{
		if($code){
			$select = $this->_getReadAdapter()->select()->reset();
			$select->from($this->getMainTable(), array('customervault_id'))
            	->where('code =? ',$code);	
			$rows = $this->_getReadAdapter()->fetchAll($select);	
			if(count($rows)>0)
				return $rows[0]['customervault_id'];			
		}
		return 0;
	}
	
	public function generateUniqueCode()
	{
		for($i=0;$i<50;$i++){
			$code = Mage::helper('core')->getRandomString(10);
			$select = $this->_getReadAdapter()->select()->reset()
							->from(array('main_table'=>$this->getMainTable()),array('noofrd'=>'COUNT(*)'))
							->where('main_table.code = ?', $code);
			$row = $this->_getReadAdapter()->fetchRow($select);	
			if($row['noofrd']==0)
				return $code;
		}
		return '';
	}
}