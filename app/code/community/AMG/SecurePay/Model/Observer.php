<?php
class AMG_SecurePay_Model_Observer
{
	public function salesQuotePaymentImportDataBefore(Varien_Event_Observer $observer)
	{
		$input = $observer->getInput();
		$payment = $observer->getPayment();
		$paymentMethod = $input->getMethod();
		if(isset($paymentMethod)){
			$additionalFields = Mage::helper('securepay')->getPaymentMethodAdditionalInfoFields();
			if(!empty($additionalFields)){
				foreach($additionalFields as $field){
					if($payment->hasAdditionalInformation($field)){
                        $payment->unsAdditionalInformation($field);
					}
				}
			}
			if($paymentMethod==Mage::getModel('securepay/directpost')->getCode()){
				if(Mage::helper('securepay')->isCcSaveAllowed()){	
					if($input['cc_save_future_directpost'] == 'Y'){
						$payment->setAdditionalInformation('cc_save_future_directpost', 'Y');
					}
				}
			}
			elseif($paymentMethod==Mage::getModel('securepay/directpost_customervault')->getCode()){
				if($input['customervault_code']) {
					$code = $input['customervault_code'];
					$customerVaultModel = Mage::getModel('securepay/customervault');
					$customerVaultModel->loadByCode($code);
					if($customerVaultModel->getId()){
						$customerId = Mage::helper('securepay')->getCurrentQuoteCustomerId();
						if($customerVaultModel->getCustomerId()==$customerId)	
							$payment->setAdditionalInformation('customervault_id', $customerVaultModel->getCustomervaultId());
					}
				}
			}
		}
	}

	public function checkoutSubmitAllAfter($observer)
    {
		$orders = $observer->getOrders();
		if(is_null($orders)){
			$orders = array($observer->getOrder());
		}
		foreach($orders as $order){
			$customerId = $order->getData('customer_id');
			if(!$customerId){
				return;
			}
			$payment = $order->getPayment();
			$paymentMethod = $payment->getMethod();
            if(isset($paymentMethod) && $payment->getData('transaction_id') ){
				if($paymentMethod==Mage::getModel('securepay/directpost')->getCode()){
					if(Mage::helper('securepay')->isCcSaveAllowed()){	
						if($payment->hasAdditionalInformation('cc_save_future_directpost') &&($payment->getAdditionalInformation('cc_save_future_directpost') == 'Y')){
							$amg_customer_vault_id = '';
							if($payment->hasAdditionalInformation('amg_customer_vault_id') &&($payment->getAdditionalInformation('amg_customer_vault_id') != '')){
								$amg_customer_vault_id = $payment->getAdditionalInformation('amg_customer_vault_id');
							
	                        	$customerVaultModel = Mage::getModel('securepay/customervault');
		                        $customerVaultModel->setData(array(
		                            'transaction_id' 		=> $payment->getData('transaction_id'),
		                            'customer_id'    		=> $customerId,
		                            'cc_type' 				=> $payment->getData('cc_type'),
		                            'cc_last4' 				=> $payment->getData('cc_last4'),
		                            'cc_exp_month' 			=> $payment->getData('cc_exp_month'),
		                            'cc_exp_year' 			=> $payment->getData('cc_exp_year'),
		                            'created_date' 			=> now(),
		                            'updated_date' 			=> now(),
		                            'amg_customer_vault_id' => $amg_customer_vault_id
		                        ));
								if($payment->hasAdditionalInformation('customervault_id') &&($payment->getAdditionalInformation('customervault_id') != '')){
									$customerVaultModel->setId($payment->getAdditionalInformation('customervault_id'));
								}
								else{
									$code = $customerVaultModel->generateUniqueCode();
									$customerVaultModel->setData('code',$code);
								}
		                        $customerVaultModel->save();
	                        }
	                    }
                    }
                }
                elseif($paymentMethod==Mage::getModel('securepay/directpost_customervault')->getCode()){
                    if ($payment->hasAdditionalInformation('customervault_id') && $payment->getData('transaction_id')) {
                        $customervault_id = $payment->getAdditionalInformation('customervault_id');
                        if($customervault_id>0){
                        	$customerVaultModel = Mage::getModel('securepay/customervault');
                        	$customerVaultModel->load($customervault_id);
                        	if($customerVaultModel->getId()){
                            	if($customerVaultModel->getCustomerId()>0 && ($customerVaultModel->getCustomerId()==$customerId))	
                            		$customerVaultModel->setData('transaction_id', $payment->getData('transaction_id'))
                            							->setData('updated_date', now())
                            							->save();
                        	}
                        }
                    }
                }
            }
        }
    }

}
