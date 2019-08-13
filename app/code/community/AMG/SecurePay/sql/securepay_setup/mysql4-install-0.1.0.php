<?php
$installer = $this;
$installer->startSetup();
try{
	$installer->run("
		DROP TABLE IF EXISTS {$this->getTable('securepay/customervault')};
		CREATE TABLE IF NOT EXISTS {$this->getTable('securepay/customervault')} (
		  `customervault_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `code` varchar(20) NOT NULL DEFAULT '',
		  `transaction_id` varchar(255) NOT NULL DEFAULT '',
		  `customer_id` int(10) unsigned NOT NULL DEFAULT '0',
		  `cc_type` varchar(10) NOT NULL DEFAULT '',
		  `cc_last4` varchar(20) NOT NULL DEFAULT '',
		  `cc_exp_month` int(10) NOT NULL DEFAULT 0,
		  `cc_exp_year` int(10) NOT NULL DEFAULT 0,
		  `created_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		  `updated_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		  `amg_customer_vault_id` varchar(255) NOT NULL DEFAULT '',
		  PRIMARY KEY (`customervault_id`),
		  UNIQUE KEY `code` (`code`),
		  KEY `FK_AMG_CUSTOMER_VAULT_ID_CUSTOMER_ENTITY_ID` (`customer_id`),
		  CONSTRAINT `FK_AMG_CUSTOMER_VAULT_ID_TO_CUSTOMER_ENTITY_ID` FOREIGN KEY (`customer_id`) REFERENCES `{$this->getTable('customer/entity')}` (`entity_id`) ON DELETE CASCADE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	");
	
	$io = new Varien_Io_File();
	$io->checkAndCreateFolder(Mage::getBaseDir().DS.'var'.DS.'log'.DS.'AMG'.DS.'SecurePay');
} 
catch(Exception $e){
	Mage::log($e->getMessage());
}
$installer->endSetup();