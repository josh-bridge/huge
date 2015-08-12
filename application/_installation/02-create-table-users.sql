CREATE TABLE IF NOT EXISTS `huge`.`users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'auto incrementing user_id of each user, unique index',
  `session_id` varchar(48) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'stores session cookie id to prevent session concurrency',
  `user_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'user''s name, unique',
  `user_cardno` bigint(16) unsigned NOT NULL COMMENT 'Users card reference number',
  `user_password_hash` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'user''s password in salted and hashed format',
  `user_email` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'user''s email, unique',
  `user_refcode` varchar(15) COLLATE utf8_unicode_ci NOT NULL COMMENT 'users referral code',
  `user_introducer_id` int(11) NOT NULL DEFAULT '3' COMMENT 'id of user that introduced this user',
  `user_active` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'user''s activation status',
  `user_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'user''s deletion status',
  `user_account_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'user''s account type (basic, premium, etc)',
  `user_has_avatar` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 if user has a local avatar, 0 if not',
  `user_remember_me_token` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'user''s remember-me cookie token',
  `user_creation_timestamp` bigint(20) DEFAULT NULL COMMENT 'timestamp of the creation of user''s account',
  `user_suspension_timestamp` bigint(20) DEFAULT NULL COMMENT 'Timestamp till the end of a user suspension',
  `user_last_login_timestamp` bigint(20) DEFAULT NULL COMMENT 'timestamp of user''s last login',
  `user_failed_logins` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'user''s failed login attempts',
  `user_last_failed_login` int(10) DEFAULT NULL COMMENT 'unix timestamp of last failed login attempt',
  `user_activation_hash` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'user''s email verification hash string',
  `user_password_reset_hash` char(40) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'user''s password reset code',
  `user_password_reset_timestamp` bigint(20) DEFAULT NULL COMMENT 'timestamp of the password reset request',
  `user_provider_type` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`user_id`), 
  ADD UNIQUE KEY `user_name` (`user_name`), 
  ADD UNIQUE KEY `user_email` (`user_email`), 
  ADD UNIQUE KEY `user_cardno` (`user_cardno`), 
  ADD UNIQUE KEY `user_refcode` (`user_refcode`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='user data';

INSERT INTO `huge`.`users` (`user_id`, `session_id`, `user_name`, `user_cardno`, `user_password_hash`, `user_email`, `user_refcode`, `user_introducer_id`, `user_active`, `user_deleted`, `user_account_type`, `user_has_avatar`, `user_remember_me_token`, `user_creation_timestamp`, `user_suspension_timestamp`, `user_last_login_timestamp`, `user_failed_logins`, `user_last_failed_login`, `user_activation_hash`, `user_password_reset_hash`, `user_password_reset_timestamp`, `user_provider_type`) VALUES
(1, NULL, 'demo', 0, '$2y$10$OvprunjvKOOhM1h9bzMPs.vuwGIsOqZbw88rzSyGCTJTcE61g5WXi', 'demo@demo.com', 'HZMj', 3, 1, 0, 2, 0, NULL, TIMESTAMP, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'DEFAULT'),
(2, NULL, 'demo2', 1, '$2y$10$OvprunjvKOOhM1h9bzMPs.vuwGIsOqZbw88rzSyGCTJTcE61g5WXi', 'demo2@demo.com', 't2G4', 3, 1, 0, 1, 0, NULL, TIMESTAMP, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'DEFAULT'),
(3, NULL, 'root', 2, '$2y$10$qtsOotyFpt.b.7yaKQnWgeTv1wvFwGGGM5PFtEUaCKqi9boW7C2Z2', 'root@localhost', 'root', 3, 1, 0, 7, 0, NULL, TIMESTAMP, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'DEFAULT');

CREATE TABLE IF NOT EXISTS `huge`.`users_details` (
  `user_id` int(11) NOT NULL,
  `user_firstname` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `user_lastname` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `user_dob` date NOT NULL,
  `user_addrline1` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `user_addrline2` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_addrline3` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_postcode` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `user_city` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `user_country` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `user_telephone` varchar(13) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_mobile` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `user_business` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `huge`.`users_details` (`user_id`, `user_firstname`, `user_lastname`, `user_dob`, `user_addrline1`, `user_addrline2`, `user_addrline3`, `user_postcode`, `user_city`, `user_country`, `user_telephone`, `user_mobile`, `user_business`) VALUES
(1, 'Test', 'Details', '1996-02-05', '123 Fake Lane', NULL, NULL, 'MN0 0MN', 'FakeCity', 'United Kingdom', NULL, '07000000000', NULL),
(2, 'Test', 'Details', '1996-02-05', '123 Fake Lane', NULL, NULL, 'MN0 0MN', 'FakeCity', 'United Kingdom', NULL, '07000000000', NULL),
(3, 'Test', 'Details', '1980-02-05', '123 Fake Lane', NULL, NULL, 'MN0 0MN', 'FakeCity', 'United Kingdom', NULL, '07000000000', 'Test Business'),