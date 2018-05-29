<?php

namespace Rbac\models;

use \yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use Yii;

/**
 * This is the model class for table "{{%users}}".
 *
 * @property int $user_id 自增ID
 * @property string $user_name 用户名
 * @property string $email 邮箱
 * @property string $phone 电话
 * @property string $avatar 头像
 * @property int $delete_flag 删除标志位，0：未删除，1：已删除
 * @property int $status 用户状态，0：启用，1：禁用 ，2：禁用
 * @property string $real_name 姓名
 * @property int $operator_id 操作人
 * @property string $remark 备注
 * @property string $create_time 创建时间
 * @property string $update_time 修改时间
 * @property UserRole[] $userRoles
 */
class Users extends \yii\db\ActiveRecord
{

    // 查询中得出的聚合结果，如果不在model中声明，则永远查不出来
    public $person_num;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%users}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_name','operator_id'], 'required'],
            [['operator_id', 'delete_flag', 'status'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['user_name', 'email'], 'string', 'max' => 45],
            [['phone', 'job_number'], 'string', 'max' => 20],
            [['avatar'], 'string', 'max' => 255],
            [['real_name'], 'string', 'max' => 50],
            [['remark'], 'string', 'max' => 150],
            [['user_name'], 'unique'],  //账号唯一
            ['email', 'email'],  //email的正则
            ['phone', 'match', 'pattern' => '/^[1][34578][0-9]{9}$/'],  //phone的正则
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'user_name' => 'User Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'avatar' => 'Avatar',
            'delete_flag' => 'Delete Flag',
            'status' => 'Status',
            'real_name' => 'Real Name',
            'operator_id' => 'Operator ID',
            'remark' => 'Remark',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }



    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserRoles()
    {
        return $this->hasMany(UserRole::className(), ['user_id' => 'user_id']);
    }



    // 时间自动更新
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    # 创建之前
                    ActiveRecord::EVENT_BEFORE_INSERT => ['update_time', 'create_time'],
                    # 修改之前
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['update_time']
                ],
                #设置默认值
                'value' => date('Y-m-d H:i:s')
            ]
        ];
    }

    /**
     * @param $user_id
     * @return array|null
     * 通过用户ID获取所有所属角色的ID
     */
    public function getRolesByUserId($user_id)
    {

        $role_arr = [];
        try {
            if ($user_id > 0) {
                $user = Users::getUserById($user_id, ['user_id']);
                if ($user) {
                    $roles = $user->getUserRoles()->select(['role_id'])->asArray()->all();
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

    /**
     * @param $user_id
     * @return array
     * 通过用户ID获取所有权限节点名称
     */
    public function getAccessByUserId($user_id)
    {

        $access = [];
        $access_temp = [];
        try {
            if ($user_id > 0) {
                $role_model = new Role();
                $roles = $this->getRolesByUserId($user_id);
                if ($roles) {
                    foreach ($roles as $k => $v) {
                        if ($v > 0) {
                            $rules_arr = $role_model->getAccessByRoleId($v);
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
     * 根据id获取用户信息
     * @param $userId
     * @param $fields
     * @return array|null|ActiveRecord
     */
    public static function getUserById($userId, $fields = ['*'])
    {
        return static::find()->where(['user_id' => $userId])->select($fields)->one();
    }


    /**
     * 根据用户账号获取用户信息
     * @param $userName
     * @return static
     */
    public static function getUserByName($userName)
    {
        return static::findOne(['user_name' => $userName]);
    }



    /**
     *  根据条件查询用户列表
     * @param $userName  string 账号
     * @param $realName  string 用户中文姓名
     * @param $roleId  int 角色id
     * @param $departmentId int 部门id
     * @param $page   int 页码
     * @param $limit  int 分页数量
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getUserListByConditions($userName, $realName, $roleId, $departmentId, $page, $limit)
    {
        // 查询用户列表信息并分页
        $sql = static::find()
            ->select(['a.user_id as user_id', 'user_name', 'real_name', 'email', 'remark', 'update_time', 'a.status as status'])
            ->from(static::tableName() . ' AS a');

        if ($roleId) {
            $sql = $sql->leftJoin(UserRole::tableName() . ' AS b', 'a.user_id = b.user_id')
                ->where(['a.delete_flag' => 0])
                ->andWhere(['b.role_id' => $roleId]);
        } else {
            $sql = $sql->where(['a.delete_flag' => 0]);
        }

        if ($userName) {
            $sql = $sql->andWhere(['like', 'user_name', "%{$userName}%", false]);
        }

        if ($realName) {
            $sql = $sql->andWhere(['like', 'real_name', "%{$realName}%", false]);
        }

        if ($departmentId) {
            $departmentModel = new Depmart();
            $departments = $departmentModel->getChildDepmartId($departmentId);
            $sql = $sql->andWhere(['in', 'depmart_id', $departments]);
        }


        // 分页
        $countQuery = clone $sql;
        $totalCount = $countQuery->count();
        $lists = $sql->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy(['user_id' => SORT_ASC])
            ->all();

        // 根据用户分页信息列表再查询用户角色信息
        $userList = [];
        for ($i = 0; $i < count($lists); $i++) {
            $userList[] = [
                'user_id' => $lists[$i]->user_id,
                'user_name' => $lists[$i]->user_name,
                'real_name' => $lists[$i]->real_name,
                'email' => $lists[$i]->email,
                'remark' => $lists[$i]->remark,
                'update_time' => $lists[$i]->update_time,
                'status' => $lists[$i]->status,
                'roleNameList' => UserRole::getUserRoleNameByUser($lists[$i]->user_id),
            ];
        }

        return array(
            'lists' => $userList,
            'totalCount' => $totalCount,
            'currentPage' => $page,
            'limit' => $limit,
        );
    }
}
