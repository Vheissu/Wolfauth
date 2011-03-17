-- ----------------------------
-- Table structure for `ci_sessions`
-- ----------------------------
DROP TABLE IF EXISTS `ci_sessions`;
CREATE TABLE `ci_sessions` (
  `session_id` varchar(40) NOT NULL DEFAULT '0',
  `ip_address` varchar(16) NOT NULL DEFAULT '0',
  `user_agent` varchar(50) NOT NULL,
  `last_activity` int(10) unsigned NOT NULL DEFAULT '0',
  `user_data` text NOT NULL,
  PRIMARY KEY (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `users`
-- ----------------------------
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(10) NOT NULL DEFAULT '1',
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(120) NOT NULL,
  `last_login` varchar(25) NOT NULL,
  `user_meta` longtext NOT NULL,
  `remember_me` longtext,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Unique` (`username`,`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO users VALUES ('1', '10', 'admin', 'password', 'testadmin@localhost', '', '', null);

-- ----------------------------
-- Table structure for `roles`
-- ----------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT,
  `actual_role_id` bigint(10) unsigned NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`,`actual_role_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of roles
-- ----------------------------
INSERT INTO roles VALUES ('1', '0', 'Guest', 'This is the default role everyone is if they\'re not logged in. Site visitors are guests until they log in.');
INSERT INTO roles VALUES ('2', '1', 'User', 'Standard registered user.');
INSERT INTO roles VALUES ('3', '2', 'Client', 'Client priveleges such as submitting things and editing their own stuff.');
INSERT INTO roles VALUES ('4', '3', 'Moderator', 'Moderator priveleges like editing comments and other things.');
INSERT INTO roles VALUES ('5', '4', 'Administrator', 'Site administrator can edit settings, etc.');
INSERT INTO roles VALUES ('6', '5', 'Elevated Administrator', 'Has elevated administrator priveleges such as being able to change things without changes appearing in the change log of the site or something.');