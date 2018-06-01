<?php

namespace Rbac\models;

use Yii;

/**
 * This is the model class for table "role".
 *
 * @property int $role_id 自增ID
 * @property int $system_id 子系统唯一标志
 * @property string $name 角色名称
 * @property int $status 状态，0：启用，1：不启用
 * @property string $remark 备注
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 *
 * @property RequirementApprovalProcess[] $requirementApprovalProcesses
 * @property System $system
 * @property RoleRule[] $roleRules
 * @property UserRole[] $userRoles
 */
class Role extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{role}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['system_id', 'name'], 'required'],
            [['system_id', 'status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'remark'], 'string', 'max' => 50],
            [['system_id'], 'exist', 'skipOnError' => true, 'targetClass' => System::className(), 'targetAttribute' => ['system_id' => 'system_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'role_id' => 'Role ID',
            'system_id' => 'System ID',
            'name' => 'Name',
            'status' => 'Status',
            'remark' => 'Remark',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRequirementApprovalProcesses()
    {
        return $this->hasMany(RequirementApprovalProcess::className(), ['opreator_role_id' => 'role_id']);
    }


    /**
     * @return mixed
     * 获取当前模型的主键ID
     */
    public static function getRolePrimaryKey()
    {
        $id_arr = self::primaryKey();
        return $id_arr[0];
    }

    ////////////////////////////////////必须函数////////////////////////

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSystem()
    {
        return $this->hasOne(System::className(), ['system_id' => 'system_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRoleRules()
    {
        return $this->hasMany(RoleRule::className(), ['role_id' => 'role_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserRoles()
    {
        return $this->hasMany(UserRole::className(), ['role_id' => 'role_id']);
    }

    /**
     * @param $data
     * @param array $condition
     * @return int
     * 根据条件进行修改
     */
    public static function updateByCondition($condition, $data)
    {
        return Role::updateAll($data, $condition);
    }

    /**
     * @param $data
     * @param array $condition
     * @return int
     * 根据ID进行修改
     */
    public static function updateByRoleId($role_id, $data)
    {
        return self::updateByCondition(["role_id" => $role_id], $data);
    }

    /**
     * @param $role_id
     * @return int
     * 根据角色ID条件进行删除
     */
    public static function deleteByRoleId($role_id)
    {
        $tr = \Yii::$app->db->beginTransaction();
        try {
            //1、删除user_role下的记录
            UserRole::deleteByCondition(["role_id" => $role_id]);
            //2、删除user_role下的记录
            UserRole::deleteByCondition(["role_id" => $role_id]);
            //3、删除role表记录
            self::deleteByCondition(["role_id" => $role_id]);
            $tr->commit();
            return true;
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

    public static function deleteByCondition($condition = array())
    {
        return Role::deleteAll($condition);
    }


    /**
     * @param $role_id
     * @param $field
     * @param $status
     * @param $system_id
     * @return array|null
     * 获取该角色下的所有节点名
     */
    public static function getAccessByRoleId($role_id, $field = "name", $status = 0, $system_id)
    {
        $access = [];
        try {
            //状态，0：启用，1：不启用
            $condition = [
                "role_id" => $role_id,
                "status" => $status,
                "system_id" => $system_id,
            ];

            //获取该角色的基本信息
            $role = Role::find()->where($condition)->select(['role_id'])->one();
            if ($role) {
                $role_rule = $role->getRoleRules()->select(['rule_id'])->all();
                $rule_arr = [];
                if ($role_rule) {
                    foreach ($role_rule as $k => $v) {
                        if ($v['rule_id'] > 0) {
                            $rule_arr[] = $v['rule_id'];
                        }
                    }
                }
                $rule_arr = @array_flip(array_flip($rule_arr));//获取到所有该角色的所有节点ID
                $rules = Rule::find()->where(['status' => $status, "system_id" => $system_id])->select([$field])->andWhere(['in', 'rule_id', $rule_arr])->all();//获取符合条件的规则
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
     * @param $id
     * @param string $fields
     * @return mixed
     * 通过角色ID获取角色信息
     */
    public static function getRoleById($id, $fields = "*")
    {
        return Role::find()->select($fields)->where(["role_id" => $id])->find();
    }
    /**
     * @param $condition
     * @param string $fields
     * @return array|\yii\db\ActiveRecord[]
     * 通过条件进行获取
     */
    public static function getRoleByCondition($condition, $fields = "*")
    {
        return Role::find()->select($fields)->where($condition)->all();
    }

}
