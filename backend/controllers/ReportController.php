<?php

namespace backend\controllers;

use Yii;
use backend\models\LoginForm;
use backend\models\ContactForm;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\rest\Controller;
use yii\filters\auth\HttpBearerAuth;
use yii\db\Query;
use common\models\Tickets;
use common\models\Vehicles;
use common\models\Client;
use common\models\Permits;
use common\models\Claims;
use common\models\Ciudades;
use common\models\Agency;
use common\models\Companyclaims;
use common\models\ClientsChangelog;
use db\Command;
use AuthorizeNetAIM;
use AuthorizeNet_Subscription;
use yii\helpers\ArrayHelper;
use DateTime;
use CDbCommand;
use FPDM;
use Fpdf\Fpdf;

class ReportController extends Controller {

    /**
     * {@inheritdoc}
     */
    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
            'only' => ['dashboard'],
        ];
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::className(),
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['dashboard'],
            'rules' => [
                [
                    'actions' => ['dashboard'],
                    'allow' => true,
                    'roles' => ['@'],
                ],
            ],
        ];
        return $behaviors;
    }

    public function actionSalesreport() {
        $request = Yii::$app->request;

        $tickets = $request->post('tickets');

        if ($tickets != null) {

            $pdf = new FPDF();

            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 15);
            $pdf->Image(Yii::getAlias('@common') . '/images/logo.png', 10, 1, 35, 38, 'png', 'http://azdriversclub.com/azaz/maketa/');
            $pdf->SetXY(50, 15);
            $pdf->Cell(40, 7, "America Drivers Club", 0, 1, ' R ');
            $pdf->SetXY(50, 10);
            // $pdf->Cell(40,7,'Report'.$f1."/".$f2,0, 1 , ' R ');
            $pdf->Ln(29);
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(35, 5, "Agency", 1, 0, 'C');
            $pdf->Cell(35, 5, "Customer", 1, 0, 'C');
            $pdf->Cell(35, 5, "PM", 1, 0, 'C');
            $pdf->Cell(35, 5, "Type", 1, 0, 'C');
            $pdf->Cell(35, 5, "Total", 1, 0, 'C');
            $pdf->Ln();
            $pdf->Cell(35, 5, "3", 1, 0, 'C');

            return $pdf->Output();
        }
    }


}
