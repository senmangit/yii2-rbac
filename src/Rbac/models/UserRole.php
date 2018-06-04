<?php

namespace Rbac\models;

use Yii;

/**
 * This is the model class for table "user_role".
 *
 * @property int $id 自增ID
 * @property int $user_id 用户ID
 * @property int $role_id 角色ID
 *
 * @property Role $role
 * @property User $user
 */
class UserRole extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{user_role}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'role_id'], 'required'],
            [['user_id', 'role_id'], 'integer'],
            [['role_id'], 'exist', 'skipOnError' => true, 'targetClass' => Role::className(), 'targetAttribute' => ['role_id' => 'role_id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
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
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }


    /***
     * @param $condition
     * @return false|int
     * @throws \Exception
     * @throws \yii\db\StaleObjectException
     */
    public static function deleteByCondition($condition = array())
    {
        return UserRole::deleteAll($condition);
    }

    /**
     * @param $user_id
     * @param $role_id
     * @return bool
     * 给用户新增角色
     */
    public static function setRoleByUserId($user_id, $role_id)
    {
        $user_model = new User();
        $roles = $user_model->getRolesByUserId($user_id);
        if (!in_array($role_id, $roles)) {
            //新增
            $user_role_model = new UserRole();
            $user_role_model->role_id = $role_id;
            $user_role_model->user_id = $user_id;
            return $user_role_model->save();
        }

    }


    /**
     * 根据用户id查询到用户所有的角色名称
     * @param $userId
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getUserRoleNameByUser($userId)
    {
        return static::find()
            ->select(['name', 'a.role_id AS role_id'])
            ->from(static::tableName() . ' AS a')
            ->leftJoin(Role::tableName() . ' AS b', 'a.role_id = b.role_id')
            ->where(['a.user_id' => $userId, 'b.status' => 0])
            ->asArray()
            ->all();
    }
}
