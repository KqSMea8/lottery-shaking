<?php

use app\models\dbaccess\BootDB;
use yii\helpers\Html;
use app\assets\IndexAsset;

$this->title = '视图检查';
IndexAsset::register($this);
$this->registerJsFile(
        '@web/js/index.js',
        ['depends' => ['\yii\web\JqueryAsset'], 'position' => \yii\web\View::POS_END]
);

$this->registerJsFile(
        '@web/js/js.cookie.js',
        ['depends' => ['yii\web\JqueryAsset'], 'position' => \yii\web\View::POS_END]
);
?>

<div class="row">

    <div class="col-md-11">
        <h1><?= Html::encode('值班视图检测') ?></h1>
        <p>
            点击查看视图检测结果。
        </p>
    </div>
</div>

<p id="shake" style="font-size: 14px; color: #b92c28;"></p>

