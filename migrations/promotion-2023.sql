ALTER TABLE `Grade` ADD `productAmount` FLOAT NOT NULL DEFAULT 50 AFTER `amount`, ADD `officeAmount` FLOAT NOT NULL DEFAULT 10 AFTER `productAmount`, ADD `membershipAmount` FLOAT NOT NULL DEFAULT 20 AFTER `officeAmount`; 
ALTER TABLE `VirtualMoney` CHANGE `amount` `amount` INT UNSIGNED NOT NULL DEFAULT '0';