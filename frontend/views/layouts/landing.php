<?php
/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use frontend\assets\LandingAsset;

LandingAsset::register($this);
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
                        <div class="col-md-12">
                            <div class="social-buttons social-buttons-top">
                                <a class="btn btn-social-icon btn-facebook" href="https://www.facebook.com/americadrivers/" target="_blank">
                                    <span class="fab fa-facebook-f"></span>
                                </a>
                                <a class="btn btn-social-icon btn-twitter" href="https://twitter.com/americadrivers" target="_blank">
                                    <span class="fab fa-twitter"></span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <nav class="navbar navbar-expand-lg navbar-light navbar-top" role="navigation"  bs-navbar>
                        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation" ng-init="navCollapsed = true" ng-click="navCollapsed = !navCollapsed">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarSupportedContent" ng-class="!navCollapsed && 'in'" ng-click="navCollapsed = true">
                            <ul class="navbar-nav mr-auto">

                                <li class="nav-item" data-match-route="/$">
                                    <a class="nav-link" href="#/">Home </a>
                                </li>
                                <li class="nav-item" data-match-route="/about">
                                    <a class="nav-link" href="#/about">About Us</a>
                                </li>
                                <li class="nav-item" data-match-route="/services">
                                    <a class="nav-link" href="#/services">Services</a>
                                </li>
                                <li class="nav-item" data-match-route="/agent">
                                    <a class="nav-link" href="#/agent">Agent</a>
                                </li>
                                <li class="nav-item" data-match-route="/reseller.*">
                                    <a class="nav-link" href="#/reseller">Reseller</a>
                                </li>
                                <li class="nav-item" data-match-route="/contact">
                                    <a class="nav-link" href="#/contact">Contact</a>
                                </li>
                            </ul>

                            <div class="main-logo d-none d-lg-block">
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
