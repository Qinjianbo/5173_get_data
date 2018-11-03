/*
 Navicat Premium Data Transfer

 Source Server         : local
 Source Server Type    : MySQL
 Source Server Version : 50722
 Source Host           : localhost:3306
 Source Schema         : 5173_data

 Target Server Type    : MySQL
 Target Server Version : 50722
 File Encoding         : 65001

 Date: 03/11/2018 18:17:37
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for muDealHistory
-- ----------------------------
DROP TABLE IF EXISTS `muDealHistory`;
CREATE TABLE `muDealHistory` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '商品名称',
  `type` varchar(20) NOT NULL COMMENT '商品类型',
  `price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '成交价格',
  `dealTime` datetime NOT NULL COMMENT '成交时间',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `gameArea` varchar(100) NOT NULL DEFAULT '' COMMENT '游戏区服阵营',
  `link` varchar(255) NOT NULL DEFAULT '' COMMENT '商品详情链接',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_link` (`link`) USING BTREE COMMENT '商品详情链接唯一'
) ENGINE=InnoDB AUTO_INCREMENT=6249 DEFAULT CHARSET=utf8;

SET FOREIGN_KEY_CHECKS = 1;
