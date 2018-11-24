<?php

namespace frontend\assets;

use yii\web\AssetBundle;
use yii\web\View;

class AngularAsset extends AssetBundle
{
    public $sourcePath = '@bower';
    public $js = [
        'angular/angular.js',
        'angular-route/angular-route.js',
        'angular-strap/dist/angular-strap.js',
        'angular-strap/dist/angular-strap.tpl.min.js',
        'angular-input-masks/angular-input-masks-standalone.js',
        'angular-ui-mask/dist/mask.js',
        //'sweetalert2/dist/sweetalert2.all.min.js',
    ];
    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];
}