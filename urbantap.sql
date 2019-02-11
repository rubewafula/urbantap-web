CREATE  TABLE  users_ (id INT(11) AUTO_INCREMENT,
first_name VARCHAR(100) NOT NULL,
last_name VARCHAR(100) NOT NULL,
email VARCHAR(191) NOT NULL,
phone_no VARCHAR(191) NOT NULL,
PRIMARY KEY(id));   
CREATE TABLE  role_user(role_id INT(10),user_id INT(10));
ALTER  TABLE  role_user ADD FOREIGN KEY(role_id) REFERENCES roles(id) ON UPDATE CASCADE ON DELETE RESTRICT;
ALTER TABLE `role_user` CHANGE `user_id` `user_id` INT(10) NOT NULL; 

CREATE TABLE `roles` (
  `id` INT(10) UNSIGNED NOT NULL,
  `name` VARCHAR(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` VARCHAR(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE  TABLE status_categories(`id` INT(10) UNSIGNED  AUTO_INCREMENT,`name` VARCHAR(200) NOT  NULL ,`created_at` TIMESTAMP NULL,`updated_at` TIMESTAMP NULL,  PRIMARY KEY (id));
CREATE  TABLE  statuses(`id` INT(10) UNSIGNED AUTO_INCREMENT,`status_category_id` INT NOT NULL,`name` VARCHAR(200),PRIMARY KEY (id));
ALTER  TABLE users ADD COLUMN `phone_verification_code` VARCHAR(100) NULL;
ALTER  TABLE users ADD COLUMN `verification_sent` INT(2) NOT NULL ;
ALTER  TABLE  users ADD COLUMN  `phone_verified` TINYINT(2) NOT NULL DEFAULT 0;
ALTER  TABLE  users ADD COLUMN  `email_verified` TINYINT(2) NOT NULL DEFAULT 0; 
ALTER  TABLE  users ADD COLUMN  `status_id`  INT(3) NULL ;
ALTER  TABLE  users ADD COLUMN  `confirmation_token` VARCHAR(50) NULL; 














