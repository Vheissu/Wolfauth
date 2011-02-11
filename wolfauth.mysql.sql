/*
Navicat MySQL Data Transfer

Source Server         : Local
Source Server Version : 50141
Source Host           : localhost:3306
Source Database       : dealfury

Target Server Type    : MYSQL
Target Server Version : 50141
File Encoding         : 65001

Date: 2011-02-11 10:25:49
*/

SET FOREIGN_KEY_CHECKS=0;
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

-- ----------------------------
-- Table structure for `usermeta`
-- ----------------------------
DROP TABLE IF EXISTS `usermeta`;
CREATE TABLE `usermeta` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `meta_key` varchar(120) NOT NULL,
  `meta_value` longtext NOT NULL,
  PRIMARY KEY (`id`,`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of usermeta
-- ----------------------------
INSERT INTO usermeta VALUES ('1', '1', 'first_name', 'Admin');
INSERT INTO usermeta VALUES ('2', '1', 'last_name', 'Istrator');
INSERT INTO usermeta VALUES ('3', '1', 'dob', '22/08/1908');

-- ----------------------------
-- Table structure for `users`
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint(20) unsigned NOT NULL DEFAULT '1',
  `username` varchar(80) NOT NULL DEFAULT '',
  `password` varchar(120) NOT NULL,
  `email` varchar(80) NOT NULL,
  `super_admin` enum('yes','no') DEFAULT 'no',
  `activation_code` varchar(120) DEFAULT NULL,
  `remember_me` longtext,
  `last_login` int(10) unsigned DEFAULT NULL,
  `status` enum('banned','inactive','active') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`,`role_id`),
  UNIQUE KEY `uniq_username` (`username`),
  UNIQUE KEY `uniq_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO users VALUES ('1', '1', 'admin', 'password', 'admin@gmail.com', 'no', null, null, null, '');
