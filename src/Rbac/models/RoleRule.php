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
class RoleRule extends \yii\db\ActiveRecord
{
    public $admin_id = [];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%role_rule}}';
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
            $access_arr = User::getAccessByUserId($user_id, $system_id);
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
        $role_rule_model = new RoleRule();
        if (in_array($user_id, $role_rule_model->admin_id)) {
            return true;
        } else {
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
     */


    public static function auth($user_id, $pathInfo = null, $module_id, $system_id)
    {

        try {
            $user = User::getUserById($user_id);
            if ($user) {

                if ($user['status'] != 0) {
                    return 402;
                }
                if ($user['delete_flag'] != 0) {
                    return 403;
                }


            } else {
                return 401;
            }


            //判断是否是超级管理员
            if (self::is_super_admin($user_id)) {
                return 400;
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


}
