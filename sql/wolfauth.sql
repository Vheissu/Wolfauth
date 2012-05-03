/*
Navicat MySQL Data Transfer

Source Server         : Localhost
Source Server Version : 50516
Source Host           : localhost:3306
Source Database       : wolfauth

Target Server Type    : MYSQL
Target Server Version : 50516
File Encoding         : 65001

Date: 2012-04-21 12:17:58
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for `capabilities`
-- ----------------------------
DROP TABLE IF EXISTS `capabilities`;
CREATE TABLE `capabilities` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT,
  `capability` varchar(120) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of capabilities
-- ----------------------------
INSERT INTO capabilities VALUES ('1', 'read');
INSERT INTO capabilities VALUES ('2', 'edit_users');
INSERT INTO capabilities VALUES ('3', 'edit_pages');
INSERT INTO capabilities VALUES ('4', 'edit_listings');
INSERT INTO capabilities VALUES ('5', 'add_listing');
INSERT INTO capabilities VALUES ('6', 'delete_listing');
INSERT INTO capabilities VALUES ('7', 'edit_others_listings');
INSERT INTO capabilities VALUES ('8', 'edit_published_listings');
INSERT INTO capabilities VALUES ('9', 'ban_user');
INSERT INTO capabilities VALUES ('10', 'unban_user');
INSERT INTO capabilities VALUES ('11', 'god_power');

-- ----------------------------
-- Table structure for `ci_sessions`
-- ----------------------------
DROP TABLE IF EXISTS `ci_sessions`;
CREATE TABLE `ci_sessions` (
  `session_id` varchar(40) NOT NULL DEFAULT '0',
  `ip_address` varchar(16) NOT NULL DEFAULT '0',
  `user_agent` varchar(120) NOT NULL,
  `last_activity` int(10) unsigned NOT NULL DEFAULT '0',
  `user_data` text NOT NULL,
  PRIMARY KEY (`session_id`),
  KEY `last_activity_idx` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of ci_sessions
-- ----------------------------

-- ----------------------------
-- Table structure for `login_attempts`
-- ----------------------------
DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE `login_attempts` (
  `id` bigint(50) unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(120) DEFAULT NULL,
  `attempts` int(5) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of login_attempts
-- ----------------------------

-- ----------------------------
-- Table structure for `roles`
-- ----------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role` varchar(150) NOT NULL,
  `display_name` varchar(120) NOT NULL,
  PRIMARY KEY (`id`,`display_name`),
  UNIQUE KEY `Unique Role ID` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of roles
-- ----------------------------
INSERT INTO roles VALUES ('1', 'guest', 'Guest User');
INSERT INTO roles VALUES ('2', 'user', 'Standard User');
INSERT INTO roles VALUES ('3', 'editor', 'Editor');
INSERT INTO roles VALUES ('4', 'super_editor', 'Super Editor');
INSERT INTO roles VALUES ('5', 'admin', 'Administrator');
INSERT INTO roles VALUES ('6', 'super_admin', 'Super Admin');

-- ----------------------------
-- Table structure for `roles_capabilities`
-- ----------------------------
DROP TABLE IF EXISTS `roles_capabilities`;
CREATE TABLE `roles_capabilities` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(10) unsigned NOT NULL,
  `capability_id` int(10) NOT NULL,
  PRIMARY KEY (`id`,`role_id`,`capability_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of roles_capabilities
-- ----------------------------
INSERT INTO roles_capabilities VALUES ('1', '6', '11');
INSERT INTO roles_capabilities VALUES ('11', '1', '1');

-- ----------------------------
-- Table structure for `users`
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(50) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(10) NOT NULL DEFAULT '0',
  `username` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(250) NOT NULL,
  `remember_me` text NOT NULL,
  PRIMARY KEY (`id`,`username`,`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES ('1', '6', 'admin', 'admin@localhost', '2beed515fd3c1a7c837c36e40cefa23ffa6cf323a7a769c018021fbfb8df741a', '');