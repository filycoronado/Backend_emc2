<?php

namespace frontend\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * Main frontend application asset bundle.
 */
class LandingAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'https://use.fontawesome.com/releases/v5.3.1/css/all.css',
        'https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.5.10/css/mdb.min.css',
        'css/bootstrap-social.css',
        'js/sweetalert2/dist/sweetalert2.min.css',
        'css/site.css',

    ];
    public $js = [
        'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.13.0/umd/popper.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.5.10/js/mdb.min.js',
        'js/ui-bootstrap-tpls-2.5.0.min.js',
        'js/sweetalert2/dist/sweetalert2.min.js',
        'js/app.js',
        'js/config-landing.js',
        'js/controllers/loginController.js',
        'js/controllers/registerController.js',
        'js/globalServices.js'
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapPluginAsset',
        'frontend\assets\AngularAsset',
    ];
    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];
}
