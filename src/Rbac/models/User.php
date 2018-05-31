<?php

namespace Rbac\models;

use Yii;

/**
 * This is the model class for table "{{%users}}".
 *
 * @property int $user_id 自增ID
 * @property int $delete_flag 删除标志位，0：未删除，1：已删除
 * @property int $status 用户状态，0：启用，1：禁用 ，2：禁用
 * @property UserRole[] $userRoles
 */
class User extends \yii\db\ActiveRecord
{

    public $table_name = "user";

    public function getTableName()
    {
        return strtolower($this->table_name);
    }


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        $model = new User();
        return $model->table_name;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status'], 'integer'],
            [['delete_flag'], 'integer'],

        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'delete_flag' => 'Delete Flag',
            'status' => 'Status',

        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserRoles()
    {
        return $this->hasMany(UserRole::className(), ['user_id' => 'user_id']);
    }


    /**
     * 根据id获取用户信息
     * @param $userId
     * @param $fields
     * @return array|null|ActiveRecord
     */
    public static function getUserById($userId, $fields = ['*'])
    {
        $id = User::primaryKey();
        $condition = [
            "$id" => $userId,
        ];
        return static::find()->where($condition)->select($fields)->one();
    }

    /**
     * @param $user_id
     * @param $system_id
     * @return array
     * 通过用户ID获取所有权限节点名称
     */
    public static function getAccessByUserId($user_id, $system_id)
    {

        $access = [];
        $access_temp = [];
        try {
            if ($user_id > 0) {
                $roles = self::getRolesByUserId($user_id, $system_id);//通过用户id获取该用户在该系统ID所拥有的所有的角色
                if ($roles) {
                    foreach ($roles as $k => $v) {
                        if ($v > 0) {
                            $rules_arr = Role::getAccessByRoleId($v, "name", 0, $system_id);
                            if ($rules_arr) {
                                $access[] = $rules_arr;
                            }

                        }

                    }
                }
            }

            if ($access) {
                foreach ($access as $ak => $av) {
                    if (is_array($av) && !empty($av)) {
                        foreach ($av as $s_v) {
                            if ($s_v) {
                                $access_temp[] = $s_v;
                            }
                        }
                    }
                }
            }

        } catch (\Exception $exception) {
            return $access_temp;
        }
        return $access_temp;

    }

    /**
     * @param $user_id
     * @param $system_id
     * @return array|null
     * 通过用户ID获取子系统所有所属角色的ID列表
     */
    public function getRolesByUserId($user_id, $system_id)
    {
        $role_arr = [];
        try {
            if ($user_id > 0) {
                $user = User::getUserById($user_id, [User::primaryKey()]);
                if ($user) {
                    $condition = [
                        "status" => 0,
                        "system_id" => $system_id,
                    ];
                    $roles = $user->getUserRoles()->where($condition)->select([Role::primaryKey()])->asArray()->all();
                    if ($roles) {
                        foreach ($roles as $k => $v) {
                            if ($v['role_id'] > 0) {
                                $role_arr[] = $v['role_id'];
                            }
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
            return $role_arr;
        }
        return @array_flip(array_flip($role_arr));
    }

}

