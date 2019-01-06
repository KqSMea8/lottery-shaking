<?php
namespace app\models\dbaccess;


use app\models\ar\LocalGroup;
use app\models\ar\LocalView;
use app\models\ar\MailRecipient;
use app\models\ar\ShakePhone;
use Exception;
use Throwable;
use yii\db\StaleObjectException;
use yii\helpers\VarDumper;

/**
 * Created by PhpStorm.
 * User: chewchen
 * Date: 2018/12/11
 * Time: 10:56
 */


class BootDB {

    /**
     * register one user by unique uuid
     * @param $uuid
     * @return bool operate success or not
     */
    public static function addOneUser($uuid)
    {
        $shake = new ShakePhone();
        $succeed = $shake->addOneUser($uuid);
        return $succeed;
    }

    public static function updateUserShakeCount($uuid, $shake_count)
    {
        if ($uuid == null) {
            throw new Exception('UUID post failed, which is null');
        }
        if ($shake_count == null) {
            throw new Exception('shake_count post failed, which is null');
        }
        $shake = ShakePhone::updateShakeCount($uuid, $shake_count);
        if (empty($shake)) {
            throw new Exception('update failed, uuid ' . $uuid . ' not existed');
        }
        return $shake;
    }

}