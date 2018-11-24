ALTER TABLE `azdriver_chudro_az`.`tickets` 
ADD COLUMN `label_status` VARCHAR(32) NULL ;

ALTER TABLE `azdriver_chudro_az`.`tickets` 
CHANGE COLUMN `file` `file` VARCHAR(50) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL ;