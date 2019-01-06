<?php
/**
 * Created by PhpStorm.
 * User: chewchen
 * Date: 2018/12/6
 * Time: 15:39
 */

namespace app\assets;
use yii\web\AssetBundle;

class IndexAsset extends AssetBundle{

    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        'css/index.css',
        'css/alert.css',
    ];
    public $js = [
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}