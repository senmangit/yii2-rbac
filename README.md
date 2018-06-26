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

   

    \Rbac\models\UserRole::hasAuth($user_id, $pathInfo = null, $module_id="", $system_id=1);
    
    
五、常用方法    
  
  
  角色相关：
  
  
  获取角色的主键ID
  
  Role::getRolePrimaryKey();
  
  
  获取该角色所属的系统信息
  
  Role::getSystem();
  
  
  获取该角色关联的规则
  
  Role::getRoleRules();
  
  
  获取归属于该角色的用户
  
  Role::getUserRoles();
  
  
  根据条件修改角色
  
  $condition为条件数组，例如
  
  ['role_id'=>1,'name'=>'test']
  
  $data为需要修改的数据，例如
  
  ['sort'=>100]
  
  Role::updateByCondition($condition, $data);
  
  
  根据角色ID进行修改
  
  $role_id 为角色ID
  
  $data为需要修改的数据，例如
  
  ['sort'=>100]
  
  Role::updateByRoleId($role_id, $data);
  
  
  根据角色ID条件进行删除
  
  Role::deleteByRoleId($role_id)
  
  根据条件进行删除
  
  $condition为条件数组，例如
  
  ['role_id'=>1,'name'=>'test']
  
  Role::deleteByCondition($condition)
  
  获取该角色下的所有节点名
  
  $role_id为角色ID
  
  $field 角色表字段名，默认为：name，即路由地址
  
  $status 状态值，0：为启用状态，默认只获取正常启用状态的角色
  
  $system_id 系统ID，属于哪个子系统
  
  Role::getAccessByRoleId($role_id, $field = "name", $status = 0, $system_id)
  
  通过角色ID获取角色信息
  
  $role_id 角色ID
  
  $fields 获取的字段，默认为所有，示例：
  
  ['role_id','name']
  
  Role::getRoleById($role_id, $fields = "*")
  
  通过条件进行获取角色
  
  $condition为条件数组，例如
  
  ['role_id'=>1,'name'=>'test']
  
  $fields 获取的字段，默认为所有，示例：
  
  ['role_id','name']
  
  Role::getRoleByCondition($condition, $fields = "*")
  
  添加角色
  
  $data为需要添加的数据，示例：
  
  [  
    'system_id'=>1,
    'name'=>'test',
    'status'=>0,
    'remark'=>'备注',  
  ];
  
  Role::add($data);
  
  根据条件获取带分页的角色列表  
  
  $page 页码，默认为0
  
  $limit 每一页条数，默认为20
  
  $condition 条件数组，默认为空
  
  Role::listOfPagin($page, $limit = 20, $condition=[]);
  
  
  给角色授权相关：
  
  
  通过用户ID获取所有权限
  
  $user_id 用户ID
  
  $system_id 子系统ID
  
  该方法返回该用户在该系统ID下的所有规则路由name
  
  RoleRule::getAccess($user_id, $system_id);
 
  
  根据条件进行删除
  
  RoleRule::deleteByCondition($condition = array());
  
  
  判断是否为超管，注意该超管是写死在配置文件中的，可供调试所用
  
  RoleRule::is_super_admin($user_id);
  
  判断是否认证通过
  
  $pathInfo为需要判断的路由地址，若为null则取当前路由
  
  $module_id为版本id，例如：http:://www.monqin.com/v1/user/info
  
  则其中的V1为版本id，若无此版本控制，可直接为空
  
  $system_id 子系统ID
  
  返回值参考： 
  
  400：已授权
  
  401:该用户未找到
  
  402：该用户状态异常
  
  403：该用户已被删除
  
  404：授权异常
  
  405：权限不足
  
  406：该系统已经被禁用
  
  RoleRule::auth($user_id, $pathInfo = null, $module_id = "", $system_id);
  
  
  判断是否有权限
  
  $pathInfo为需要判断的路由地址，若为null则取当前路由
  
  $module_id为版本id，例如：http:://www.monqin.com/v1/user/info
  
  则其中的V1为版本id，若无此版本控制，可直接为空
  
  $system_id 子系统ID
  
  返回为布尔值
  
  RoleRule::hasAuth($user_id, $pathInfo = null, $module_id, $system_id);
    
  为角色分配权限
  
  $role_id 需要分配权限的角色ID
  
  $rule_id_arr 规则节点ID数组  
  
  RoleRule::access($role_id, array $rule_id_arr);
   
   
   
  规则相关：
  
  根据ID 获取规则信息
  
  $rule_id 规则ID
  
  $fields 获取的字段，默认为所有，示例：
  
  ['rule_id','name']
  
  Rule::getRuleById($rule_id, $fields = "*");
  
  根据rule_id删除数据
  
  Rule::deleteByRuleId($rule_id);
  
  获取规则节点树型数据结构
  
  $type 类型，当type=0时$id为角色ID，否则为用户ID
  
  $id 角色或用ID，具体类型取决于$type
  
  $menu_show 是否是菜单，0：不是菜单，1：菜单，为null时为所有
  
  $status 规则节点状态，0：启用，1：不启用，当为null时为所有
  
  $pid 树顶id，若是获取整个树则为0，否则需要填入树顶点ID
  
  $fields 获取的字段，默认为null，只获取所需要的字段：
  
  ['system_id', 'rule_id', 'pid', 'name', 'title', 'href', 'icon', 'status', 'menu_show', 'sort']
  
  $is_fifter 是否过滤掉无权限的节点，1：过滤，0：不过滤
  
  $module_id   $module_id为版本id，例如：http:://www.monqin.com/v1/user/info，则其中的V1为版本id，若无此版本控制，可直接为空
 
  $sort 排序，1：降序，0：升序
  
  $system_id 子系统ID
 
  Rule::getRulesTree($id, $type = 0, $menu_show = null, $status = null, $pid = 0, $fields = null, $is_fifter = 0, $module_id='', $sort = 1, $system_id)
  
  
  根据规则ID获取其下所有节点id数组
  
  Rule::getSonByRuleId($rule_id);
  
  通过rule_id获取所有到顶点的ID
  
  Rule::getRootByRuleId($rule_id)
  
  新增规则节点
  
  $data  为需要添加的数据，示例：
  
   [  
     'system_id'=>1,
     'name'=>'rule/add', //后台执行的路由，必填
     'title'=>'权限管理',  //规则节点的标题，必填
     'href'=>'链接地址',    //前台链接地址，非必填
     'pid'=>'0', //上级ID
     'status'=>0,  //状态，0：启用，1：禁用
     'remark'=>'备注',  //非必填
     'sort'=>'0',//排序，非必填
     'menu_show'=>'0',//是否显示菜单，0：不显示，1：显示  
     'icon'=>'0',//菜单图标 非必填
   ];
   
   
  Rule::add($data)
  
  
  获取规则节点的分页列表
  
  $page 页码，默认为0
  
  $limit 每一页条数，默认为20
  
  $system_id 子系统ID，为null时则获取所有系统的规则节点
  
  Rule::listOfPagin($page, $limit = 20, $system_id = null)
  
  
  获取用户菜单
  
  $user_id 用户ID 必填
  
  $system_id 系统ID 必填
  
  Rule::getMenus($user_id, $system_id, $sort = 1, $pid = 0, $module_id = "", $menu_show = 1, $status = 0, $is_fifter = 1, $fields = null)
    
    
    
  用户相关：
  
  判断用户是否有效
  
  User::is_valid($user_id)
  
  根据id获取用户信息
  
  User::getUserById($userId, $fields = ['*'])
  
  通过用户ID获取所有权限节点名称数组
  
  User::getAccessByUserId($user_id, $status=0, $system_id)
  
  通过用户ID获取子系统所有所属角色的ID列表
  
  User::getRolesByUserId($user_id, $system_id)
  
  获取用户的分页列表
  
  $keywords 查询的关键词，提供username和real_name的模糊查找
  
  User::listOfPagin($page, $limit = 20, $sort = 1, $keywords = NULL)
  
  
  用户-角色相关：
  
  
  给用户分配一个角色
  
  $user_id 用户ID
  
  $role_id 角色ID  
  
  $system_id 系统ID 必填
  
  UserRole::setRoleByUserId($user_id, $role_id)
  
  
  为没有配置角色的用户配置一个基本角色
  
  调用此方法需在配置项里配置参数base_role_id的值，该值为你需要指定的一个角色ID
  
  UserRole::setBasicRole($user_id)
  
  
  为用户批量分配角色
  
  $user_id 用户ID
  
  $role_id_arr 角色ID 数组
  
  UserRole::setUserRole($user_id, $role_id_arr, $system_id)
  
   
六、如有任何疑问欢迎加入QQ群：338461207 进行交流

if you have any questions, welcome to join QQ group: 338461207
    