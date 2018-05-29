<?php

namespace Rbac\models;

use Yii;

/**
 * This is the model class for table "{{%rule}}".
 *
 * @property integer $rule_id
 * @property string $name
 * @property string $title
 * @property integer $pid
 * @property integer $status
 * @property string $remark
 * @property integer $sort
 * @property integer $menu_show
 * @property string $icon
 * @property string $href
 * @property string $create_time
 * @property string $update_time
 *
 * @property Access[] $accesses
 * @property UsedFunctions[] $usedFunctions
 */
class Rule extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%rule}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'title', 'status', 'menu_show'], 'required'],
            [['pid', 'status', 'sort', 'menu_show'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['name', 'icon'], 'string', 'max' => 60],
            [['title', 'remark'], 'string', 'max' => 50],
            [['href'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'rule_id' => 'Rule ID',
            'name' => 'Name',
            'title' => 'Title',
            'pid' => 'Pid',
            'status' => 'Status',
            'remark' => 'Remark',
            'href' => 'Href',
            'sort' => 'Sort',
            'menu_show' => 'Menu Show',
            'icon' => 'Icon',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccesses()
    {
        return $this->hasMany(Access::className(), ['rule_id' => 'rule_id']);
    }

    /**
     * @param $role_id
     * @return false|int
     * @throws \Exception
     * @throws \yii\db\StaleObjectException
     * 根据rule_id删除
     */
    public function deleteByRuleId($rule_id)
    {
        $tr = Yii::$app->db->beginTransaction();
        try {

            //1、删除access记录
            (new Access())->deleteByCondition(["rule_id" => $rule_id]);

            //2、删除use_function记录
            (new UsedFunctions())->deleteByCondition(["rule_id" => $rule_id]);

            //3、删除节点记录
            $query = Rule::findOne($rule_id)->delete();

            $tr->commit();
            return $query;

        } catch (\Exception $exception) {
            $tr->rollBack();
            return false;
        }

    }

    /**
     * @param $id
     * @param $type 当type=0为角色ID，否则为用户ID
     * @param null $menu_show
     * @param null $status
     * @param int $pid
     * @param $module_id
     * @return array
     * 获取权限节点树
     */
    public function getRulesTree($id, $type = 0, $menu_show = null, $status = null, $pid = 0, $fields = null, $is_fifter = 0, $module_id, $sort = 1)
    {

        try {
            $rule_list = [];
            $condition = [];
            $condition['pid'] = $pid;

            //type=1即ID为用户ID时，此ID必须大于0
            if (!$id > 0 && $type != 0) {
                return $rule_list;
            }

            if ($menu_show != null) {
                $condition['menu_show'] = $menu_show;
            }

            if ($status != null) {
                $condition['status'] = $status;
            }

            $base_fields = ['rule_id', 'pid', 'name', 'title', 'href', 'icon', 'status', 'menu_show', 'sort'];

            if ($fields != null) {
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
                        $access_status = in_array($rule_list[$s]['name'], $role->getAccessByRoleId($id)) == true ? 1 : 0;
                    } else {//否则为用户ID
                        //获取用户授权状态
                        $access_model = new Access();
                        $access_status = $access_model->auth($id, $rule_list[$s]['name'], $module_id) == true ? 1 : 0;
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
                $rule_list[$rk]['child_rules'] = $this->getRulesTree($id, $type, $menu_show, $status, $rule_list[$rk]['rule_id'], $fields, $is_fifter, $module_id, $sort);
                //判断是否为特殊菜单,true：展示，false：不展示  //
                if ($menu_show == 1) {

                    if ($type == 1) {
                        // 非最后一层级的目录，但用户没有该层级下子目录的权限，则移除该根目录。
                        $isParent = Rule::findOne(['pid' => $rv['rule_id'], 'menu_show' => 1]);
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
    public function getSonByRuleId($rule_id, &$ids = [])
    {
        $rules = Rule::find()->where(["pid" => $rule_id])->all();

        if ($rules) {
            foreach ($rules as $k => $v) {
                $ids[] = $v['rule_id'];
                $this->getSonByRuleId($v['rule_id'], $ids);
            }

        }

        if (!in_array($rule_id, $ids)) {
            $ids[] = $rule_id;
        }
        return @array_flip(array_flip($ids));

    }


    /**
     * 查询首页-操作日志下拉列表（方案2）
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
            $rule = Rule::findOne($rule_id);
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

}
