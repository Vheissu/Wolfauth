/*
Navicat MySQL Data Transfer

Source Server         : Localhost
Source Server Version : 50508
Source Host           : localhost:3306
Source Database       : loqally

Target Server Type    : MYSQL
Target Server Version : 50508
File Encoding         : 65001

Date: 2011-08-04 15:46:58
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `groups`
-- ----------------------------
DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `description` mediumtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of groups
-- ----------------------------
INSERT INTO `groups` VALUES ('1', 'Administrators', 'Just your standard administrators group');
INSERT INTO `groups` VALUES ('2', 'Users', 'Just your standard users group');

-- ----------------------------
-- Table structure for `groups_permissions`
-- ----------------------------
DROP TABLE IF EXISTS `groups_permissions`;
CREATE TABLE `groups_permissions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(11) unsigned NOT NULL,
  `permission_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`,`group_id`,`permission_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of groups_permissions
-- ----------------------------
INSERT INTO `groups_permissions` VALUES ('1', '1', '1');

-- ----------------------------
-- Table structure for `groups_roles`
-- ----------------------------
DROP TABLE IF EXISTS `groups_roles`;
CREATE TABLE `groups_roles` (
  `id` int(11) unsigned NOT NULL,
  `group_id` int(11) unsigned NOT NULL,
  `role_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`,`group_id`,`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of groups_roles
-- ----------------------------
INSERT INTO `groups_roles` VALUES ('0', '1', '4');

-- ----------------------------
-- Table structure for `groups_users`
-- ----------------------------
DROP TABLE IF EXISTS `groups_users`;
CREATE TABLE `groups_users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`,`group_id`,`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of groups_users
-- ----------------------------
INSERT INTO `groups_users` VALUES ('1', '1', '1');
INSERT INTO `groups_users` VALUES ('2', '2', '1');

-- ----------------------------
-- Table structure for `login_attempts`
-- ----------------------------
DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE `login_attempts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(50) NOT NULL,
  `attempts` int(5) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of login_attempts
-- ----------------------------
INSERT INTO `login_attempts` VALUES ('20', '127.0.0.1', '1', '2011-08-03 11:34:09', '0000-00-00 00:00:00');

-- ----------------------------
-- Table structure for `permissions`
-- ----------------------------
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `permission` varchar(120) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of permissions
-- ----------------------------
INSERT INTO `permissions` VALUES ('1', 'questions/index');
INSERT INTO `permissions` VALUES ('2', 'testauth/index');

-- ----------------------------
-- Table structure for `permissions_roles`
-- ----------------------------
DROP TABLE IF EXISTS `permissions_roles`;
CREATE TABLE `permissions_roles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `permission_id` int(11) unsigned NOT NULL,
  `role_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`,`permission_id`,`role_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of permissions_roles
-- ----------------------------

-- ----------------------------
-- Table structure for `permissions_users`
-- ----------------------------
DROP TABLE IF EXISTS `permissions_users`;
CREATE TABLE `permissions_users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `permission_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`,`permission_id`,`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of permissions_users
-- ----------------------------
INSERT INTO `permissions_users` VALUES ('1', '1', '1');
INSERT INTO `permissions_users` VALUES ('2', '2', '1');

-- ----------------------------
-- Table structure for `roles`
-- ----------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Unique Role` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of roles
-- ----------------------------
INSERT INTO `roles` VALUES ('1', 'Guest', 'A non-registered guest user.');
INSERT INTO `roles` VALUES ('2', 'User', 'A standard user.');
INSERT INTO `roles` VALUES ('3', 'Moderator', 'A moderative user can edit questions, etc.');
INSERT INTO `roles` VALUES ('4', 'Admin', 'A site administrator can basically do everything.');
INSERT INTO `roles` VALUES ('5', 'Super Admin', 'A super admin has God like powers.');

-- ----------------------------
-- Table structure for `roles_users`
-- ----------------------------
DROP TABLE IF EXISTS `roles_users`;
CREATE TABLE `roles_users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`,`role_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of roles_users
-- ----------------------------

-- ----------------------------
-- Table structure for `umeta`
-- ----------------------------
DROP TABLE IF EXISTS `umeta`;
CREATE TABLE `umeta` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(80) DEFAULT NULL,
  `last_name` varchar(80) DEFAULT NULL,
  `country` varchar(80) DEFAULT NULL,
  `post_code` int(10) DEFAULT NULL,
  `dob` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of umeta
-- ----------------------------
INSERT INTO `umeta` VALUES ('1', 'Admin', 'Smith', 'Australia', '4000', '24/05/1988');

-- ----------------------------
-- Table structure for `users`
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `umeta_id` int(11) NOT NULL,
  `username` varchar(60) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password` varchar(150) NOT NULL,
  `salt` varchar(120) NOT NULL,
  `questions_count` int(5) NOT NULL DEFAULT '0',
  `answers_count` int(5) NOT NULL DEFAULT '0',
  `points` int(10) unsigned NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `last_login` datetime NOT NULL,
  `last_ip` varchar(60) NOT NULL,
  `status` enum('banned','active','inactive') NOT NULL DEFAULT 'inactive',
  `activation_code` varchar(150) NOT NULL,
  `remember_me` mediumtext,
  PRIMARY KEY (`id`,`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES ('1', '1', 'admin', 'admin@localhost', '06da0b92ad87efda3ec60fd184f7a7cbc672fc00', 'ZroJKXzM', '0', '0', '0', '2011-07-31 08:45:48', '0000-00-00 00:00:00', '', 'active', '', null);
