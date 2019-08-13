<?php
class AMG_SecurePay_Block_Directpost_Customervault_Form extends Mage_Payment_Block_Form_Cc
{
    protected function _construct()
    {
        parent::_construct();
       	$this->setTemplate('AMG/securepay/directpost/customervault/form.phtml');

    }

	public function getCustomerVaultsCollection()
    {
    	$customerId = Mage::helper('securepay')->getCurrentQuoteCustomerId();
		if($customerId>0){
			return Mage::getModel('securepay/customervault')->getCollection()
				->addFieldToFilter('customer_id',$customerId);
		}
		return array();
    }
}
