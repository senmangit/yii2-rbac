# yii2-rbac-manager

yii2的RBAC,基于角色的权限控制，扁平结构，容许一个用户有一个或多个角色的灵活配置，含菜单等等，支持多个子系统公用一个权限管理。

一、安装：

composer require senman/yii2-rbac-manager dev-master


二、导入数据库

导入vender/senman/yii2-rbac-manager/Rbac.sql

三、配置参数
在config/params.php里添加配置参数

```
 //权限配置
    'rbac_manager' => [
        "user_model" => "\\common\\models\\User",//用户模型地址
        "super_admin_id" => [578],
        "base_role_id" => "",//配置基本角色的ID
        "user_status" => [
            "status_deleted" => 0,//删除时的状态值
            "status_active" => 10,//正常时的状态值
        ],
        //不需要权限校验的路由
        "except_route" => [
            "rule/menus",
        ],
    ]
```

四、判断是否具有权限

    UserRole::hasAuth($user_id, $pathInfo = null, $module_id="", $system_id=1);