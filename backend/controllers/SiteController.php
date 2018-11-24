<?php
namespace backend\controllers;


use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;

/**
 * Site controller
 */
class SiteController extends Controller
{

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {   
        $this->layout = 'system-sidenav';

        return $this->renderContent(null);
    }

}