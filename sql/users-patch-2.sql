ALTER TABLE `azdriver_chudro_az`.`clients` 
CHANGE COLUMN `phone2` `phone2` VARCHAR(20) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL AFTER `phone`,
CHANGE COLUMN `CardNumber` `CardNumber` VARCHAR(50) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL ,
CHANGE COLUMN `fechabaja` `fechabaja` DATE NULL ,
CHANGE COLUMN `notas` `notas` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL ;

ALTER TABLE `azdriver_chudro_az`.`clients` 
CHANGE COLUMN `Plan_client` `Plan_client` INT(10) NULL ,
CHANGE COLUMN `Status_client` `Status_client` INT(10) NULL ,
CHANGE COLUMN `Dpayment` `Dpayment` VARCHAR(25) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL ,
CHANGE COLUMN `estservicio` `estservicio` INT(11) NULL ,
CHANGE COLUMN `MF` `MF` INT(11) NULL DEFAULT 0 ,
CHANGE COLUMN `expiration` `expiration` DATE NULL,
ADD COLUMN `paid` DOUBLE NULL AFTER `total`,
ADD COLUMN `pending` DOUBLE NULL AFTER `paid`;

DROP COLUMN `adeudo`;