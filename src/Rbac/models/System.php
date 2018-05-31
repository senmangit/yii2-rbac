<?php

namespace Rbac\models;

use Yii;

/**
 * This is the model class for table "system".
 *
 * @property int $system_id
 * @property string $name 系统名称
 * @property string $remark 系统描述
 * @property string $url 系统入口
 * @property int $delete_flag 删除标识，0：未删除，1：删除
 * @property int $status 状态：0：正常启用，1暂停使用
 * @property string $create_time 创建时间
 * @property string $update_time 更新时间
 *
 * @property Role[] $roles
 * @property Rule[] $rules
 */
class System extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%system}}';
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




}
