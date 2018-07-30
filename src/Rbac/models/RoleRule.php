<?php

namespace Rbac\models;

use Yii;

/**
 * This is the model class for table "role_rule".
 *
 * @property int $id 自增ID
 * @property int $role_id 角色ID
 * @property int $rule_id 规则节点ID
 *
 * @property Role $role
 * @property Rule $rule
 */
class RoleRule extends Base
{
    /**
     * {@inheritdoc}
     */
    public static $model_name = "role_rule";

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
            [['role_id', 'rule_id'], 'required'],
            [['role_id', 'rule_id'], 'integer'],
            [['role_id'], 'exist', 'skipOnError' => true, 'targetClass' => Role::className(), 'targetAttribute' => ['role_id' => 'role_id']],
            [['rule_id'], 'exist', 'skipOnError' => true, 'targetClass' => Rule::className(), 'targetAttribute' => ['rule_id' => 'rule_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'role_id' => 'Role ID',
            'rule_id' => 'Rule ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRole()
    {
        return $this->hasOne(Role::className(), ['role_id' => 'role_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRule()
    {
        return $this->hasOne(Rule::className(), ['rule_id' => 'rule_id']);
    }


    /**
     * @param $user_id
     * @param $system_id
     * @return array
     * 通过用户ID获取所有权限
     */
    public static function getAccess($user_id, $system_id)
    {
        try {
            $access_arr = User::getAccessByUserId($user_id, self::getActiveVal(), $system_id);
        } catch (\Exception $exception) {
            $access_arr = [];
        }
        return $access_arr;
    }

    /**
     * @param $condition
     * @return int
     * 根据条件进行删除
     */
    public static function deleteByCondition($condition = array())
    {
        return RoleRule::deleteAll($condition);
    }


    /**
     * @param $user_id
     * @return bool
     * 判断是否为超管
     */
    public static function is_super_admin($user_id)
    {
        try {
            $parms = Yii::$app->params['rbac_manager'];
            $role_rule_model = new RoleRule();
            if (in_array($user_id, $parms["super_admin_id"])) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $exception) {
            return false;
        }
    }


    /**
     * @param $user_id
     * @param $pathInfo
     * @param $module_id
     * @param $system_id
     * @return bool
     * 判断是否认证通过
     *
     * 400：已授权
     * 401:该用户未找到
     * 402：该用户状态异常
     * 403：该用户已被删除
     * 404：授权异常
     * 405：权限不足
     * 406：该系统已经被禁用
     */


    public static function auth($user_id, $pathInfo = null, $module_id = "", $system_id)
    {

        try {
            //判断用户是否有效
            if (!User::is_valid($user_id)) {
                return 402;
            }
            //判断是否是超级管理员
            if (self::is_super_admin($user_id)) {
                return 400;
            }

            //判断是否系统禁用了
            if (!System::is_valid($system_id)) {
                return 406;
            }


            if ($pathInfo == null) {
                $pathInfo = Yii::$app->request->pathInfo;
            }

            if ($pathInfo) {
                $path_arr = @explode('/', $pathInfo);
                if (@$path_arr[0] == $module_id) {
                    unset($path_arr[0]);
                }
                $rule_reg = @strtolower(implode('/', $path_arr));

                $parms = \Yii::$app->params;

                //判断是否在免检之列
                try {
                    $except_route_config_arr = $parms['rbac_manager']['except_route'];
                    if (in_array($rule_reg, $except_route_config_arr)) {
                        return 400;
                    }
                } catch (\Exception $e) {
                    //不做任何处理
                }


                if (in_array($rule_reg, self::getAccess($user_id, $system_id))) {//判断该路由是否在该用户的所属权限列表内
                    return 400;
                }
            }

        } catch (\Exception $exception) {
            return 404;
        }
        return 405;
    }


    /**
     * @param $user_id
     * @param null $pathInfo
     * @param $module_id
     * @param $system_id
     * @return bool
     * 判断是否有权限
     */
    public static function hasAuth($user_id, $pathInfo = null, $module_id, $system_id)
    {
        try {
            if (self::auth($user_id, $pathInfo, $module_id, $system_id) == 400) {
                return true;
            }
        } catch (\Exception $exception) {
            return false;
        }

        return false;
    }

    /**
     * 分配权限
     */
    public static function access($role_id, array $rule_id_arr)
    {
        $transaction = RoleRule::getDb()->beginTransaction();
        try {
            $rule_ids_temp_arr = [];
            $rule_model = new Rule();
            if (!is_array($rule_id_arr)) {
                return false;
            } else {
                if ($rule_id_arr) {
                    foreach ($rule_id_arr as $k => $v) {
                        if ($v > 0) {
                            if (!$rule_model::findOne(["rule_id" => $v])) {
                                return false;
                            } else {
                                $rule_ids = Rule::getRootByRuleId($v);
                                foreach ($rule_ids as $rk => $rv) {
                                    $rule_ids_temp_arr[] = $rv;
                                }
                            }
                        } else {
                            return false;
                        }
                    }
                }
            }
            $rule_id_arr = array_flip(array_flip(array_merge($rule_ids_temp_arr, $rule_id_arr)));

            if (!($role_id > 0)) {
                return false;
            }
            $role = Role::findOne(["role_id" => $role_id]);

            if ($role) {
                $rule_list = Role::getAccessByRoleId($role_id, "rule_id", self::getActiveVal(), $role['system_id']);
                $result_add = array_diff($rule_id_arr, $rule_list);//需要增加的
                $result_del = array_diff($rule_list, $rule_id_arr);//需要减少的
//                    //删除差异
//              Access::deleteAll('role_id = :role_id AND rule_id in (:rule_id)', [':role_id' => $role_id, ':rule_id' => $result_del]);
                RoleRule::deleteAll('role_id = :role_id ', [':role_id' => $role_id]);
                //新增
                if ($rule_id_arr) {
                    foreach ($rule_id_arr as $k => $v) {
                        $access_model = new RoleRule();
                        $access_model->role_id = $role_id;
                        $access_model->rule_id = $v;
                        $access_model->save();
                    }
                }

                $all_access_add = [];
                foreach ($result_add as $rk => $rv) {
                    $rule_info = Rule::find()->where(["rule_id" => $rv])->select(['title'])->one();
                    $all_access_add[] = $rule_info['title'];
                }
                $all_access_del = [];
                foreach ($result_del as $rk => $rv) {
                    $rule_info = Rule::find()->where(["rule_id" => $rv])->select(['title'])->one();
                    $all_access_del[] = $rule_info['title'];
                }

//                //记录新增日志
//                if ($all_access_add) {
//                    $all_access_str = @implode("、", $all_access_add);
//                    $this->addActionLogs(\Yii::$app->params['actionLogOp']['add'], "角色：{$role['name']} <br> 权限：{$all_access_str}");
//                }
//
//
//                //记录删除日志
//                if ($all_access_del) {
//                    $all_access_str = @implode("、", $all_access_del);
//                    $this->addActionLogs(\Yii::$app->params['actionLogOp']['del'], "角色：{$role['name']} <br> 权限：{$all_access_str}");
//                }


                $transaction->commit();
                return true;
            } else {
                return false;
            }


        } catch (\Exception $e) {
            $transaction->rollBack();
            return false;

        } catch (\Throwable $e) {
            $transaction->rollBack();
            return false;
        }


    }
}
