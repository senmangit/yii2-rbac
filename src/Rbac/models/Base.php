<?php

namespace Rbac\models;

use Yii;

class Base extends \yii\db\ActiveRecord
{
    /**
     * 获取配置的Rbac参数
     */
    public static function getRbacParam()
    {
        return Yii::$app->params['rbac_manager'];
    }

    /**
     * @return mixed
     * 获取用户状态
     */
    public static function getStatusVal()
    {
        $parm = self::getRbacParam();
        return $parm["status"];
    }


    /**
     * @return mixed
     * 获取禁用的值
     */
    public static function getDisableVal($model_name)
    {
        $status_val = self::getStatusVal($model_name);
        return $status_val['status_disable'];
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
}
