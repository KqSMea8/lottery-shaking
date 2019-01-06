<?php
/**
 * Created by PhpStorm.
 * User: chenqiu
 * Date: 2019-01-06
 * Time: 16:35
 */

use yii\helpers\Html;

$this->title = '抽奖活动开启';

$this->registerJsFile('@web/js/activity_start.js',
    [
        'depends' => ['\yii\web\JqueryAsset'],
        'position' => \yii\web\View::POS_END
    ]
);

echo Html::a('开启授权', ['/site/start-control'], ['class' => 'btn btn-primary']);