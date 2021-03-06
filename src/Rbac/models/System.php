<?php

namespace Rbac\models;

use Yii;
use yii\data\Pagination;

/**
 * This is the model class for table "system".
 *
 * @property int $system_id
 * @property string $name 系统名称
 * @property string $remark 系统描述
 * @property string $url 系统入口
 * @property int $delete_flag 删除标识，0：未删除，1：删除
 * @property int $status 状态：0：正常启用，1暂停使用
 *
 * @property Role[] $roles
 * @property Rule[] $rules
 */
class System extends  Base
{

    /**
     * {@inheritdoc}
     */
    public static $model_name = "system";


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
            [['name'], 'required'],
            [['delete_flag', 'status'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['name'], 'string', 'max' => 100],
            [['remark'], 'string', 'max' => 300],
            [['url'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'system_id' => 'System ID',
            'name' => 'Name',
            'remark' => 'Remark',
            'url' => 'Url',
            'delete_flag' => 'Delete Flag',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRoles()
    {
        return $this->hasMany(Role::className(), ['system_id' => 'system_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRules()
    {
        return $this->hasMany(Rule::className(), ['system_id' => 'system_id']);
    }

    /**
     * @param $page
     * @param int $limit
     * @param array $condition
     * @param int $sort
     * @return array
     * 获取系统分页列表
     */

    public static function listOfPagin($page, $limit = 20, $condition = [], $sort = 0)
    {
        //构造查询
        $query = System::find();
        if ($condition) {
            $query = $query->where($condition);
        }
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count()]);

        //处理参数
        // $limit = input('limit', $pages->limit);
        // $page = intval(input('page', $pages->page));

        if (!($limit >= 0)) {
            $limit = 20;
        }


        if ($sort == 1) {
            $query->orderBy(['sort' => SORT_DESC]);
        } else {
            $query->orderBy(['sort' => SORT_ASC]);
        }


        //获取数据
        $list = $query->offset(($page - 1) * $limit)
            ->limit($limit)
            ->all();

        //返回数据
        return ['list' => $list, 'pages' => $pages];
    }


    /**
     * 获取系统列表（下拉列表框）
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getSystemList($condition, $fields = "*", $sort = 0)
    {
        $query = static::find()
            ->select($fields)
            ->where($condition);

        if ($sort == 1) {
            $query->orderBy(['sort' => SORT_DESC]);
        } else {
            $query->orderBy(['sort' => SORT_ASC]);
        }

        return $query->all();
    }

    /**
     * @param $system_id
     * @return bool
     * 判断系统是否有效
     */
    public static function is_valid($system_id)
    {
        try {
            $system = self::getSystemById($system_id, ['status']);
            if ($system['status'] == self::getActiveVal()) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $exception) {
            return false;
        }

    }

    /**
     * 根据id获取系统信息
     * @param $system_id
     * @param $fields
     * @return array|null|ActiveRecord
     */
    public static function getSystemById($system_id, $fields = ['*'])
    {
        //组织条件
        $condition = [
            "system_id" => $system_id,
        ];

        //返回结果
        return static::find()->where($condition)->select($fields)->one();
    }
}
