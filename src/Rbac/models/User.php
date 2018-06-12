<?php

namespace Rbac\models;

use Yii;
use yii\data\Pagination;

/**
 * This is the model class for table "{{%users}}".
 *
 * @property int $user_id 自增ID
 * @property int $status 用户状态，10：启用，0：禁用
 * @property UserRole[] $userRoles
 */
class User extends \yii\db\ActiveRecord
{
    /**
     * 获取配置的Rbac参数
     */
    public static function getRbacParam()
    {
        return Yii::$app->params['rbac_manager'];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        $model_parm = self::getRbacParam();
        $user_model_parm = $model_parm['user_model'];
        return $user_model_parm::tableName();
    }


    /**
     * @return mixed
     * 获取用户状态
     */
    public static function getStatusVal()
    {
        $parm = self::getRbacParam();
        return $parm['user_status'];
    }

    /**
     * @return mixed
     * 获取删除的值
     */

    public static function getDelVal()
    {
        $status_val = self::getStatusVal();
        return $status_val['status_deleted'];
    }

    /**
     * @return mixed
     * 获取状态的值
     */
    public static function getActiveVal()
    {
        $status_val = self::getStatusVal();
        return $status_val['status_active'];
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            self::getUserPrimaryKey() => 'User ID',
            'status' => 'Status',

        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserRoles()
    {
        return $this->hasMany(UserRole::className(), ["user_id" => User::getUserPrimaryKey()]);
    }

    /**
     * @param $user_id
     * @return bool
     * 判断用户是否有效
     */
    public static function is_valid($user_id)
    {
        try {
            $user = self::getUserById($user_id, ['status']);
            if ($user['status'] == self::getActiveVal()) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $exception) {
            return false;
        }
    }


    /**
     * @return mixed
     * 获取当前模型的主键ID
     */
    public static function getUserPrimaryKey()
    {
        $id_arr = self::primaryKey();
        return $id_arr[0];
    }

    /**
     * 根据id获取用户信息
     * @param $userId
     * @param $fields
     * @return array|null|ActiveRecord
     */
    public static function getUserById($userId, $fields = ['*'])
    {
        $id = self::getUserPrimaryKey();
        $condition = [
            $id => $userId,
        ];
        return static::find()->where($condition)->select($fields)->one();
    }

    /**
     * @param $user_id
     * @param $system_id
     * @return array
     * 通过用户ID获取所有权限节点名称
     */
    public static function getAccessByUserId($user_id, $status, $system_id)
    {

        $access = [];
        $access_temp = [];
        try {
            if ($user_id > 0) {
                if (System::is_valid($system_id)) {
                    $roles = self::getRolesByUserId($user_id, $system_id);//通过用户id获取该用户在该系统ID所拥有的所有的角色
                    if ($roles) {
                        foreach ($roles as $k => $v) {
                            if ($v > 0) {
                                //判断角色状态
                                $rules_arr = Role::getAccessByRoleId($v, "name", $status, $system_id);//获取所有角色的名称
                                if ($rules_arr) {
                                    $access[] = $rules_arr;
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
    public static function getRolesByUserId($user_id, $system_id)
    {
        $role_arr = [];
        $user_primary_id = self::getUserPrimaryKey();
        try {
            if ($user_id > 0) {
                if (System::is_valid($system_id)) {
                    $user = User::getUserById($user_id, [$user_primary_id]);
                    if ($user) {
                        $role_prikey_id = Role::getRolePrimaryKey();
                        $roles = $user->getUserRoles()->select([$role_prikey_id])->asArray()->all();
                        if ($roles) {
                            foreach ($roles as $k => $v) {
                                if ($v['role_id'] > 0) {
                                    //判断改角色是否在改系统ID下
                                    if (Role::findOne(["role_id" => $v['role_id'], "system_id" => $system_id])) {
                                        $role_arr[] = $v['role_id'];
                                    }

                                }
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

    //获取用户分页列表
    public static function listOfPagin($page, $limit = 20, $sort = 1, $keywords = NULL)
    {
        //构造查询
        $query = User::find();
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count()]);

        //处理参数
        // $limit = input('limit', $pages->limit);
        // $page = intval(input('page', $pages->page));

        if (!($limit >= 0)) {
            $limit = 20;
        }

        if ($sort == 1) {
            $query->orderBy([self::getUserPrimaryKey() => SORT_DESC]);
        } else {
            $query->orderBy([self::getUserPrimaryKey() => SORT_ASC]);
        }

        if (!empty($keywords)) {
            $query->andWhere("concat(username,'_',real_name) like '%$keywords%'");
        }

        //获取数据
        $list = $query->offset(($page - 1) * $limit)
            ->limit($limit)
            ->asArray()
            ->all();

        if ($list) {
            foreach ($list as $k => $v) {
                $roles = [];
                //获取每个用户对应的角色信息
                if ($v) {
                    $roles_id = UserRole::find()->where(["user_id" => $v])->all();
                    $role_temp = [];
                    foreach ($roles_id as $rk => $rv) {
                        $role_temp[] = Role::findOne(["role_id" => $rv['role_id']]);
                    }
                    $list[$k]['role_list'] = $role_temp;
                }
            }

        }

        //返回数据
        return apiSuccess(['list' => $list, 'pages' => $pages]);
    }
}

