<?php
/**
 * Created by PhpStorm.
 * User: chenqiu
 * Date: 2019-01-05
 * Time: 18:25
 */

use yii\helpers\Html;

$this->title = 'User Confirm';

$this->registerJsFile('@web/js/register_uuid.js', [
    'depends' => ['\yii\web\JqueryAsset'],
    'position' => \yii\web\View::POS_END
    ]
);

$this->registerJsFile('@web/js/js.cookie.js', [
        'depends' => ['\yii\web\JqueryAsset'],
        'position' => \yii\web\View::POS_END
    ]
);

echo '<h1>' . Html::encode('register_uuid') . '</h1>';