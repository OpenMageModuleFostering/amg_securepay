<?php
class AMG_SecurePay_Block_Customer_Storedcard extends Mage_Core_Block_Template
{
	protected function _prepareLayout() 
	{
		parent::_prepareLayout();	
		if($head = $this->getLayout()->getBlock('head')){
			$head->setTitle($this->__('My Credit Cards'));
		}
	}
		
	public function getStoredCards()
	{
		if(Mage::getSingleton('customer/session')->isLoggedIn()){
			$loggedInCustomerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
			if($loggedInCustomerId>0){
				$collection = Mage::getModel('securepay/customervault')->getCollection()
							->addFieldToFilter('customer_id',$loggedInCustomerId);
				if(count($collection)>0)
					return $collection;			
			}
		}
		return array();
    }

    public function getDeleteUrl($storedCard)
	{
		return $this->getUrl('*/*/delete', array('c'=>$storedCard->getCode()));
	}
}
