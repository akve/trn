#SQL changes

ALTER TABLE `trn`.`trn_coupon_tracking` 
ADD COLUMN `last_check` VARCHAR(64) NULL AFTER `inserted`,
ADD COLUMN `got_review` INT NULL DEFAULT 0 AFTER `last_check`,
ADD COLUMN `review_properties` TEXT NULL AFTER `got_review`,
ADD COLUMN `review_score` FLOAT NOT NULL DEFAULT 0 AFTER `review_properties`;

ALTER TABLE `trn`.`wp_atn_buyer` 
ADD COLUMN `first_name` VARCHAR(128) NOT NULL DEFAULT '' AFTER `blocked`,
ADD COLUMN `last_name` VARCHAR(128) NOT NULL DEFAULT '' AFTER `first_name`;
