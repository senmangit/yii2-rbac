<?php

namespace Rbac\models;

use Yii;

/**
 * This is the model class for table "{{%role}}".
 *
 * @property integer $role_id
 * @property string $name
 * @property integer $status
 * @property string $remark
 * @property string $create_time
 * @property string $update_time
 *
 * @property Access[] $accesses
 * @property UserRole[] $userRoles
 */
class Role extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%role}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['status'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['name', 'remark'], 'string', 'max' => 50],
            [['name'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'role_id' => 'Role ID',
            'name' => 'Name',
            'status' => 'Status',
            'remark' => 'Remark',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccesses()
    {
        return $this->hasMany(Access::className(), ['role_id' => 'role_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserRoles()
    {
        return $this->hasMany(UserRole::className(), ['role_id' => 'role_id']);
    }

    public function getRole($condition, $field = "*")
    {
        return Role::find()->where($condition)->select($field)->one();
    }

    /**
     * @param $data
     * @param array $condition
     * @return int
     * 根据条件进行修改
     */
    public function updateByCondition($condition, $data)
    {
        return Role::updateAll($data, $condition);
    }

    /**
     * @param $data
     * @param array $condition
     * @return int
     * 根据ID进行修改
     */
    public function updateByRoleId($role_id, $data)
    {
        return $this->updateByCondition(["role_id" => $role_id], $data);
    }

    /**
     * @param $role_id
     * @return int
     * 根据角色ID条件进行删除
     */
    public function deleteByRoleId($role_id)
    {
        $tr = \Yii::$app->db->beginTransaction();
        try {
            //1、删除access下的记录
            (new Access())->deleteByCondition(["role_id" => $role_id]);
            //2、删除user_role下的记录
            (new UserRole())->deleteByCondition(["role_id" => $role_id]);
            //3、删除role表记录
            $query = $this->deleteByCondition(["role_id" => $role_id]);
            $tr->commit();
            return $query;
        } catch (\Exception $exception) {
            $tr->rollBack();
            return false;
        }
    }

    /**
     * @param $role_id
     * @return int
     * 根据条件进行删除
     */

    public function deleteByCondition($condition = array())
    {
        return Role::deleteAll($condition);
    }


    /**
     * @param $role_id
     * @return array|null
     * 获取该角色下的所有节点名
     */
    public function getAccessByRoleId($role_id, $field = "name", $status = 0)
    {
        $access = [];
        try {
            //状态，0：启用，1：不启用
            $condition = [
                "role_id" => $role_id,
                "status" => $status,
            ];
            $role_model = new Role();
            $role = $role_model->getRole($condition, ['role_id']);

            if ($role) {
                $role_rule = $role->getAccesses()->select(['rule_id'])->all();
                $rule_arr = [];
                if ($role_rule) {
                    foreach ($role_rule as $k => $v) {
                        if ($v['rule_id'] > 0) {
                            $rule_arr[] = $v['rule_id'];
                        }
                    }
                }
                $rule_arr = @array_flip(array_flip($rule_arr));//获取到所有该角色的所有节点ID
                $rules = Rule::find()->where(['status' => 0])->select([$field])->andWhere(['in', 'rule_id', $rule_arr])->all();//获取符合条件的规则

                if ($rules) {
                    foreach ($rules as $rk => $rv) {
                        $access[] = $rv[$field];
                    }
                }
            }
        } catch (\Exception $exception) {
            return [];
        }

        return @array_flip(array_flip($access));

    }


    /**
     * 获取角色列表（下拉列表框）
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getRoleList()
    {
        return static::find()
            ->select(['role_id', 'name'])
            ->where(['status' => 0])
            ->all();
    }
}
