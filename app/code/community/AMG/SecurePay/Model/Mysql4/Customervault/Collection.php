<?php
class AMG_SecurePay_Model_Mysql4_Customervault_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	public function _construct()
	{
		parent::_construct();
		$this->_init('securepay/customervault');  
	}
}