ALTER TABLE `azdriver_chudro_az`.`users` 
ADD COLUMN `auth_key` VARCHAR(32) NULL AFTER `pass`,
ADD COLUMN `created_at` INT(11) NULL AFTER `note_profile`,
ADD COLUMN `updated_at` INT(11) NULL AFTER `created_at`,
ADD UNIQUE INDEX `auth_key_UNIQUE` (`auth_key` ASC) VISIBLE;