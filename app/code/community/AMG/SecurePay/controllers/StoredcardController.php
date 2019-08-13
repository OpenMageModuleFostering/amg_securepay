<?php
class AMG_SecurePay_StoredcardController extends Mage_Core_Controller_Front_Action
{
	public function preDispatch()
	{
		parent::preDispatch();
		$action = $this->getRequest()->getActionName();
		$loginUrl = Mage::helper('customer')->getLoginUrl();

		if(!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)){
			$this->setFlag('', self::FLAG_NO_DISPATCH, true);
		}
	}

	public function indexAction()
	{
		$this->loadLayout();
		$this->_initLayoutMessages('customer/session');
		$this->renderLayout();
    }

	public function deleteAction()
	{
		if(Mage::getSingleton('customer/session')->isLoggedIn()){
			$loggedInCustomerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
			if($loggedInCustomerId>0){
				$code = $this->getRequest()->getParam('c');
				if($code){
					$customerVaultModel = Mage::getModel('securepay/customervault');
					$customerVaultModel->loadByCode($code);
					if($customerVaultModel->getId() && ($customerVaultModel->getCustomerId() == $loggedInCustomerId)){
						$customerVaultModel->delete();
						Mage::getSingleton('customer/session')->addSuccess($this->__('Your selected Credit Card record has been deleted.'));
					}
				}
			}
		}
		$this->_redirect('*/*/');
    }
}
