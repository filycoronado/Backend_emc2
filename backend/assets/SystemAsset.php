<?php

namespace backend\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * Main frontend application asset bundle.
 */
class SystemAsset extends AssetBundle {

    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'https://use.fontawesome.com/releases/v5.3.1/css/all.css',
        'css/mdb-pro-4.5.12.css',
        'css/bootstrap-social.css',
        'css/sidenav.css',
        'css/site.css',
        'css/HomeDashboard.css',
        'css/clientsDetails.css',
        'css/SearchStyles.css'
    ];
    public $js = [
        'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.13.0/umd/popper.min.js',
        //'js/mdb.min.js',
        'js/ui-bootstrap-tpls-3.0.5.min.js',
        'js/app.js',
        'js/config-system.js',
        'js/controllers/loginController.js',
        'js/controllers/DashboardController.js',
        'js/controllers/client/ClientFormController.js',
        'js/controllers/SalesReport/SalesReportController.js',
        'js/controllers/EndorsmentReport/EndorsmentReportController.js',
        'js/controllers/PremiumReport/PremiumReportController.js',
        'js/controllers/ClaimsReportController/ClaimsReportController.js',
        'js/controllers/ClientsDetails/ClientsDetailsController.js',
        'js/controllers/ClientsDetails/ClientsPaymentsController.js',
        'js/controllers/ClientsDetails/ClientsClaimsController.js',
        'js/controllers/MakeAPayment/MakeAPaymentController.js',
        'js/controllers/AddClaim/AddClaimController.js',
        'js/controllers/SearchClientController/SearchClientController.js',
        'js/controllers/ClientsDetails/AddCoveragesController.js',
        'js/controllers/AddClaim/ClaimDetailController.js',
        'js/controllers/ClientsDetails/ConfigureAutoPayController.js',
        'js/controllers/ClientsDetails/AddCoveragesController2.js',
        'js/controllers/StatusReport/StatusReportController.js',
        'js/controllers/ClientsDetails/CustomerNotesController.js',
        'js/controllers/Resellers/PostulantsController.js',
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
