<?php
/**
 * Created by PhpStorm.
 * User: chewchen
 * Date: 2018/12/18
 * Time: 17:47
 */

namespace app\models\ar;


use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;

class MailRecipient extends ActiveRecord {
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
        return 'mail_recipient';
    }

    public function addOne2Mail($group_id, $mail_abbr, $mail_receiver)
    {
        $this->group_id = $group_id;
        $this->abbr = $mail_abbr;
        $this->receiver = $mail_receiver;
        $this->save();
        return $this;
    }

}