<?php
class AMG_SecurePay_Model_Directpost_Customervault_Source_PaymentAction
{
    const AUTHORIZE 		= 'authorize';
	const AUTHORIZE_CAPTURE = 'authorize_capture';
	const SALE 				= 'sale';
	
    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::AUTHORIZE,
                'label' => Mage::helper('securepay')->__('Authorize Only')
            ),
            array(
                'value' => self::AUTHORIZE_CAPTURE,
                'label' => Mage::helper('securepay')->__('Authorize And Capture')
            ),
            array(
                'value' => self::SALE,
                'label' => Mage::helper('securepay')->__('Sale')
            ),
        );
    }
}
