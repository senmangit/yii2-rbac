-- --------------------------------------------------------
-- 主机:                           127.0.0.1
-- 服务器版本:                        10.1.9-MariaDB-log - mariadb.org binary distribution
-- 服务器操作系统:                      Win32
-- HeidiSQL 版本:                  9.5.0.5196
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- 导出  表 auth.role 结构
CREATE TABLE IF NOT EXISTS `role` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `system_id` int(11) NOT NULL COMMENT '子系统唯一标志',
  `son_id` int(11) NOT NULL DEFAULT '1' COMMENT '角色在子系统的id(每个子系统从1开始）',
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '角色名称',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态，1：启用，0：不启用',
  `remark` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '备注',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`role_id`),
  KEY `index_2` (`status`) USING BTREE,
  KEY `system_id` (`system_id`),
  CONSTRAINT `role_ibfk_1` FOREIGN KEY (`system_id`) REFERENCES `system` (`system_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='角色';

-- 正在导出表  auth.role 的数据：~2 rows (大约)
/*!40000 ALTER TABLE `role` DISABLE KEYS */;
INSERT INTO `role` (`role_id`, `system_id`, `son_id`, `name`, `status`, `remark`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, '超级管理员', 0, NULL, '2018-06-01 18:47:27', '2018-06-01 18:47:27'),
	(2, 1, 2, '普通用户', 0, NULL, '2018-06-01 18:47:40', '2018-06-13 15:51:27');
/*!40000 ALTER TABLE `role` ENABLE KEYS */;

-- 导出  表 auth.role_rule 结构
CREATE TABLE IF NOT EXISTS `role_rule` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `role_id` int(11) NOT NULL COMMENT '角色ID',
  `rule_id` int(11) NOT NULL COMMENT '规则节点ID',
  PRIMARY KEY (`id`),
  KEY `fk_role_zn_access` (`role_id`) USING BTREE,
  KEY `fk_rule_zn_access` (`rule_id`) USING BTREE,
  CONSTRAINT `role_rule_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`),
  CONSTRAINT `role_rule_ibfk_2` FOREIGN KEY (`rule_id`) REFERENCES `rule` (`rule_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='角色和规则关系映射';

-- 正在导出表  auth.role_rule 的数据：~0 rows (大约)
/*!40000 ALTER TABLE `role_rule` DISABLE KEYS */;
INSERT INTO `role_rule` (`id`, `role_id`, `rule_id`) VALUES
	(1, 1, 4);
/*!40000 ALTER TABLE `role_rule` ENABLE KEYS */;

-- 导出  表 auth.rule 结构
CREATE TABLE IF NOT EXISTS `rule` (
  `rule_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `system_id` int(11) NOT NULL COMMENT '子系统唯一标志',
  `name` varchar(60) COLLATE utf8_unicode_ci NOT NULL COMMENT 'url（规则节点名称）',
  `title` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '标题',
  `href` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'url链接',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '上层ID',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态，1：启用，0：禁用',
  `remark` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '备注',
  `sort` int(11) DEFAULT NULL COMMENT '权重',
  `menu_show` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否显示菜单，0：不显示，1：显示',
  `icon` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`rule_id`),
  KEY `index_2` (`name`,`pid`,`status`,`menu_show`) USING BTREE,
  KEY `system_id` (`system_id`),
  CONSTRAINT `rule_ibfk_1` FOREIGN KEY (`system_id`) REFERENCES `system` (`system_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='节点规则';

-- 正在导出表  auth.rule 的数据：~1 rows (大约)
/*!40000 ALTER TABLE `rule` DISABLE KEYS */;
INSERT INTO `rule` (`rule_id`, `system_id`, `name`, `title`, `href`, `pid`, `status`, `remark`, `sort`, `menu_show`, `icon`, `create_at`, `update_at`) VALUES
	(4, 1, 'common/test', '公共菜单', '/common/test/', 0, 0, NULL, 1, 1, NULL, '2018-06-05 15:35:25', '2018-06-13 15:50:21');
/*!40000 ALTER TABLE `rule` ENABLE KEYS */;

-- 导出  表 auth.system 结构
CREATE TABLE IF NOT EXISTS `system` (
  `system_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT '系统名称',
  `remark` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '系统描述',
  `url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '系统入口',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态：1：正常启用，0：暂停使用',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`system_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='系统项目表';

-- 正在导出表  auth.system 的数据：~1 rows (大约)
/*!40000 ALTER TABLE `system` DISABLE KEYS */;
INSERT INTO `system` (`system_id`, `name`, `remark`, `url`, `status`, `created_at`, `updated_at`) VALUES
	(1, '权限管理', '权限管理', NULL, 0, '2018-06-04 13:22:25', '2018-06-13 15:48:25');
/*!40000 ALTER TABLE `system` ENABLE KEYS */;

-- 导出  表 auth.user 结构
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户id',
  `username` varchar(50) DEFAULT NULL COMMENT '账号名称',
  `real_name` varchar(50) NOT NULL DEFAULT '' COMMENT '用户姓名',
  `email` varchar(255) DEFAULT NULL COMMENT '用户邮箱',
  `status` tinyint(1) DEFAULT '10' COMMENT '用户状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- 正在导出表  auth.user 的数据：~0 rows (大约)
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` (`id`, `username`, `real_name`, `email`, `status`) VALUES
	(1, 'senman', '', NULL, 10);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;

-- 导出  表 auth.user_role 结构
CREATE TABLE IF NOT EXISTS `user_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `role_id` int(11) NOT NULL COMMENT '角色ID',
  PRIMARY KEY (`id`),
  KEY `fk_role_zn_user_role` (`role_id`) USING BTREE,
  KEY `fk_users_zn_user_role` (`user_id`) USING BTREE,
  CONSTRAINT `user_role_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`),
  CONSTRAINT `user_role_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=628 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='用户-角色';

-- 正在导出表  auth.user_role 的数据：~26 rows (大约)
/*!40000 ALTER TABLE `user_role` DISABLE KEYS */;
INSERT INTO `user_role` (`id`, `user_id`, `role_id`) VALUES
	(1, 1, 1);
/*!40000 ALTER TABLE `user_role` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
