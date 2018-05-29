<?php

namespace Rbac\models;


/**
 * This is the model class for table "{{%access}}".
 *
 * @property integer $id
 * @property integer $rule_id
 * @property integer $role_id
 *
 * @property Role $role
 * @property Rule $rule
 */
class Access extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%access}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['rule_id', 'role_id'], 'required'],
            [['rule_id', 'role_id'], 'integer'],
            [['role_id'], 'exist', 'skipOnError' => true, 'targetClass' => Role::className(), 'targetAttribute' => ['role_id' => 'role_id']],
            [['rule_id'], 'exist', 'skipOnError' => true, 'targetClass' => Rule::className(), 'targetAttribute' => ['rule_id' => 'rule_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'rule_id' => 'Rule ID',
            'role_id' => 'Role ID',
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
     * 通过用户ID获取所有权限
     */
    public function getAccess($user_id)
    {
        try {
            $user_model = new Users();
            $access_arr = $user_model->getAccessByUserId($user_id);
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
    public function deleteByCondition($condition = array())
    {
        return Access::deleteAll($condition);
    }

    /**
     * @param $user_id
     * @return bool
     * 判断是否认证通过
     */
    public function auth($user_id, $pathInfo = null, $module_id)
    {

        try {

            if ($pathInfo == null) {
                $pathInfo = \Yii::$app->request->pathInfo;
            }

            if ($pathInfo) {
                $path_arr = @explode('/', $pathInfo);
                if (@$path_arr[0] == $module_id) {
                    unset($path_arr[0]);
                }

                $rule_reg = @strtolower(implode('/', $path_arr));
                if (in_array($rule_reg, $this->getAccess($user_id))) {
                    return true;
                }

            }

        } catch (\Exception $exception) {
            return false;
        }
        return false;
    }

    /**
     * @param null $user_id
     * 有权限则通过，无权限则返回权限不足
     *
     */

    public function access($user_id = null, $pathInfo = null, $module_id)
    {

        try {
            $user = Users::getUserById($user_id, ['status', 'delete_flag']);
            if ($user['status'] != 0) {
                return false;
            }
            if ($user['delete_flag'] != 0) {
                return false;
            }
            if (!$this->auth($user_id, $pathInfo, $module_id)) {
                return false;
            }
        } catch (\Exception $exception) {
            return false;
        }

    }


}
