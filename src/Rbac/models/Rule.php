<?php

namespace Rbac\models;

use Yii;
use yii\data\Pagination;

/**
 * This is the model class for table "rule".
 *
 * @property int $rule_id 自增ID
 * @property int $system_id 子系统唯一标志
 * @property string $name url（规则节点名称）
 * @property string $title 标题
 * @property string $href url链接
 * @property int $pid 上层ID
 * @property int $status 状态，0：启用，1：禁用
 * @property string $remark 备注
 * @property int $sort 权重
 * @property int $menu_show 是否显示菜单，0：不显示，1：显示
 * @property string $icon
 * @property string $create_at 创建时间
 * @property string $update_at 修改时间
 *
 * @property RoleRule[] $roleRules
 * @property System $system
 */
class Rule extends Base
{
    /**
     * {@inheritdoc}
     */
    public static $model_name = "rule";


    public static function tableName()
    {
        $model_parm = parent::getRbacParam();
        $user_model_parm = $model_parm[self::$model_name . '_model'];
        return $user_model_parm::tableName();
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['system_id', 'title'], 'required'],
            [['system_id', 'pid', 'status', 'sort', 'menu_show'], 'integer'],
            [['create_at', 'update_at'], 'safe'],
            [['name', 'icon'], 'string', 'max' => 60],
            [['title','name', 'remark'], 'string', 'max' => 50],
            [['href'], 'string', 'max' => 255],
            [['system_id'], 'exist', 'skipOnError' => true, 'targetClass' => System::className(), 'targetAttribute' => ['system_id' => 'system_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'rule_id' => 'Rule ID',
            'system_id' => 'System ID',
            'name' => 'Name',
            'title' => 'Title',
            'href' => 'Href',
            'pid' => 'Pid',
            'status' => 'Status',
            'remark' => 'Remark',
            'sort' => 'Sort',
            'menu_show' => 'Menu Show',
            'icon' => 'Icon',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRoleRules()
    {
        return $this->hasMany(RoleRule::className(), ['rule_id' => 'rule_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSystem()
    {
        return $this->hasOne(System::className(), ['system_id' => 'system_id']);
    }

    /**
     * @param $rule_id
     * @param string $fields
     * @return mixed
     * 根据ID 获取规则信息
     */
    public static function getRuleById($rule_id, $fields = ["*"])
    {
        return Rule::find()->select($fields)->where(["rule_id" => $rule_id])->one();
    }


    /**
     * @param $rule_id
     * @return false|int
     * @throws \Exception
     * @throws \yii\db\StaleObjectException
     * 根据rule_id删除
     */
    public static function deleteByRuleId($rule_id)
    {
        try {

            if (self::getRuleById($rule_id)) {
                //1、删除access记录
                RoleRule::deleteByCondition(["rule_id" => $rule_id]);
                //2、删除use_function记录
                // (new UsedFunctions())->deleteByCondition(["rule_id" => $rule_id]);
                //3、删除节点记录
                $query = Rule::findOne(["rule_id" => $rule_id])->delete();
                return $query;
            } else {
                return false;
            }
        } catch (\Exception $exception) {
            return false;
        }

    }

    /**
     * @param $id
     * @param int $type 当type=0为角色ID，否则为用户ID
     * @param null $menu_show
     * @param null $status
     * @param int $pid
     * @param null $fields
     * @param int $is_fifter
     * @param string $module_id
     * @param int $sort
     * @param $system_id
     * @param $del_empty_child_rule
     * @return array
     */
    public function getRulesTree($id, $type = 0, $menu_show = null, $status = null, $pid = 0, $fields = null, $is_fifter = 0, $module_id = '', $sort = 1, $system_id, $del_empty_child_rule = 0)
    {

        try {
            $rule_list = [];
            $condition = [];
            $condition['pid'] = $pid;//树顶，即从哪一层开始递归

            //type=1即ID为用户ID时，此ID必须大于0
            if (!($id > 0) && $type != 0) {
                return $rule_list;
            }

            if (!is_null($menu_show)) {
                $condition['menu_show'] = $menu_show;
            }

            if (!is_null($status)) {
                $condition['status'] = $status;
            }
            if ($system_id > 0) {
                $condition['system_id'] = $system_id;
            }

            $base_fields = ['system_id', 'rule_id', 'pid', 'name', 'title', 'href', 'icon', 'status', 'menu_show', 'sort'];

            if (!is_null($fields)) {
                $fields = array_merge($fields, $base_fields);
            } else {
                $fields = $base_fields;
            }


            //查询出所有的节点
            $rule_model = new Rule();
            $res = $rule_model->find()->where($condition)->select($fields)->all();

            //将结果集转为数组
            if ($res) {
                foreach ($res as $k => $v) {
                    if ($v) {
                        $rule_list[] = $v->toArray();
                    }

                }
            }

            //循环检查授权状态，并赋值
            if ($rule_list) {
                foreach ($rule_list as $s => $sv) {

                    //获取上级名称
                    if ($rule_list[$s]['pid'] > 0) {
                        $pid_name = Rule::find()->where(["rule_id" => $rule_list[$s]['pid']])->select(['title'])->one();
                        $rule_list[$s]['pid_title'] = $pid_name['title'];
                    } else {
                        $rule_list[$s]['pid_title'] = null;
                    }

                    //检查状态
                    if ($type == 0) {//当为0的时候为角色ID
                        //获取角色授权状态
                        $role = new Role();
                        $access_status = in_array($rule_list[$s]['name'], Role::getAccessByRoleId($id, "name", 0, $system_id)) == true ? 1 : 0;
                    } else {//否则为用户ID
                        //获取用户授权状态
                        $access_status = RoleRule::hasAuth($id, $rule_list[$s]['name'], $module_id, $system_id) == true ? 1 : 0;

//                        //判断是否为常用菜单
//                        if (UsedFunctions::find()->where(["user_id" => $id, "rule_id" => $rule_list[$s]['rule_id']])->select(["id"])->one()) {
//                            $rule_list[$s]['is_used_functions'] = 1;
//                        } else {
//                            $rule_list[$s]['is_used_functions'] = 0;
//                        }
                    }
                    $rule_list[$s]['access_status'] = $access_status;

                    //判断是否过滤
                    if ($is_fifter) {
                        if ($access_status != 1) {
                            unset($rule_list[$s]);
                        }
                    }

                }
            }

            sort($rule_list);
            foreach ($rule_list as $rk => $rv) {
                $rule_list[$rk]['child_rules'] = $this->getRulesTree($id, $type, $menu_show, $status, $rule_list[$rk]['rule_id'], $fields, $is_fifter, $module_id, $sort, $system_id);
                //判断是否为特殊菜单,true：展示，false：不展示  //
                if ($menu_show == 1) {
//                    //判断路由在当前时间内可否作为菜单显示
//                    if (in_array($rv['name'], Yii::$app->params['taskRoute'])) {
//                        if (!(Task::menuFilters($rv['name'], $id))) {//true：展示，false：不展示
//                            unset($rule_list[$rk]);
//                        }
//                    }

                    //删除子节点为空的树
                    if ($del_empty_child_rule) {

                        // 非最后一层级的目录，但用户没有该层级下子目录的权限，则移除该根目录。
                        $isParent = Rule::find()->select(['rule_id'])->where(['pid' => $rv['rule_id'], 'menu_show' => 1])->one();
                        if ($isParent && !count($rule_list[$rk]['child_rules'])) {
                            unset($rule_list[$rk]);
                        }

                        //若是pid为0的顶级树则当其没有任何子节点时也删除
                        // 非最后一层级的目录，但用户没有该层级下子目录的权限，则移除该根目录。
                        $isParent = Rule::find()->select(['rule_id'])->where(['pid' => $pid, 'menu_show' => 1])->one();
                        if ($isParent && !count($rule_list[$rk]['child_rules'])) {
                            unset($rule_list[$rk]);
                        }


                    }
                }
            }

            //排序处理
            if ($rule_list) {
                @usort($rule_list, function ($a, $b) use ($sort) {
                    if ($sort) {
                        //从大到小排列
                        return ($a['sort'] > $b['sort']) ? -1 : 1;
                    } else {
                        //从小到大排列
                        return ($a['sort'] < $b['sort']) ? -1 : 1;
                    }

                });
            }

        } catch (\Exception $exception) {
            return [];
        }

        return $rule_list;
    }

    /**
     * @param $rule_id
     * @param array $ids
     * @return array|null
     * 根据规则ID获取其下所有节点id
     */
    public static function getSonByRuleId($rule_id, &$ids = [])
    {
        $rules = Rule::find()->where(["pid" => $rule_id])->all();

        if ($rules) {
            foreach ($rules as $k => $v) {
                $ids[] = $v['rule_id'];
                self::getSonByRuleId($v['rule_id'], $ids);
            }

        }
        if (!in_array($rule_id, $ids)) {
            $ids[] = $rule_id;
        }
        return @array_flip(array_flip($ids));

    }


    /**
     * 获取模块列表（方案2）
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getModuleNameLists()
    {
        $rootModuleList = static::find()->where(['pid' => 0])->orderBy(['sort' => SORT_ASC])->all();

        $moduleList = ["常规-登录"];
        if ($rootModuleList) {
            foreach ($rootModuleList as $value) {
                $rootName = $value['title'];
                $rootId = $value['rule_id'];
                $childList = static::find()->where(['pid' => $rootId])->orderBy(['sort' => SORT_ASC])->all();
                if ($childList) {
                    foreach ($childList as $child) {
                        $moduleList[] = "{$rootName}-{$child['title']}";
                    }
                } else {
                    $moduleList[] = $rootName;
                }
            }
        }

        return $moduleList;
    }

    /**
     * @param $rule_id
     * @param array $pid_arr
     * @return array|null
     * 通过rule_id获取所有到顶点的ID
     */
    public static function getRootByRuleId($rule_id, &$pid_arr = [])
    {

        try {
            $rule = Rule::findOne(["rule_id" => $rule_id]);
            if ($rule) {
                if ($rule['pid'] > 0) {
                    $pid_arr[] = $rule['pid'];
                    self::getRootByRuleId($rule['pid'], $pid_arr);
                }
            }
        } catch (\Exception $exception) {
            return [];
        }
        return @array_flip(array_flip($pid_arr));
    }


    /**
     * @param $data
     * @return bool|void
     * 新增规则
     */
    public static function add($data)
    {

        try {
            if (!($data['system_id'] > 0)) {
                return false;
            }
            $rule = Rule::findOne(["name" => $data['name'], "title" => $data['title'], "href" => $data['href'], "system_id" => $data['system_id']]);
            if ($rule) {
                return false;
            }
            if ($data['title'] == "") {
                return false;
            }
            if (!in_array($data['status'], [self::getActiveVal(), self::getDisableVal("rule")])) {//状态，0：启用，1：禁用
                return false;
            }
            if (!in_array($data['menu_show'], [0, 1])) {//是否显示菜单，0：不显示，1：显示
                return false;
            }
            if ($data['pid'] > 0) {
                $rule_pid = Rule::findOne(["rule_id" => $data['pid']]);
                if (!$rule_pid) {
                    return false;
                }
            }

            $rule_model = new Rule();
            $temp = [
                "Rule" => $data
            ];
            if (!$rule_model->load($temp, 'Rule')) {
                return false;
            }
            if (!$rule_model->save()) {
                return false;
            } else {
                return true;
            }
        } catch (\Exception $exception) {
            return false;
        }
    }


    /**
     * @param $page
     * @param int $limit
     * @param null $system_id
     * @return array
     * 获取规则分页列表
     */
    public static function listOfPagin($page, $limit = 20, $system_id = null)
    {
        //构造查询
        $query = Rule::find();
        if ($system_id > 0) {
            $query = $query->where(["system_id" => $system_id]);
        }

        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count()]);

        //处理参数
        // $limit = input('limit', $pages->limit);
        // $page = intval(input('page', $pages->page));

        if (!($limit >= 0)) {
            $limit = 20;
        }

        //获取数据
        $list = $query->offset(($page - 1) * $limit)
            ->limit($limit)
            ->all();

        //返回数据
        return ['list' => $list, 'pages' => $pages];
    }


    /**
     * @param $user_id
     * @param $system_id
     * @param int $sort
     * @param int $pid
     * @param string $module_id
     * @param int $menu_show
     * @param int $status
     * @param int $is_fifter
     * @param array $fields
     * @param int $del_empty_child_rules
     * @return array
     * 获取用户菜单
     */
    public static function getMenus($user_id, $system_id, $sort = 1, $pid = 0, $module_id = "", $menu_show = 1, $status = null, $is_fifter = 1, $fields = null, $del_empty_child_rules = 0)
    {
        if (!($pid >= 0 && $system_id > 0)) {
            return [];
        }
        if ($status == null) {
            $status = self::getActiveVal();
        }
        $rule_model = new Rule();
        $menus = $rule_model->getRulesTree($user_id, 1, $menu_show, $status, $pid, $fields, $is_fifter, $module_id, $sort, $system_id, $del_empty_child_rules);
        return $menus;
    }


    /**
     * @param $rule_id
     * @return bool
     * 判断节点状态
     */
    public static function is_valid($rule_id)
    {
        $rule = Rule::getRuleById($rule_id, ['status']);
        if ($rule['status'] == self::getActiveVal()) {
            return true;
        } else {
            return false;
        }
    }


}
