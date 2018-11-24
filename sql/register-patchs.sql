ALTER TABLE `azdriver_chudro_az`.`users` 
CHANGE COLUMN `acesstoekn` `acesstoekn` VARCHAR(80) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL ,
CHANGE COLUMN `photoimage` `photoimage` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL ,
CHANGE COLUMN `note_profile` `note_profile` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL ,
ADD COLUMN `isReseller` TINYINT(1) NOT NULL DEFAULT '0' AFTER `policy`;


ALTER TABLE `azdriver_chudro_az`.`agency` 
CHANGE COLUMN `DocumentoReporte` `DocumentoReporte` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL ,
CHANGE COLUMN `ReporteClaims` `ReporteClaims` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL ,
CHANGE COLUMN `CommisionReport` `CommisionReport` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL ,
CHANGE COLUMN `mgtmreport` `mgtmreport` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL ,
CHANGE COLUMN `reportereloj` `reportereloj` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL ,
CHANGE COLUMN `fax` `fax` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL ,
CHANGE COLUMN `website` `website` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL ,
CHANGE COLUMN `reportdealer` `reportdealer` VARCHAR(40) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL ,
CHANGE COLUMN `id_owner` `id_owner` TINYINT(4) NULL ;

ALTER TABLE `azdriver_chudro_az`.`agency` 
CHANGE COLUMN `id_owner` `id_owner` INT(11) NULL DEFAULT NULL ;