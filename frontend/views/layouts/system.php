<?php
/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use frontend\assets\SystemAsset;

SystemAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" ng-app="app">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title>America Drivers Club</title>
        <?php $this->head() ?>

        <script>paceOptions = {ajax: {trackMethods: ['GET', 'POST']}};</script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/red/pace-theme-minimal.css" rel="stylesheet" />
    </head>
    <body ng-controller="MainController">
        <?php $this->beginBody() ?>

        <div class="wrap">
            <header>
                <div class="container">
                    <div class="row">
                        <div class="col-md-12" style="min-height: 66px;">
                        </div>
                    </div>

                    <nav class="navbar navbar-expand-lg navbar-light navbar-top" role="navigation"  bs-navbar>
                        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation" ng-init="navCollapsed = true" ng-click="navCollapsed = !navCollapsed">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarSupportedContent" ng-class="!navCollapsed && 'in'" ng-click="navCollapsed = true">
                            <ul class="navbar-nav mr-auto">

                                <!-- home -->
                                <li class="nav-item" data-match-route="(/$)|(/dashboard)">
                                    <a class="nav-link" href="#/">Home</a>
                                </li>
                                <!-- Customer -->
                                <li class="nav-item dropdown" data-match-route="/Customer(s)?.*">
                                    <a class="nav-link dropdown-toggle" id="new-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Customers</a>
                                    <div class="dropdown-menu dropdown-primary" aria-labelledby="new-dropdown">
                                        <a class="dropdown-item" href="#/Customers/Search">Search</a>
                                        <a class="dropdown-item" href="#/Customer/add">New Client</a>
                                        <a class="dropdown-item" href="#/Customers/link-authorize">Link Authorize.net</a>
                                    </div>
                                </li>
                                <!-- reports -->
                                <li class="nav-item dropdown" data-match-route="/Report.*">
                                    <a class="nav-link dropdown-toggle" id="reports-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Reports</a>
                                    <div class="dropdown-menu dropdown-primary" aria-labelledby="reports-dropdown">
                                        <a class="dropdown-item" href="#/Report/Sales">Sales Report</a>
                                        <a class="dropdown-item" href="#/Report/Endorsements">Endorsements</a>
                                        <a class="dropdown-item" href="#/Report/Premium">Premium Report</a>
                                        <a class="dropdown-item" href="#/Report/Claims">Claims Report</a>
                                    </div>
                                </li>
                                <!-- forms -->
                                <li class="nav-item" data-match-route="/forms">
                                    <a class="nav-link" href="#/forms">Forms</a>
                                </li>
                                <!-- claims -->
                                <!--                                <li class="nav-item" data-match-route="/claims">
                                                                    <a class="nav-link" href="#/claims">Claims</a>
                                                                </li>-->


                                <li class="nav-item" ng-class="{active:isActive('/logout')}" ng-show="loggedIn()" ng-click="logout()" class="ng-hide">
                                    <a class="nav-link" href="">Logout</a>
                                </li>
                            </ul>

                            <div class="main-logo-compact d-none d-lg-block">
                                <img src="images/logo.png" alt="Logo ADC">
                            </div>
                        </div>
                    </nav>
                </div>
            </header>

            <div class="container">
                <div class="content" ng-view>

                </div>
            </div>
        </div>

        <footer class="page-footer white">
            <div class="container">
                <div class="footer-content text-center py-3">
                    Contact Us <span class="badge badge-pill badge-contact"><i class="fas fa-phone"></i></span> 855-500-6912 <span class="badge badge-pill badge-contact"><i class="far fa-envelope"></i></span> CONTACT@AMERICADRIVERSCLUB.COM
                </div>
            </div>
        </footer>

        <?php $this->endBody() ?>

    </body>
</html>
<?php $this->endPage() ?>
