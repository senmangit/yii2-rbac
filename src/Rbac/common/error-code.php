<?php
/**
 * 基本常量
 */
define('SUCCESS', '00000');
define('FAIL', '00001');
define('ERROR_METHOD', '00002');   //错误的请求方法
define('ERROR_PARAM', '00003');    //非法的字段参数
define('EXCEPTION', '00004');  // 异常抛出
define('AUTH_FAIL', '00005');  // 权限不足
define('PAGE_LIMIT_ERROR', '00006');  //分页limit参数错误
define('PAGE_OFFSET_ERROR', '00007');  //分页offset参数错误
define('PID_ERROR', '00008');  //父级ID错误
define('FREQUENT_REQUEST', '00009');  // 请求过于频繁
define('DATA_DUPLICATION', '00010');  // 数据已存在
define('DATA_DO_NOT_EXIST', '00011');  // 数据不存在
define('MISSING_PARAM', '00012');    // 参数缺失


/**
 * 授权
 */
define('USER_NO_FOUND', '40001');  // 用户未找到
define('USER_STATUS_ERROR', '40002');  // 该用户状态异常
define('USER_IS_DELETED', '40003');  // 该用户已被删除
define('AUTH_EXCEPTION', '40004');  // 授权异常


/**
 * 角色
 */
define("USER_ID_ERROR", 10010);  // 用户ID错误
define("ROLE_ID_ERROR", 20007);  // 角色ID错误
define("ROLE_NAME_EMPTY", 20008);  //角色名称不许为空
define("ROLE_NAME_ALREADY_EXIST", 20009);//角色名称已被占用
define("ROLE_STATUS_ERROR", 20010);  //角色状态错误
define("ROLE_IS_USED", 20011);  // 该角色已经被使用


/**
 * 规则节点
 */
define("RULE_NAME_EMPTY", 21000);  // 规则节点路由地址不许为空
define("RULE_NAME_ALREADY_EXIST", 21002);  // 规则节点路由地址已被占用
define("RULE_TITLE_ERROR", 21003);  // 规则节点名称不许为空
define("RULE_STATUS_ERROR", 21004);  // 规则节点状态错误
define("RULE_MENU_SHOW_ERROR", 21005);  // 是否菜单状态错误
define("RULE_ID_ERROR", 21006);  // 规则节点ID错误
define("RULE_IS_USED", 21007);  // 该节点已经被使用


/**
 * 系统
 */
define("SYSTEM_ID_ERROR", 30000);  // 系统ID错误


