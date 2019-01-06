<?php
/**
 * Created by PhpStorm.
 * User: chenqiu
 * Date: 2019-01-05
 * Time: 18:44
 */

namespace app\models\ar;


use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;

class ShakePhone extends ActiveRecord
{
    public static function getDb()
    {
        try {
            return Yii::$app->get('bootdb');
        } catch (InvalidConfigException $e) {
        }
        return null;
    }

    public static function tableName()
    {
        return 'shake_phone';
    }


    public function addOneUser($uuid)
    {
        $succeed = false;
        if (self::findOne(['uuid' => $uuid]) != null) {
            return $succeed;
        }
        $self = new self();
        $self->uuid = $uuid;
        $self->shake_count = 0;
        $self->save();
        return $succeed = true;
    }

    public static function updateShakeCount($uuid, $shake_count)
    {
        $select = self::findOne(['uuid' => $uuid]);
        if ($select == null) {
            return null;
        }
        $select->shake_count = $shake_count;
        $select->update();
        return $select;
    }

    public static function setAllExpire($expire)
    {
        $all_users = self::find()->all();
        foreach ($all_users as $user) {
            $user->is_new = !$expire;
            $user->update();
        }
    }
}