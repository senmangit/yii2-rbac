<?php
$languages = [
    'SUCCESS' => '操作成功',
    'FAIL' => '操作失败',
    'ERROR_METHOD' => '错误的请求方法',
    'ERROR_PARAM' => '非法的字段参数',
    'EXCEPTION' => '异常抛出',
    'AUTH_FAIL' => '权限不足',
    'PAGE_LIMIT_ERROR' => 'limit参数错误',
    'PAGE_OFFSET_ERROR' => 'offset参数错误',
    'FREQUENT_REQUEST' => '请求过于频繁',
    'PID_ERROR' => '父级ID错误',
    'DATA_DUPLICATION' => '数据已存在',
    'DATA_DO_NOT_EXIST' => '数据不存在',
    'MISSING_PARAM' => '参数缺失',

    //Auth
    "RULE_NAME_EMPTY" => "节点地址不许为空",
    "RULE_NAME_ALREADY_EXIST" => "规则节点地址已被占用",
    "RULE_TITLE_ERROR" => "规则节点名称不许为空",
    "RULE_STATUS_ERROR" => "规则节点状态错误",
    "RULE_MENU_SHOW_ERROR" => "是否菜单状态错误",
    "RULE_ID_ERROR" => "规则节点ID错误",
    "RULE_IS_USED" => "该节点已经被使用",

    "ROLE_ID_ERROR" => "角色ID错误",
    "ROLE_NAME_EMPTY" => "角色名称不许为空",
    "ROLE_NAME_ALREADY_EXIST" => "角色名称已被占用",
    "ROLE_STATUS_ERROR" => "角色状态错误",
    "ROLE_IS_USED" => "当前角色正在使用",

    "USER_NO_FOUND" => "用户未找到",
    "USER_STATUS_ERROR" => "该用户状态异常",
    "USER_IS_DELETED" => "该用户已被删除",
    "AUTH_EXCEPTION" => "授权异常",
    "USER_ID_ERROR" => "用户ID错误",

];

foreach ($languages as $languageKey => $languageVal) {
    define($languageKey . '_LANG', $languageVal);
}
