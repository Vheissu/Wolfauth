/*
Navicat MySQL Data Transfer

Source Server         : Localhost
Source Server Version : 50516
Source Host           : localhost:3306
Source Database       : wolfauth

Target Server Type    : MYSQL
Target Server Version : 50516
File Encoding         : 65001

Date: 2012-03-07 21:33:48
*/

SET FOREIGN_KEY_CHECKS=0;
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
-- Table structure for `permissions`
-- ----------------------------
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(10) NOT NULL,
  `permission` varchar(120) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of permissions
-- ----------------------------

-- ----------------------------
-- Table structure for `roles`
-- ----------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role_name` varchar(120) NOT NULL,
  `role_slug` varchar(150) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Unique Role ID` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of roles
-- ----------------------------
INSERT INTO roles VALUES ('1', 'Guest User', 'guest');
INSERT INTO roles VALUES ('2', 'Standard User', 'user');
INSERT INTO roles VALUES ('3', 'Editor', 'editor');
INSERT INTO roles VALUES ('4', 'Super Editor', 'super_editor');
INSERT INTO roles VALUES ('5', 'Administrator', 'admin');
INSERT INTO roles VALUES ('6', 'Super Admin', 'super_admin');

-- ----------------------------
-- Table structure for `users`
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(50) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(10) NOT NULL DEFAULT '0',
  `username` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `salt` varchar(50) NOT NULL,
  `password` varchar(250) NOT NULL,
  `first_name` varchar(125) DEFAULT NULL,
  `last_name` varchar(125) DEFAULT NULL,
  `activated` enum('no','yes') NOT NULL DEFAULT 'no',
  `joined` int(11) NOT NULL,
  `remember_me` text NOT NULL,
  PRIMARY KEY (`id`,`username`,`email`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of users
-- ----------------------------
