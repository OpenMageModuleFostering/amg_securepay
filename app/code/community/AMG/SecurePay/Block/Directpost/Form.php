<?php
class AMG_SecurePay_Block_Directpost_Form extends Mage_Payment_Block_Form_Cc
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('AMG/securepay/directpost/form.phtml');
    }

    public function isCcSaveAllowed()
    {
        return Mage::helper('securepay')->isCcSaveAllowed();	
     }
}
