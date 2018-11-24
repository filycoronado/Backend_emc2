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
    <body ng-controller="MainController" class="fixed-sn light-blue-skin">
        <?php $this->beginBody() ?>

        <!--Double navigation-->
        <header>
            <!-- Sidebar navigation -->
            <div id="slide-out" class="side-nav sn-bg-4 fixed">
                <ul class="custom-scrollbar">
                    <!-- Logo -->
                    <li>
                        <div class="logo-wrapper waves-light">
                            <a href="#"><img src="images/logo.png" class="img-fluid flex-center"></a>
                        </div>
                    </li>
                    <!--/. Logo -->
                    <!--Search Form-->
                    <li ng-if="displaySearch" style="display: none;">
                        <form class="search-form" role="search">
                            <div class="form-group md-form mt-0 pt-1 waves-light">
                                <input type="text" class="form-control" placeholder="Search">
                            </div>
                        </form>
                    </li>
                    <!--/.Search Form-->
                    <!-- Side navigation links -->
                    <li>
                        <ul class="collapsible collapsible-accordion nav navbar-nav">
                            <li><a class="collapsible-header waves-effect arrow-r" href="#/"><i class="fa fas fa-home"></i> Home</a>
                            </li>
                            <li><a class="collapsible-header waves-effect arrow-r"><i class="fa fas fa-users"></i>
                                    Customers<i class="fa fa-angle-down rotate-icon"></i></a>
                                <div class="collapsible-body">
                                    <ul>
                                        <li><a href="#/Customers/Search" class="waves-effect">Search</a></li>
                                        <li><a href="#/Customer/add" class="waves-effect">New Client</a></li>
                                        <!--<li><a href="#/Customers/link-authorize" class="waves-effect">Link Authorize.net</a></li>-->
                                    </ul>
                                </div>
                            </li>
                            <li><a class="collapsible-header waves-effect arrow-r"><i class="fa fas fa-table"></i> 
                                    Reports<i class="fa fa-angle-down rotate-icon"></i></a>
                                <div class="collapsible-body">
                                    <ul>
                                        <li><a href="#/Report/Sales" class="waves-effect">Sales Report</a></li>
                                        <li><a href="#/Report/Status" class="waves-effect">Status Report</a></li>
                                    </ul>
                                </div>
                            </li>
                            <li><a class="collapsible-header waves-effect arrow-r" href="#/forms"><i class="fa fas fa-file-alt"></i> Forms</a>
                        </ul>
                    </li>
                    <!--/. Side navigation links -->
                </ul>
                <div class="sidenav-bg mask-strong"></div>
            </div>
            <!--/. Sidebar navigation -->
            <!-- Navbar -->
            <nav class="navbar fixed-top navbar-toggleable-md navbar-expand-lg scrolling-navbar double-nav">
                <!-- SideNav slide-out button -->
                <div class="float-left">
                    <a href="#" data-activates="slide-out" class="button-collapse"><i class="fa fa-bars"></i></a>
                </div>
                <!-- Breadcrumb-->
                <div class="breadcrumb-dn mr-auto">
                    <p ng-if="pageTitle" style="color: #FFF">{{pageTitle}}</p>
                </div>
                <ul class="nav navbar-nav nav-flex-icons ml-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-user"></i> {{userData.name}}
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
                            <a class="dropdown-item" href="" ng-click="logout()">Logout</a>
                        </div>
                    </li>
                </ul>
            </nav>
            <!-- /.Navbar -->
        </header>
        <!--/.Double navigation-->

        <!--Main Layout-->
        <main>
            <div class="container-fluid mt-5" ng-view>
            </div>
        </main>
        <!--Main Layout-->

        <script src="js/mdb.min.js"></script>
        <script type="text/javascript">
                        // SideNav Button Initialization
                        $(".button-collapse").sideNav();
                        // SideNav Scrollbar Initialization
                        var sideNavScrollbar = document.querySelector('.custom-scrollbar');
                        Ps.initialize(sideNavScrollbar);
        </script>
        <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>
