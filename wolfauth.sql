/*
Navicat MySQL Data Transfer

Source Server         : Localhost
Source Server Version : 50508
Source Host           : localhost:3306
Source Database       : wolfauth

Target Server Type    : MYSQL
Target Server Version : 50508
File Encoding         : 65001

Date: 2011-04-07 13:07:26
*/

SET FOREIGN_KEY_CHECKS=0;
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
-- Table structure for `roles`
-- ----------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of roles
-- ----------------------------
INSERT INTO roles VALUES ('1', 'Registered User', 'registered', 'A standard registered user.');
INSERT INTO roles VALUES ('2', 'Moderative User', 'moderator', 'A user with moderation priveleges.');
INSERT INTO roles VALUES ('3', 'Administator', 'administrator', 'An administrative user.');
INSERT INTO roles VALUES ('4', 'Super Administrator', 'superadmin', 'A super admin, like a God.');

-- ----------------------------
-- Table structure for `users`
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `join_date` datetime NOT NULL,
  `last_login` datetime NOT NULL,
  `salt` varchar(255) NOT NULL,
  `remember_me` longtext NOT NULL,
  `profile_fields` longtext NOT NULL,
  `status` enum('banned','inactive','validating','active') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Unique` (`username`,`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO users VALUES ('1', 'admin', 'fd40f787af9d2e136faa1df87114439fbba667d5', 'admin@admin.com', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '21057135534d9932dc1cbef2.70204711', '', '', 'banned');
INSERT INTO users VALUES ('2', 'testuser', '6d0085e0d86c2bf3607eac0fd390e96e135c58b9', 'testuser@test.com', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '18887459754d999e2fe30e86.34226797', '', 'a:3:{s:6:\"points\";i:10;s:7:\"uploads\";i:15;s:5:\"ratio\";s:3:\"9.5\";}', 'banned');

-- ----------------------------
-- Table structure for `users_to_roles`
-- ----------------------------
DROP TABLE IF EXISTS `users_to_roles`;
CREATE TABLE `users_to_roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(10) NOT NULL,
  `user_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of users_to_roles
-- ----------------------------
INSERT INTO users_to_roles VALUES ('1', '4', '1');
