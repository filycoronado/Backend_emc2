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
use common\models\NotasAzdriver;
use db\Command;
use AuthorizeNetAIM;
use AuthorizeNet_Subscription;
use yii\helpers\ArrayHelper;
use DateTime;
use FPDM;
use Fpdf\Fpdf;

define("AUTHORIZENET_API_LOGIN_ID", "5Qj6sW2W2s");
define("AUTHORIZENET_TRANSACTION_KEY", "67YEnFZk7F735w5c");
define("AUTHORIZENET_SANDBOX", false);

/**
 * Site controller
 */
class ApiController extends Controller {

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

    public function beforeAction($action) {
        //if ($action->id == 'login') {
        $this->enableCsrfValidation = false;
        //}
        return parent::beforeAction($action);
    }

    public function actionLogin() {

        $model = new LoginForm();
        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->login()) {
            return ['access_token' => Yii::$app->user->identity->getAuthKey()];
        } else {
            $model->validate();
            return $model;
        }
    }

    public function actionGetuserdata() {
        if (!Yii::$app->user->getIsGuest())
            return Yii::$app->user->identity;
        else
            return array();
    }

    public function actionGetuseraccess() {
        if (!Yii::$app->user->getIsGuest() && $permits = Permits::find()->where(["id_user" => Yii::$app->user->identity->id])->one())
            return $permits;
        else
            return array();
    }

    public function actionLogout() {
        return Yii::$app->user->logout();
    }

    public function actionDashboard() {
        $response = [
            'username' => Yii::$app->user->identity->user,
            'access_token' => Yii::$app->user->identity->getAuthKey(),
        ];
        return $response;
    }

    function actionUser_by_id($id) {

        $user = (new Query())->select('*')->from('users')->where('id=' . $id . "and (nivel=1 or nivel=2 or nivel=5)and habilitado=1")->one();
        if ($user != null) {
            return $user;
        }
        return null;
    }

    function actionUsers_by_agency() {


        $user = (new Query())->select('*')->from('users')->where("(agencia=101 or agencia=103 or  agencia=104 or agencia=105)  and (nivel=1 or nivel=2 or nivel=5) and habilitado=1")->all();
        if ($user != null) {
            return $user;
        }
        return null;
    }

    function actionAgencies_by_owner() {
        $request = Yii::$app->request;
        $idOwner = $request->post('idOwner');
        $agencies = (new Query())->select('*')->from('agency')->where('id_owner=' . $idOwner)->all();
        if ($agencies != null) {
            return $agencies;
        }
        return null;
    }

    function actionGet_clients_by_agency_status() {
        /**
         * get All Cleints actives  per agency
         */
        $user = Yii::$app->user->identity;
        $permits = new Permits();
        $permits = $this->Get_permits($user->id);

        $stragency = "";
        if ($permits != null && $permits->agency == 1) {
            $stragency = '(id_agencia=101 or id_agencia=102 or id_agencia=103 or id_agencia=104 or  id_agencia=105)';
        } else {
            $stragency = 'id_agencia=' . $user->agencia;
        }
        $Clients = Client::find()
                ->Where('habilitado=1')
                ->andwhere($stragency)
                ->andWhere("CURDATE() <= fechaMed")
                ->andWhere("credit_value=0")
                ->all();
        // $Clients = (new Query())->select('*')->from('clients')->where('Status_client=' . $status . ' and id_agencia=' . $agency . ' and habilitado=1')->all();
        if ($Clients != null) {
            return $Clients;
        }
        return null;
    }

    function actionGet_tikets_by_date() {
        $request = Yii::$app->request;
        $f1 = $request->post('f1');
        $f2 = $request->post('f2');
        $agency = $request->post('agency');
        $user = $request->post('user');
        $plan = $request->post('plan');
        $typeMembership = $request->post('typeMembership');
        $typeTransaction = $request->post('typeTransaction');
        //$idowner = $request->post('idowner');

        $substringAgency = "";
        $substringUser = "";
        $substringPlan = "";
        $substringTypeTransaction = "";
        $substringTypeMermber = "";
        if ($agency != 0) {
            $substringAgency = " clients.id_agencia=$agency";
        } else {
            
        }

        if ($user != "") {
            $substringUser = " tickets.agente='$user'";
        }

        if ($plan != 0) {
            $substringPlan = " clients.plan=$plan";
        }

        if ($typeTransaction != 0) {

            if ($typeTransaction == 2) {
                $substringTypeTransaction = " (tickets.payment='Card' or tickets.payment='CARD')";
            } else if ($typeTransaction == 3) {
                $substringTypeTransaction = " (tickets.payment='Card-Auto' or tickets.payment='Auto-Pay')";
            } else if ($typeTransaction == 1) {
                $substringTypeTransaction = "  (tickets.payment='Cash' or tickets.payment='CASH')";
            }
        }

        if ($typeMembership != "") {
            $substringTypeMermber = " tickets.label_status='$typeMembership'";
        }

        /* $find = \app\models\Client::find()->innerJoinWith('tickets', false)
          ->all(); */

        /* $Tiket = (new Query())->select('clients.id_agencia as ag, tickets.total as tot, clients.*, tickets.*')->from('clients ')
          ->innerJoin('tickets', $on = 'clients.id=tickets.id_client')->where("tickets.date BETWEEN  '$date1' and '$date2' $substringAgency $substringUser $substringPlan $substringTypeTransaction $substringTypeMermber")->all(); */


        $arr = explode("T", $f1);
        $f1 = $arr[0];

        $arr2 = explode("T", $f2);
        $f2 = $arr2[0];

        $query = new Query;
        $query->select('clients.id as  id_cliente, clients.Dpayment as membership_status,clients.id_agencia as ag, clients.name as name , clients.apellido as lastname, clients.plan as plan, tickets.total as tot, tickets.*'
                )
                ->from('clients')
                ->join('LEFT OUTER JOIN', 'tickets', 'clients.id=tickets.id_client')
                ->Where($substringAgency)
                ->andFilterWhere(['between', 'tickets.date', $f1, $f2])
                ->andWhere($substringUser)
                ->andWhere($substringPlan)
                ->andWhere($substringTypeTransaction)
                ->andWhere($substringTypeMermber)
                ->all();

        $command = $query->createCommand();
        $data = $command->queryAll();
        if ($data != null) {
            $this->CreateSalesReport($data);
            return $data;
        }

        return $data;
    }

    function Save_ticket($id_client, $type, $date, $agente, $total, $payment, $tpaymode, $addcover, $label) {
        /* "": "19542",
          "file": "Forms/1954209-09-201720:16:28Invoicern.PDF",
          "": "INVOICE",
          "": "2017-09-09",
          "": "usererik",
          "": "24",
          "": "Cash",
          "": "0",
          "id_agencia": null,
          "agcode": "0",
          "addcover": "0",
          "free": "0" */
        $tick = new Tickets();
        $tick->id_client = $id_client;
        $tick->type = $type;
        $tick->date = $date;
        $tick->agente = $agente;
        $tick->total = $total;
        $tick->payment = $payment;
        $tick->addcover = $addcover;
        $tick->tpaymode = $tpaymode;
        $tick->label_status = $label;
        $tick->agcode = 0;
        return $tick->save(false);
    }

    function actionUpdateTicket($idticket, $id_client, $type, $date, $agente, $total, $payment, $tpaymode, $addcover, $File) {
        $tick = new Tickets();
        if (($tick = Tickets::findOne($idticket)) !== null) {
            $tick->id_client = $id_client;
            $tick->type = $type;
            $tick->date = $date;
            $tick->agente = $agente;
            $tick->total = $total;
            $tick->payment = $payment;
            $tick->addcover = $addcover;
            $tick->tpaymode = $tpaymode;
            $tick->file = $File;
            $tick->update(false);
        } else {
            return null;
        }
    }

    function generarticket() {
        
    }

    function actionSave_vehicle($id_client, $vin, $year, $make, $model, $glass, $imagen1, $imagen2, $imagen3, $imagen4) {
        /*
         *    'id_client' => 'Id Client',
          'vin' => 'Vin',
          'year' => 'Year',
          'make' => 'Make',
          'model' => 'Model',
          'glass' => 'Glass',
          'note' => 'Note',
          'placas' => 'Placas',
          'id_agencia' => 'Id Agencia',
          'imagen1' => 'Imagen1',
          'imagen2' => 'Imagen2',
          'imagen3' => 'Imagen3',
          'imagen4' => 'Imagen4',
          'premium' => 'Premium',
         * 
         */
        $vehicle = new Vehicles();
        $vehicle->id_client = $id_client;
        $vehicle->vin = $vin;
        $vehicle->year = $year;
        $vehicle->make = $make;
        $vehicle->model = $model;
        $vehicle->glass = $glass;
        $vehicle->imagen1 = $imagen1;
        $vehicle->imagen2 = $imagen2;
        $vehicle->imagen3 = $imagen3;
        $vehicle->imagen4 = $imagen4;
        $vehicle->save(true);
    }

    function actionUpdate_vehicle($id_vechile, $id_client, $vin, $year, $make, $model, $glass, $imagen1, $imagen2, $imagen3, $imagen4) {
        $vehicle = new Vehicles();
        if (($vehicle = Vehicles::findOne($id_vechile)) !== null) {

            $vehicle->vin = $vin;
            $vehicle->year = $year;
            $vehicle->make = $make;
            $vehicle->model = $model;
            $vehicle->glass = $glass;
            $vehicle->imagen1 = $imagen1;
            $vehicle->imagen2 = $imagen2;
            $vehicle->imagen3 = $imagen3;
            $vehicle->imagen4 = $imagen4;
            $vehicle->update(false);
        } else {
            return null;
        }
    }

    function actionSave_client() {
        $modelClient = new Client();
        $modelClient->load(Yii::$app->getRequest()->getBodyParams(), 'modelClient');
        $vehicles = Yii::$app->request->post('vehicles');
        $cardInfo = Yii::$app->getRequest()->getBodyParams('cardInfo');

        $modelClient->id_users = Yii::$app->user->identity->user;
        $modelClient->total2 = 0;
        $modelClient->total3 = 0;
        $modelClient->total4 = 0;
        $modelClient->total5 = 0;
        $modelClient->agcode = 0;
        $modelClient->Activebank = 0;
        $modelClient->id_agencia = Yii::$app->user->identity->agencia;
        $modelClient->fecAlta = date('Y-m-d');
        $stardate = "";
        $modelClient->credit_value = 0;
        $modelClient->payment_date_credit = date("Y-m-d");
        if (isset($cardInfo['cardInfo']['recurrentPayment']) && $cardInfo['cardInfo']['recurrentPayment']) {
            $stardate = $cardInfo['cardInfo']['paymentDay'];
        }
        if ($modelClient->paymode != "0") {
            if ($modelClient->paymode == 1) {//un pago pendiente
                $modelClient->total = $modelClient->paid;
                $modelClient->credit_value = $modelClient->total;
                $modelClient->payment_date_credit = $stardate;
            } else if ($modelClient->paymode == 2) {// dos pagos pendientes
                $modelClient->total = $modelClient->paid;
                $modelClient->credit_value = ($modelClient->total * 2);
                $modelClient->payment_date_credit = $stardate;
            }
        }
        //corregir fechas
        if (!empty($modelClient->date_birth))
            $modelClient->date_birth = date('Y-m-d', strtotime($modelClient->date_birth));
        if (!empty($modelClient->effective))
            $modelClient->effective = date('Y-m-d', strtotime($modelClient->effective));
        if (!empty($modelClient->fechaMed))
            $modelClient->fechaMed = date('Y-m-d', strtotime($modelClient->fechaMed));

        $modelClient->exp = $modelClient->fechaMed;
        $timeEffective = strtotime($modelClient->effective);
        switch ($modelClient->plan) {
            case 1:

                $modelClient->exp = date("Y-m-d", strtotime("+6 months", $timeEffective));
                $modelClient->dateexpfinal = date("Y-m-d", strtotime("+6 months", $timeEffective));
                break;
            case 2:
                $modelClient->exp = date("Y-m-d", strtotime("+6 months", $timeEffective));
                $modelClient->dateexpfinal = date("Y-m-d", strtotime("+6 months", $timeEffective));
                break;
            case 3:
                $modelClient->exp = date("Y-m-d", strtotime("+12 months", $timeEffective));
                $modelClient->dateexpfinal = date("Y-m-d", strtotime("+12 months", $timeEffective));
                break;
        }
        $enbaleAutopay = 0;
        if (isset($cardInfo['cardInfo']['recurrentPayment']) && $cardInfo['cardInfo']['recurrentPayment']) {
            $enbaleAutopay = 1;
        }

        if ($modelClient->validate()) {
            //guardar cliente
            if ($modelClient->save(false)) {

                //guardar vehiculos
                foreach ($vehicles as $v) {
                    $vehicle = new Vehicles();
                    //$vehicle->load($v);
                    $vehicle->id_client = $modelClient->id;
                    $vehicle->vin = $v['vin'];
                    $vehicle->model = $v['model'];
                    $vehicle->year = $v['year'];
                    $vehicle->make = $v['make'];
                    $vehicle->glass = $v['glass'];
                    if (!$vehicle->save()) {
                        //agregar algun error de que no se guardo el vehiculo o algo
                        //return $vehicle;
                    }
                }

                //generar pago

                $response = [
                    'status' => 'success',
                    'message' => 'Client saved.',
                    'id' => $modelClient->id
                ];
                switch ($modelClient->pay) {
                    case 1: // CASH/FREE
                        //DoPaymentCash($modelClient, $type, $agente, $tpaymode, $addcover, $payment, $newBusiness , $cardInfo, $enbaleAutopay)
                        $value = $this->DoPaymentCash($modelClient, "INVOICE", Yii::$app->user->identity->user, 0, 0, "CASH", true, $cardInfo, $enbaleAutopay); //cash
                        if ($value == 1) {
                            return $response;
                        } else {
                            return "Error payment Cash" . $value;
                        }

//$this->DoPaymentCash($modelClient, "INVOICE", Yii::$app->user->identity->user, 0, 0, "CASH", true);
                        break;
                    case 2: //
                        //
                    $value = $this->DoPaymentCard($modelClient, "INVOICE", Yii::$app->user->identity->user, 0, 0, "CARD", $cardInfo, true, $enbaleAutopay);
                        if ($value == 1) {
                            return $response;
                        } else {
                            return "Error";
                        }
                        //$this->DoPaymentCard($modelClient->id, "INVOICE", Yii::$app->user->identity->user, $modelClient->paid, 0, 0, "CARD", $cardInfo['billingName'], $cardInfo['billingLastName'], $cardInfo['cardNumber'], $expDate, $cardInfo['cvv'], $stardate);
                        break;
                }
                if (isset($cardInfo['recurrentPayment']) && $cardInfo['recurrentPayment']) {
                    //establecer pago rregurente aqui
                }
            }

            $response = [
                'status' => 'success',
                'message' => 'Client saved.',
                'id' => $modelClient->id
            ];
            return $response;
        } else {
            $modelClient->validate();
            return $modelClient;
        }
    }

    function actionClient_by_id($id_cliente) {
        if (($model = Client::findOne($id_cliente)) !== null) {
            return $model;
        } else {
            return null;
        }
    }

    function actionUpdate_client($id) {

        if (($modelClient = Client::findOne($id)) !== null) {
            $modelClient->load(Yii::$app->getRequest()->getBodyParams(), 'modelClient');

            if (!empty($modelClient->date_birth))
                $modelClient->date_birth = date('Y-m-d', strtotime($modelClient->date_birth));

            if ($modelClient->save()) {
                $response = [
                    'status' => 'success',
                    'message' => 'Client saved.',
                    'id' => $modelClient->id
                ];
                return $response;
            } else {
                throw new Exception("Error Processing Request", 1);
            }
        } else {
            throw new \yii\web\NotFoundHttpException();
        }
    }

    function actionUpdate_client_membership($client_id, $effecctiveDate, $service, $plan) {
        $Client = new Client();
        $pendignDate;
        $cancelDate;
        $expiredDate;
        if ($plan == 1) {//monthly
            $pendignDate = strtotime('+7 day', strtotime($effecctiveDate));
            $pendignDate = date('Y-m-d', $pendignDate);

            $cancelDate = strtotime('+6 month', strtotime($effecctiveDate));
            $cancelDate = date('Y-m-d', $cancelDate);

            $expiredDate = strtotime('+12 month', strtotime($effecctiveDate));
            $expiredDate = date('Y-m-d', $expiredDate);
        } else if ($plan == 2) {//six months
            $pendignDate = strtotime('+6 month', strtotime($effecctiveDate));
            $pendignDate = date('Y-m-d', $pendignDate);

            $cancelDate = strtotime('+6 month', strtotime($effecctiveDate));
            $cancelDate = date('Y-m-d', $cancelDate);

            $expiredDate = strtotime('+6 month', strtotime($effecctiveDate));
            $expiredDate = date('Y-m-d', $expiredDate);
        } else if ($plan == 3) {//year
            $pendignDate = strtotime('+12 month', strtotime($effecctiveDate));
            $pendignDate = date('Y-m-d', $pendignDate);

            $cancelDate = strtotime('+12 month', strtotime($effecctiveDate));
            $cancelDate = date('Y-m-d', $cancelDate);

            $expiredDate = strtotime('+12 month', strtotime($effecctiveDate));
            $expiredDate = date('Y-m-d', $expiredDate);
        }
        if (($Client = Client::findOne($client_id)) !== null) {
            $Client->effective = $effecctiveDate; /* creation date */
            $Client->exp = $cancelDate; /* cancel date membership */
            $Client->fechaMed = $pendignDate; /* pending cancellaion date */
            //  $Client->dateexpfinal = $expiredDate; /* end date policy */
            $Client->service = $service;
            $Client->plan = $plan;

            return $Client->update(false);
        } else {
            return null;
        }
    }

    function actionMake_payment() {


        $stardate = date("Y-m-d");

        $cardInfo = [];
        $modelClient = new Client();
        $modelClient->load(Yii::$app->getRequest()->getBodyParams(), 'modelClient');
        $cardInfo = Yii::$app->getRequest()->getBodyParams('cardInfo');
        $user = Yii::$app->user->identity;
        $request = Yii::$app->request;
        $isAddCover = $request->post('isAddCover');
        $new_business = $request->post('new_business');
        $id_client = $request->post('id_client');
        $method = $modelClient->pay;
        $modelClient->id = $id_client;
        $enbaleAutopay = 0;
        $responseForBack = [
            'flash' => [
                'class' => 'success',
                'message' => 'Payment saved.',
            ]
        ];

        switch ($method) {
            case 1:
                if (isset($cardInfo['cardInfo']['recurrentPayment']) && $cardInfo['cardInfo']['recurrentPayment']) {
                    $enbaleAutopay = 1;
                }
                $value = $this->DoPaymentCash($modelClient, "INVOICE", Yii::$app->user->identity->user, 0, $isAddCover, "CASH", $new_business, $cardInfo, $enbaleAutopay); //cash
                if ($value == 1) {
                    return $responseForBack;
                } else {
                    return "Error";
                }

                break;
            case 2: // CARD DoPaymentCard(Client $modelClient, $type, $agente, $tpaymode, $addcover, $cardInfo, $newBusiness)
                $enbaleAutopay = 0;
                if (isset($cardInfo['cardInfo']['recurrentPayment']) && $cardInfo['cardInfo']['recurrentPayment']) {
                    $enbaleAutopay = 1;
                    //$cardInfo['cardInfo']['paymentDay'];
                }
                $value = $this->DoPaymentCard($modelClient, "INVOICE", Yii::$app->user->identity->user, 0, $isAddCover, "CARD", $cardInfo, false, $enbaleAutopay);
                if ($value == 1) {
                    return $responseForBack;
                } else {
                    return "Error";
                }

                break;
            case 3:
                $this->DoPaymentAutoPay($client_id, "INVOICE", $id_user, $amount, 0, $isAddCover, "AUTO-PAY", $cardName, $cardLastName, $credictCard, $expDate);
                break;
        }
        // return $client->pay;
    }

    function RewriteMembership(Client $modelClient2, $id_client, $label) {
        $modelClient = new Client();
        $modelClient = Client::findOne($id_client);
        $plan = $modelClient->plan;
        $modelClient->Dpayment = $label;
        $startDate = date("Y-m-d");

        if ($modelClient2->credit_value == 0) {
            switch ($plan) {
                case 1:
                    $modelClient->effective = $startDate;
                    $expirationDate = strtotime('+6 month', strtotime($modelClient->effective));
                    $expirationDate = date('Y-m-d', $expirationDate);
                    $fechamed = strtotime('+1 month', strtotime($modelClient->effective));
                    $fechamed = date('Y-m-d', $fechamed);
                    $modelClient->exp = $expirationDate;
                    $modelClient->fechaMed = $fechamed;
                    break;
                case 2:
                    $modelClient->effective = $startDate;
                    $expirationDate = strtotime('+6 month', strtotime($modelClient->effective));
                    $expirationDate = date('Y-m-d', $expirationDate);
                    $fechamed = strtotime('+6 month', strtotime($modelClient->effective));
                    $fechamed = date('Y-m-d', $fechamed);
                    $modelClient->exp = $expirationDate;
                    $modelClient->fechaMed = $fechamed;
                    break;
                case 3:
                    $modelClient->effective = $startDate;
                    $expirationDate = strtotime('+12 month', strtotime($modelClient->effective));
                    $expirationDate = date('Y-m-d', $expirationDate);
                    $fechamed = strtotime('+12 month', strtotime($modelClient->effective));
                    $fechamed = date('Y-m-d', $fechamed);
                    $modelClient->exp = $expirationDate;
                    $modelClient->fechaMed = $fechamed;
                    break;
            }
            return $modelClient->update(false);
        } else {
            $modelClient->total = $modelClient->total + $modelClient2->paid;
            $modelClient->credit_value = ($modelClient2->credit_value - $modelClient2->paid);

            $date = $modelClient2->payment_date_credit;
            $date = strtotime('+1 month', strtotime($date));
            $date = date('Y-m-d', $date);
            $modelClient->payment_date_credit = $date;
            return $modelClient->update(false);
        }
    }

    function RenewMembership(Client $modelClient2, $id_client, $label) {
        $modelClient = new Client();
        $modelClient = Client::findOne($id_client);
        $plan = $modelClient->plan;
        $modelClient->Dpayment = $label;
        if ($modelClient2->credit_value == 0) {
            switch ($plan) {
                case 1: //monthly
                    $modelClient->effective = $modelClient->exp;

                    $expirationDate = strtotime('+6 month', strtotime($modelClient->effective));
                    $expirationDate = date('Y-m-d', $expirationDate);
                    $fechamed = strtotime('+1 month', strtotime($modelClient->effective));
                    $fechamed = date('Y-m-d', $fechamed);
                    $modelClient->exp = $expirationDate;
                    $modelClient->fechaMed = $fechamed;
                    break;
                case 2: //six Months
                    $modelClient->effective = $modelClient->exp;
                    $expirationDate = strtotime('+6 month', strtotime($modelClient->effective));
                    $expirationDate = date('Y-m-d', $expirationDate);
                    $fechamed = strtotime('+6 month', strtotime($modelClient->effective));
                    $fechamed = date('Y-m-d', $fechamed);
                    $modelClient->exp = $expirationDate;
                    $modelClient->fechaMed = $fechamed;
                    break;
                case 3://yearly

                    $modelClient->effective = $modelClient->exp;
                    $expirationDate = strtotime('+12 month', strtotime($modelClient->effective));
                    $expirationDate = date('Y-m-d', $expirationDate);
                    $fechamed = strtotime('+12 month', strtotime($modelClient->effective));
                    $fechamed = date('Y-m-d', $fechamed);
                    $modelClient->exp = $expirationDate;
                    $modelClient->fechaMed = $fechamed;
                    break;
            }

            return $modelClient->update(false);
        } else {
            $modelClient->total = $modelClient->total + $modelClient2->paid;
            $modelClient->credit_value = ($modelClient2->credit_value - $modelClient2->paid);

            $date = $modelClient2->payment_date_credit;
            $date = strtotime('+1 month', strtotime($date));
            $date = date('Y-m-d', $date);
            $modelClient->payment_date_credit = $date;
            return $modelClient->update(false);
        }
    }

    function updateDatesInPeriod(Client $modelClient2, $id_client, $case, $label) {
        $modelClient = new Client();
        $modelClient = Client::findOne($id_client);
        $plan = $modelClient->plan;
        $effectiveDate = $modelClient->effective;
        $marginDatePayment = $modelClient->fechaMed;
        $modelClient->Dpayment = $label;

        //calclar nuevos values


        if ($modelClient2->credit_value == 0) {
            switch ($plan) {
                case 1: //monthly

                    $newPendingDate = strtotime('+1 month', strtotime($marginDatePayment));
                    $newPendingDate = date('Y-m-d', $newPendingDate);
                    $modelClient->effective = $marginDatePayment;
                    $modelClient->fechaMed = $newPendingDate;

                    break;
                case 2:  //six months
                    $newPendingDate = strtotime('+6 month', strtotime($marginDatePayment));
                    $newPendingDate = date('Y-m-d', $newPendingDate);
                    $modelClient->effective = $marginDatePayment;
                    $modelClient->fechaMed = $newPendingDate;
                    $modelClient->exp = $newPendingDate;
                    break;
                case 3: //year
                    $newPendingDate = strtotime('+12 month', strtotime($marginDatePayment));
                    $newPendingDate = date('Y-m-d', $newPendingDate);
                    $modelClient->effective = $marginDatePayment;
                    $modelClient->fechaMed = $newPendingDate;
                    $modelClient->exp = $newPendingDate;
                    break;
            }


            return $modelClient->update(false);
        } else {
            $modelClient->total = $modelClient->total + $modelClient2->paid;
            $modelClient->credit_value = ($modelClient2->credit_value - $modelClient2->paid);

            $date = $modelClient2->payment_date_credit;
            $date = strtotime('+1 month', strtotime($date));
            $date = date('Y-m-d', $date);
            $modelClient->payment_date_credit = $date;
            return $modelClient->update(false);
        }
    }

    public function DoPaymentCash(Client $modelClient, $type, $agente, $tpaymode, $addcover, $payment, $newBusiness, $cardInfo, $enbaleAutopay) {


        $today = date("Y-m-d");
        $plan = $modelClient->plan;
        $effectiveDate = $modelClient->effective;
        $marginDatePayment = $modelClient->fechaMed;
        $expirationDate = $modelClient->exp;
        $total = $modelClient->paid;


        if ($enbaleAutopay == 1) {
            $this->DoPaymentAutoPay($modelClient, $cardInfo);
        }
        if ($newBusiness == true) {// firt payment firt day policy is today not necessary changes
            $label = "New_Business";
            $r = $this->Save_ticket($modelClient->id, $type, $today, $agente, $modelClient->paid, $payment, $tpaymode, $addcover, $label);
            if ($r == true) {
                return true;
            } else {
                return false;
            }
        } else if ($today <= $marginDatePayment && $today < $expirationDate && $effectiveDate != $expirationDate) {/* payment period */

            $label = "Payment";
            if ($modelClient->credit_value > 0) {
                $label = "credit";
            }
            $r = $this->Save_ticket($modelClient->id, $type, $today, $agente, $modelClient->paid, $payment, $tpaymode, $addcover, $label);

            if ($r == true) {
                return $this->updateDatesInPeriod($modelClient, $modelClient->id, 1, $label);
            } else {
                return false;
            }
        } else if ($today > $marginDatePayment && $today < $expirationDate) { /* plocy canceled heere */

            $label = "Re-Active";
            $r = $this->Save_ticket($modelClient->id, $type, $today, $agente, $modelClient->paid, $payment, $tpaymode, $addcover, $label);
            if ($r == true) {
                return $this->updateDatesInPeriod($modelClient, $modelClient->id, 2, $label);
            } else {
                return false;
            }
        } else if ($today <= $marginDatePayment && $effectiveDate == $expirationDate) {
            $label = "Renew";
            $r = $this->Save_ticket($modelClient->id, $type, $today, $agente, $modelClient->paid, $payment, $tpaymode, $addcover, $label);

            if ($r == true) {
                return $this->RenewMembership($modelClient, $modelClient->id, $label);
            } else {
                return false;
            }
        } else if ($today >= $expirationDate) { ///Rewrite
            $label = "Rewrite";
            $r = $this->Save_ticket($modelClient->id, $type, $today, $agente, $modelClient->paid, $payment, $tpaymode, $addcover, $label);
            if ($r == true) {
                return $this->RewriteMembership($modelClient, $modelClient->id, $label);
            } else {
                return false;
            }
        }
    }

    function DoPaymentCard(Client $modelClient, $type, $agente, $tpaymode, $addcover, $labelMP, $cardInfo, $newBusiness, $autopay) {

        $merchantAuthentication = new \net\authorize\api\contract\v1\MerchantAuthenticationType();
        $merchantAuthentication->setName("5Qj6sW2W2s");
        $merchantAuthentication->setTransactionKey("8kzrp6M658F5SsWm");



        $today = date("Y-m-d");
        $cardExpiry = $cardInfo['cardInfo']['mm'] . "/" . $cardInfo['cardInfo']['yy'];

        // Create the payment data for a credit card
        $creditCard = new \net\authorize\api\contract\v1\CreditCardType;
        $creditCard->setCardNumber($cardInfo['cardInfo']['cardNumber']);
        $creditCard->setExpirationDate($cardExpiry);
        $creditCard->setCardCode($cardInfo['cardInfo']['cvv']);
        // Add the payment data to a paymentType object
        $paymentOne = new \net\authorize\api\contract\v1\PaymentType;
        $paymentOne->setCreditCard($creditCard);
        // Create order information
        $order = new \net\authorize\api\contract\v1\OrderType();
        $order->setInvoiceNumber("ADC" . $modelClient->id);
        $order->setDescription("Coverages ADC");

        $customerAddress = new \net\authorize\api\contract\v1\CustomerAddressType();
        $customerAddress->setFirstName($modelClient->name);
        $customerAddress->setLastName($modelClient->apellido);
        $customerAddress->setCompany("America Drivers Club RD");
        $customerAddress->setAddress($modelClient->address);
        $customerAddress->setCity($modelClient->city);
        $customerAddress->setState($modelClient->state);
        $customerAddress->setZip($modelClient->zip);
        $customerAddress->setCountry("USA");

        $customerData = new \net\authorize\api\contract\v1\CustomerDataType();
        $customerData->setType("individual");
        $customerData->setId($modelClient->id);
        $customerData->setEmail($modelClient->email);

        // Add values for transaction settings
        $duplicateWindowSetting = new \net\authorize\api\contract\v1\SettingType();
        $duplicateWindowSetting->setSettingName("duplicateWindow");
        $duplicateWindowSetting->setSettingValue("60");

        // Add some merchant defined fields. These fields won't be stored with the transaction,
        // but will be echoed back in the response.
        $merchantDefinedField1 = new \net\authorize\api\contract\v1\UserFieldType();
        $merchantDefinedField1->setName("NUM");
        $merchantDefinedField1->setValue($modelClient->phone);

        $merchantDefinedField2 = new \net\authorize\api\contract\v1\UserFieldType();
        $merchantDefinedField2->setName("Best Company");
        $merchantDefinedField2->setValue("ADC");


        $transactionRequestType = new \net\authorize\api\contract\v1\TransactionRequestType();
        $transactionRequestType->setTransactionType("authCaptureTransaction");
        $transactionRequestType->setAmount($modelClient->paid); //$modelClient->paid
        $transactionRequestType->setOrder($order);
        $transactionRequestType->setPayment($paymentOne);
        $transactionRequestType->setBillTo($customerAddress);
        $transactionRequestType->setCustomer($customerData);
        $transactionRequestType->addToTransactionSettings($duplicateWindowSetting);
        $transactionRequestType->addToUserFields($merchantDefinedField1);
        $transactionRequestType->addToUserFields($merchantDefinedField2);


        // Assemble the complete transaction request
        // Set the transaction's refId
        $refId = 'ref' . time();
        $request = new \net\authorize\api\contract\v1\CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setRefId($refId);
        $request->setTransactionRequest($transactionRequestType);
        // Create the controller and get the response
        $controller = new \net\authorize\api\controller\CreateTransactionController($request);
        $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);





//        $cardExpiry = $cardInfo['cardInfo']['mm'] . "/" . $cardInfo['cardInfo']['yy'];
//        $sale = new AuthorizeNetAIM;
//        $sale->amount = $modelClient->paid;
//        $sale->first_name = $modelClient->name;
//        $sale->last_name = $modelClient->apellido;
//        $sale->company = "ADC";
//        $sale->card_num = $cardInfo['cardInfo']['cardNumber'];
//        $sale->exp_date = $cardExpiry;
//        $response = $sale->authorizeAndCapture();






        if ($response != null && $response->getMessages()->getResultCode() == "Ok") {


            //$transaction_id = $response->transaction_id;
            //active Auto pay
            if ($autopay == 1) {
                $this->DoPaymentAutoPay($modelClient, $cardInfo);
            }
            $today = date("Y-m-d");
            $plan = $modelClient->plan;
            $effectiveDate = $modelClient->effective;
            $marginDatePayment = $modelClient->fechaMed;
            $expirationDate = $modelClient->exp;
            $total = $modelClient->paid;

            if ($newBusiness) {// firt payment firt day policy is today not necessary changes
                $label = "New_Business";
                $r = $this->Save_ticket($modelClient->id, $type, $today, $agente, $modelClient->paid, $labelMP, $tpaymode, $addcover, $label);
                if ($r == true) {
                    return true;
                } else {
                    return false;
                }
            } else if ($today <= $marginDatePayment && $today < $expirationDate && $effectiveDate != $expirationDate) {/* payment period */

                $label = "Payment";
                $r = $this->Save_ticket($modelClient->id, $type, $today, $agente, $modelClient->paid, $labelMP, $tpaymode, $addcover, $label);

                if ($r == true) {
                    return $this->updateDatesInPeriod($modelClient, $modelClient->id, 1, $label);
                } else {
                    return false;
                }
            } else if ($today > $marginDatePayment && $today < $expirationDate) { /* plocy canceled heere */

                $label = "Re-Active";
                $r = $this->Save_ticket($modelClient->id, $type, $today, $agente, $modelClient->paid, $labelMP, $tpaymode, $addcover, $label);
                if ($r == true) {
                    return $this->updateDatesInPeriod($modelClient, $modelClient->id, 2, $label);
                } else {
                    return false;
                }
            } else if ($today <= $marginDatePayment && $effectiveDate == $expirationDate) {
                $label = "Renew";
                $r = $this->Save_ticket($modelClient->id, $type, $today, $agente, $modelClient->paid, $labelMP, $tpaymode, $addcover, $label);

                if ($r == true) {
                    return $this->RenewMembership($modelClient, $modelClient->id, $label);
                } else {
                    return false;
                }
            } else if ($today >= $expirationDate) { ///Rewrite
                $label = "Rewrite";
                $r = $this->Save_ticket($modelClient->id, $type, $today, $agente, $modelClient->paid, $labelMP, $tpaymode, $addcover, $label);
                if ($r == true) {
                    return $this->RewriteMembership($modelClient, $modelClient->id, $label);
                } else {
                    return false;
                }
            }
        } else {
            return 0;
        }
    }

    function updateSubscription($subscriptionId, $cardInfo, $total) {
        /* Create a merchantAuthenticationType object with authentication details
          retrieved from the constants file */

        $cardExpiry = $cardInfo['cardInfo']['mm'] . "/" . $cardInfo['cardInfo']['yy'];

        $merchantAuthentication = new \net\authorize\api\contract\v1\MerchantAuthenticationType();
        $merchantAuthentication->setName("5Qj6sW2W2s");
        $merchantAuthentication->setTransactionKey("8kzrp6M658F5SsWm");

        // Set the transaction's refId
        $refId = 'ref' . time();
        $subscription = new \net\authorize\api\contract\v1\ARBSubscriptionType();
        $creditCard = new \net\authorize\api\contract\v1\CreditCardType();
        $creditCard->setCardNumber($cardInfo['cardInfo']['cardNumber']);
        $creditCard->setExpirationDate($cardExpiry);
        $payment = new \net\authorize\api\contract\v1\PaymentType();
        $payment->setCreditCard($creditCard);


        //set profile information
        $profile = new \net\authorize\api\contract\v1\CustomerProfileIdType();
        //$profile->setCustomerProfileId("121212");
        // $profile->setCustomerPaymentProfileId("131313");
        //  $profile->setCustomerAddressId("141414");
        $subscription->setPayment($payment);
        $subscription->setAmount($total);
        //set customer profile information
        //$subscription->setProfile($profile);

        $request = new \net\authorize\api\contract\v1\ARBUpdateSubscriptionRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setRefId($refId);
        $request->setSubscriptionId($subscriptionId);
        $request->setSubscription($subscription);
        $controller = new \net\authorize\api\controller\ARBUpdateSubscriptionController($request);
        //AnetController\ARBUpdateSubscriptionController($request);
        $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);

        return $response;
    }

    function DoPaymentAutoPay(Client $modelClient, $cardInfo) {
        $merchantAuthentication = new \net\authorize\api\contract\v1\MerchantAuthenticationType();
        $merchantAuthentication->setName("5Qj6sW2W2s");
        $merchantAuthentication->setTransactionKey("8kzrp6M658F5SsWm");
//Convert it into a timestamp.
        $timestamp = strtotime($cardInfo['cardInfo']['paymentDay']);
        $stardate = $cardInfo['cardInfo']['paymentDay'];
        $date = new DateTime($stardate);
        $stardate = $date->format('Y-m-d H:i:s');
        $ocurrences = "12";
        if ($modelClient->paymode == 1) {
            $ocurrences = "1";
        }
        if ($modelClient->paymode == 2) {
            $ocurrences = "2";
        }
//Convert it to DD-MM-YYYY

        $cardExpiry = $cardInfo['cardInfo']['mm'] . "/" . $cardInfo['cardInfo']['yy'];

        $refId = 'ref' . time();
        $subscription = new \net\authorize\api\contract\v1\ARBSubscriptionType();
        $subscription->setName("America Drivers Club");

        $interval = new \net\authorize\api\contract\v1\PaymentScheduleType\IntervalAType();
        $interval->setLength("1");
        $interval->setUnit("months");

        $paymentSchedule = new \net\authorize\api\contract\v1\PaymentScheduleType();
        $paymentSchedule->setInterval($interval);
        $paymentSchedule->setStartDate(new DateTime($stardate));
        $paymentSchedule->setTotalOccurrences($ocurrences);
        $paymentSchedule->setTrialOccurrences("1");

        $subscription->setPaymentSchedule($paymentSchedule);
        $subscription->setAmount($modelClient->paid); //
        $subscription->setTrialAmount("0.00");

        $creditCard = new \net\authorize\api\contract\v1\CreditCardType();
        $creditCard->setCardNumber($cardInfo['cardInfo']['cardNumber']);
        $creditCard->setExpirationDate($cardExpiry);

        $payment = new \net\authorize\api\contract\v1\PaymentType();
        $payment->setCreditCard($creditCard);
        $subscription->setPayment($payment);

        $order = new \net\authorize\api\contract\v1\OrderType();
        $order->setInvoiceNumber("ADC" . $modelClient->id);
        $order->setDescription("America Drivers Club");
        $subscription->setOrder($order);


        $billTo = new \net\authorize\api\contract\v1\NameAndAddressType();
        $billTo->setFirstName($modelClient->name);
        $billTo->setLastName($modelClient->apellido);
        $subscription->setBillTo($billTo);

        $request = new \net\authorize\api\contract\v1\ARBCreateSubscriptionRequest();
        $request->setmerchantAuthentication($merchantAuthentication);
        $request->setRefId($refId);
        $request->setSubscription($subscription);
        $controller = new \net\authorize\api\controller\ARBCreateSubscriptionController($request);

        $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);





        $subscription_id = $response->getSubscriptionId();

        $cardNumberstr = $cardInfo['cardInfo']['cardNumber'];
        $cardExpiry = $cardInfo['cardInfo']['mm'] . "/" . $cardInfo['cardInfo']['yy'];
        $cvv = $cardInfo['cardInfo']['cvv'];
        $finallyString = $cardNumberstr . "|" . $cardExpiry . "|" . $cvv;
        $modelClient->CardNumber = $finallyString;
        // echo  "Subcritpionid:--->".$subscription_id;
        if ($subscription_id > 0) {
            $modelClient->subscriptionID = $subscription_id;
            $modelClient->Activebank = 9999;
            $modelClient->update(false);
            return true;
        } else {
            return false;
        }
//        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
//            echo "SUCCESS: Subscription ID : " . $response->getSubscriptionId() . "\n";
//        } else {
//            echo "ERROR :  Invalid response\n";
//            $errorMessages = $response->getMessages()->getMessage();
//            echo "Response : " . $errorMessages[0]->getCode() . "  " . $errorMessages[0]->getText() . "\n";
//        }
    }

    function updateSubcriptionAutoPay($subscriptionId, $subscription) {
        $request = new AuthorizeNetARB;
        $response = $request->updateSubscription($subscriptionId, $subscription);
    }

    function actionGet_payhistory() {
        $request = Yii::$app->request;
        $id_client = $request->post('id_client');
        //preparing the query
        $tickets = Tickets::find()->where('total>0')->andWhere("id_client=$id_client")->all();


        return $tickets;
        /* $ticekt = Tickets::find()->where('total>0')
          ->andWhere("id_client=$id_client")->all();
          return $ticekt; */
    }

    function actionGet_payhistory_latest() {
        $request = Yii::$app->request;
        $id_client = $request->post('id_client');
        //preparing the query
        $tickets = Tickets::find()->where(['>', 'total', 0])->andWhere(['id_client' => $id_client])->limit(5)->orderBy(['date' => SORT_DESC])->all();

        return $tickets;
    }

    public function Get_permits($id_user) {
        $permits = Permits::find()->where("id_user=" . $id_user)->one();
        if ($permits != null) {
            return $permits;
        } else {
            return null;
        }
    }

    public function actionGet_change_log() {
        //$log = ClientsChangelog::find()->all();
        //        $log = ClientsChangelog::find()
        //        ->joinWith(,false)
        //        ->all();

        $query = new Query;
        $query->select('clients_changelog.*,users.user as us'
                )->from('clients_changelog')
                ->join('LEFT OUTER JOIN', 'users', 'users.id=clients_changelog.id_user')
                ->orderBy(['date' => SORT_DESC])
                ->all();

        $command = $query->createCommand();
        $data = $command->queryAll();
        if ($data != null) {
            return $data;
        }

        return $data;
    }

    public function actionAutomaticPaymetn() {
        $payment = $request->post();
    }

    public function actionGet_claims() {
        $request = Yii::$app->request;

        $f1 = $request->post('f1');
        $f2 = $request->post('f2');
        $arr = explode("T", $f1);
        $f1 = $arr[0];
        $arr2 = explode("T", $f2);
        $f2 = $arr2[0];
        $type_claim = $request->post('type_claim');
        $status_claim = $request->post('status_claim');
        $paid = $request->post('paid');

        $stringpaid = "";
        $stringTypeclaim = "";
        $stringStatusClaim = "";

        if ($paid != "") {
            $stringpaid = "claims.pagado=" . $paid;
        }
        if ($type_claim != "") {
            $stringTypeclaim = "claims.tipoclaim=" . $type_claim;
        }

        if ($status_claim != "") {
            $stringStatusClaim = "claims.status=" . $status_claim;
        }
        $claims = Claims::find()->andWhere(['between', 'fecha', $f1, $f2])->andWhere($stringpaid)->andWhere($stringTypeclaim)->andWhere($stringStatusClaim)->all();
        if ($claims != null) {
            $this->CreateClaimReport($claims);
        }
        return $claims;
    }

    public function actionGet_client_by_id() {
        $request = Yii::$app->request;
        $id_client = $request->post('id_client');
        $client = Client::findOne($id_client);
        if ($client)
            return $client;
        else {
            throw new \yii\web\NotFoundHttpException();
        }
    }

    public function actionGet_cars_by_id() {
        $request = Yii::$app->request;
        $id_client = $request->post('id_client');
        $vehicles = Vehicles::find()->where("id_client=" . $id_client)->all();
        return $vehicles;
    }

    public function actionGet_claims_by_id() {
        $request = Yii::$app->request;
        $id_client = $request->post('id_client');
        $claims = Claims::find()->where("member_id=" . $id_client)->all();
        return $claims;
    }

    public function actionGet_claims_by_id_latest() {
        $request = Yii::$app->request;
        $id_client = $request->post('id_client');
        $claims = Claims::find()->where(['member_id' => $id_client])->limit(5)->orderBy(['fecha' => SORT_DESC])->all();
        return $claims;
    }

    public function actionGet_cyties() {
        $cyties = Ciudades::find()->all();
        return $cyties;
    }

    public function actionGet_provider_bycity() {
        $request = Yii::$app->request;
        $id_city = $request->post("id_ciudad");
        $Companyclaims = Companyclaims::find()->where("id_ciudad=" . $id_city)->all();
        return $Companyclaims;
    }

    public function actionSave_claim() {
        $request = Yii::$app->request;
        $vehicle = new Vehicles();

        $id_client = $request->post('id_client');
        $claimForm = Yii::$app->getRequest()->getBodyParams('claimForm');
        $vehicleID = $claimForm['claimForm']['VehicleSelected'];
        $vehicle = Vehicles::findOne($vehicleID);
        $modelClient = Client::find()->where("id=" . $id_client)->one();
        $idprovider = $claimForm['claimForm']['ProviderSelected'];

        $claim = new Claims();
        $fullname = $modelClient->name . " " . $modelClient->apellido;
        $companyCliams = new Companyclaims();
        $companyCliams = Companyclaims::find()->where("id=" . $idprovider)->one();
        $nameProvider = $companyCliams->nombre;
        $claim->fecha = date("Y-m-d");
        $claim->claim = $claimForm['claimForm']['observations'];
        $claim->color = $claimForm['claimForm']['VehicleColor'];
        $claim->dirorigen = $claimForm['claimForm']['originL'];
        $claim->dirdestino = $claimForm['claimForm']['DestinationL'];
        $claim->placas = $claimForm['claimForm']['vehilePlates'];
        $claim->idprovedor = $claimForm['claimForm']['ProviderSelected'];
        $claim->mtv = $claimForm['claimForm']['mtv'];
        $claim->autoestado = $claimForm['claimForm']['VehicleConditions'];
        $claim->id_agencia = $modelClient->id_agencia;
        $claim->tipoclaim = $claimForm['claimForm']['ClaimType'];
        $claim->id_usuario = Yii::$app->user->identity->id;
        $claim->member_id = $modelClient->id;
        $claim->year = $vehicle->year;
        $claim->model = $vehicle->model;
        $claim->vin = $vehicle->vin;
        $claim->make = $vehicle->make;
        $claim->tc = $claimForm['claimForm']['vehicleType'];
        $claim->status = 1; //open
        $claim->pagado = 2; //not paid
        $claim->provider = $nameProvider;
        $claim->servicio = $modelClient->service;
        $claim->nom = $fullname;
        $claim->telefono = $modelClient->phone;
        $claim->total = $claimForm['claimForm']['total'];
        $response = [];
        if ($claim->save(false)) {
            $response = [
                'status' => 'success',
                'message' => 'Claim saved.',
                'id' => $claim->id
            ];
        } else {
            $response = [
                'status' => 'Error',
                'message' => 'claim can not save.'
            ];
        }
        return $response;
    }

    public function actionSearchclients() {
        $request = Yii::$app->request;
        $idAgency = Yii::$app->user->identity->agencia;
        $agency = new Agency();
        $agency = Agency::find()->where("id=" . $idAgency)->one();
        $owner = $agency->id_owner;
        $agencies = Agency::find()->where("id_owner=" . $owner)->all();
        $strtlo = "";
        $iterator = 1;
        $number = count($agencies);
        foreach ($agencies as &$agency) {

            if ($iterator < $number) {
                $strtlo .= "id_agencia=" . $agency->id . " or  ";
            } else if ($iterator == $number) {
                $strtlo .= "id_agencia=" . $agency->id;
            }
            $iterator++;
        }

        $idClient = $request->post('membershiID');
        $Name = $request->post('Name');
        $LastName = $request->post('LastName');
        $clients;
        if ($idClient != null) {
            $clients = Client::find()->where("id=" . $idClient)->andWhere($strtlo)->all();
        } else {
            $clients = Client::find()->Where("name like '%" . $Name . "%'")->andWhere("apellido like '%" . $LastName . "%'")->andWhere($strtlo)->all();
        }
        $response;
        if ($clients != null) {
            $response = [
                'status' => 'success',
                'message' => '.',
                'Info' => $clients,
            ];
        } else {
            $response = [
                'status' => 'Error',
                'message' => 'Error.',
                'Info' => $clients,
            ];
        }

        return $response;
    }

    public function actionSavecoverages() {

        $cardInfo = [];
        $modelClient = new Client();
        $modelClient->load(Yii::$app->getRequest()->getBodyParams(), 'modelClient');
        $cardInfo = Yii::$app->getRequest()->getBodyParams('cardInfo');
        $vehicles = Yii::$app->request->post('vehicles');
        $user = Yii::$app->user->identity;
        $request = Yii::$app->request;

        $Gtotal = $request->post('Gtotal');
        $id_client = $request->post('id_client');
        $old_value = $request->post('old_value');
        $modelClient->total = $Gtotal;
        $modelClient->id = $id_client;
        //$modelClient->update(false);
        //save Ticket
        //guardar vehiculos
        $response = [
            'status' => 'success',
            'message' => '',
        ];

        if ($modelClient->subscriptionID != 0) {

            $this->updateSubscription($modelClient->subscriptionID, $cardInfo, $Gtotal);
        }

        if ($modelClient->pay == 1) {
            $r = $this->savePaymentCASH($modelClient->id, "Coverages", $user->user, $modelClient->paid, 1);
            if ($r == true) {
                foreach ($vehicles as $v) {
                    $vei = Vehicles::find()->where('id=' . $v['id'])->one();
                    $vei->glass = $v['glass'];
                    $vei->update(false);
                }

                $clientLog = new ClientsChangelog();
                $clientLog->description = "Cahnges Coverages in Membership  (Type Glass)";
                $clientLog->date = date("Y-m-d");
                $clientLog->total_before = $old_value;
                $clientLog->total_after = $Gtotal;
                $clientLog->status = 1;
                $clientLog->id_client = $modelClient->id;
                $clientLog->id_user = $user->id;
                $clientLog->save(false);
                $modelClient->update(false);
            } else {
                $response = [
                    'status' => 'error',
                    'message' => 'error',
                ];
            }
        } else if ($modelClient->pay == 2) {
            $r = $this->savePaymentCard($modelClient, $cardInfo, $user->user, 1, "Coverages");

            if ($r == true) {
                foreach ($vehicles as $v) {
                    $vei = Vehicles::find()->where('id=' . $v['id'])->one();
                    $vei->glass = $v['glass'];
                    $vei->update(false);
                }
                $modelClient->update(false);
            } else {
                $response = [
                    'status' => 'error',
                    'message' => 'error',
                ];
            }
        }

        return $response;
    }

    public function actionUpdatecoverages() {

        $request = Yii::$app->request;
        $Gtotal = $request->post('Gtotal');
        $old_value = $request->post('old_value');
        $id_client = $request->post('id_client');

        $modelClient = Client::findOne($id_client);
        $modelClient->load(Yii::$app->getRequest()->getBodyParams(), 'modelClient');
        $cardInfo = Yii::$app->getRequest()->getBodyParams('cardInfo');
        $vehicles = Yii::$app->request->post('vehicles');
        $user = Yii::$app->user->identity;





        $modelClient->total = $Gtotal;
        $modelClient->id = $id_client;
        //$modelClient->update(false);
        //save Ticket
        //guardar vehiculos

        if ($modelClient->subscriptionID != 0) {

            $this->updateSubscription($modelClient->subscriptionID, $cardInfo, $Gtotal);
        }
        $r = $modelClient->update(false);
        if (true) {
            $this->RewriteMembership($modelClient,$id_client, "Rewrite");

            if (true) {
                // update cards here
                $querty = Vehicles::deleteAll("id_client=" . $modelClient->id);
                //guardar vehiculos
                foreach ($vehicles as $v) {
                    $vehicle = new Vehicles();
                    //$vehicle->load($v);
                    $vehicle->id_client = $modelClient->id;
                    $vehicle->vin = $v['vin'];
                    $vehicle->model = $v['model'];
                    $vehicle->year = $v['year'];
                    $vehicle->make = $v['make'];
                    $vehicle->glass = $v['glass'];
                    if (!$vehicle->save()) {
                        $response = [
                            'status' => 'error1',
                            'message' => '',
                        ];
                    }
                }

                $clientLog = new ClientsChangelog();
                $clientLog->description = "Cahnges Coverages in Membership  (re-adjust membership)";
                $clientLog->date = date("Y-m-d");
                $clientLog->total_before = $old_value;
                $clientLog->total_after = $Gtotal;
                $clientLog->status = 1;
                $clientLog->id_client = $modelClient->id;
                $clientLog->id_user = $user->id;
                $clientLog->save(false);


                $response = [
                    'status' => 'success',
                    'message' => '',
                ];
                return $response;
            } else {
                $response = [
                    'status' => 'error2',
                    'message' => '',
                ];
            }
        } else {
            $response = [
                'status' => 'error3',
                'message' => '',
            ];

            return $response;
        }
    }

    function savePaymentCASH($id_client, $label, $agent, $paid, $iscover) {
        $today = date("Y-m-d");
        //$value = $this->DoPaymentCash($modelClient, "INVOICE", Yii::$app->user->identity->user, 0, $isAddCover, "CASH", $new_business, $cardInfo, $enbaleAutopay); //cash
        return $this->Save_ticket($id_client, "INVOICE", $today, $agent, $paid, "CASH", 0, $iscover, $label);
    }

    function savePaymentCard(Client $modelClient, $cardInfo, $agent, $iscover, $label) {

        $merchantAuthentication = new \net\authorize\api\contract\v1\MerchantAuthenticationType();
        $merchantAuthentication->setName("5Qj6sW2W2s");
        $merchantAuthentication->setTransactionKey("8kzrp6M658F5SsWm");



        $today = date("Y-m-d");
        $cardExpiry = $cardInfo['cardInfo']['mm'] . "/" . $cardInfo['cardInfo']['yy'];

        // Create the payment data for a credit card
        $creditCard = new \net\authorize\api\contract\v1\CreditCardType;
        $creditCard->setCardNumber($cardInfo['cardInfo']['cardNumber']);
        $creditCard->setExpirationDate($cardExpiry);
        $creditCard->setCardCode($cardInfo['cardInfo']['cvv']);
        // Add the payment data to a paymentType object
        $paymentOne = new \net\authorize\api\contract\v1\PaymentType;
        $paymentOne->setCreditCard($creditCard);
        // Create order information
        $order = new \net\authorize\api\contract\v1\OrderType();
        $order->setInvoiceNumber("ADC" . $modelClient->id);
        $order->setDescription("Coverages ADC");

        $customerAddress = new \net\authorize\api\contract\v1\CustomerAddressType();
        $customerAddress->setFirstName($modelClient->name);
        $customerAddress->setLastName($modelClient->apellido);
        $customerAddress->setCompany("America Drivers Club RD");
        $customerAddress->setAddress($modelClient->address);
        $customerAddress->setCity($modelClient->city);
        $customerAddress->setState($modelClient->state);
        $customerAddress->setZip($modelClient->zip);
        $customerAddress->setCountry("USA");

        $customerData = new \net\authorize\api\contract\v1\CustomerDataType();
        $customerData->setType("individual");
        $customerData->setId($modelClient->id);
        $customerData->setEmail($modelClient->email);

        // Add values for transaction settings
        $duplicateWindowSetting = new \net\authorize\api\contract\v1\SettingType();
        $duplicateWindowSetting->setSettingName("duplicateWindow");
        $duplicateWindowSetting->setSettingValue("60");

        // Add some merchant defined fields. These fields won't be stored with the transaction,
        // but will be echoed back in the response.
        $merchantDefinedField1 = new \net\authorize\api\contract\v1\UserFieldType();
        $merchantDefinedField1->setName("NUM");
        $merchantDefinedField1->setValue($modelClient->phone);

        $merchantDefinedField2 = new \net\authorize\api\contract\v1\UserFieldType();
        $merchantDefinedField2->setName("Best Company");
        $merchantDefinedField2->setValue("ADC");


        $transactionRequestType = new \net\authorize\api\contract\v1\TransactionRequestType();
        $transactionRequestType->setTransactionType("authCaptureTransaction");
        $transactionRequestType->setAmount($modelClient->paid);
        $transactionRequestType->setOrder($order);
        $transactionRequestType->setPayment($paymentOne);
        $transactionRequestType->setBillTo($customerAddress);
        $transactionRequestType->setCustomer($customerData);
        $transactionRequestType->addToTransactionSettings($duplicateWindowSetting);
        $transactionRequestType->addToUserFields($merchantDefinedField1);
        $transactionRequestType->addToUserFields($merchantDefinedField2);


        // Assemble the complete transaction request
        // Set the transaction's refId
        $refId = 'ref' . time();
        $request = new \net\authorize\api\contract\v1\CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setRefId($refId);
        $request->setTransactionRequest($transactionRequestType);
        // Create the controller and get the response
        $controller = new \net\authorize\api\controller\CreateTransactionController($request);
        $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);




        if ($response != null) {
            // Check to see if the API request was successfully received and acted upon
            if ($response->getMessages()->getResultCode() == "Ok") {
                return $this->Save_ticket($modelClient->id, "INVOICE", $today, $agent, $modelClient->paid, "CARD", 0, $iscover, $label);
            }
        }
    }

    public function actionGet_claim_byid() {
        $request = Yii::$app->request;
        $id_claim = $request->post('id_claim');
        $claim = Claims::find()->where('id=' . $id_claim)->one();
        return $claim;
    }

    public function actionUpdate_claim() {
        $claim = Yii::$app->request->post('claimForm');
        $c = new Claims();
        $c = Claims::find()->where("id=" . $claim['id'])->one();
        $c->status = $claim['status'];
        $c->pagado = $claim['pagado'];
        $c->total = $claim['total'];
        $c->claim = $claim['claim'];

        return $c->update(false);
    }

    public function actionInvoce_pdf($id) {
        $ticket = Tickets::findOne($id);
        if ($ticket) {
            $fields = array(
                'membership' => $ticket->client->id,
                'full_name' => $ticket->client->name . ' ' . $ticket->client->apellido,
                'agency' => $ticket->client->id_agencia,
                'date' => $ticket->date,
                'amount' => $ticket->total,
                'transaction' => 'payment',
                'next_pay' => $ticket->client->exp,
                'method' => $ticket->payment,
                'total' => $ticket->total,
                'client_s' => '',
                'agent_s' => '',
            );

            $pdf = new FPDM(Yii::getAlias('@common') . '/pdf/Invoicern_form_fix.pdf');
            $pdf->Load($fields, true); // second parameter: false if field values are in ISO-8859-1, true if UTF-8
            $pdf->Merge(false);

            return $pdf->Output();
        } else {
            throw new \yii\web\NotFoundHttpException();
        }
    }

    public function actionClaim_form($id) {
        $pdf = new FPDF();

        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 15);
        $pdf->Image(Yii::getAlias('@common') . '/images/logo.png', 10, 1, 35, 38, 'png', 'http://azdriversclub.com/azaz/maketa/');
        $pdf->SetXY(50, 15);
        $pdf->Cell(40, 7, "America Drivers Club", 0, 1, ' R ');
        $pdf->SetXY(50, 10);
        // $pdf->Cell(40,7,'Report'.$f1."/".$f2,0, 1 , ' R ');
        $pdf->Ln(29);
        $claim = Claims::findOne($id);
        $pdf->SetFont('Arial', 'B', 9);

        $pdf->Ln();
        $pdf->Cell(35, 5, "Date:", 1, 0, 'C');
        $pdf->Cell(70, 5, $claim->fecha, 1, 0, 'L');
        $pdf->Ln();
        $pdf->Cell(35, 5, "User #:", 1, 0, 'C');
        $pdf->Cell(70, 5, $claim->id_usuario, 1, 0, 'L');
        $pdf->Ln();
        $pdf->Cell(35, 5, "Claim#:", 1, 0, 'C');
        $pdf->Cell(70, 5, $claim->id, 1, 0, 'L');
        $pdf->Ln();
        $pdf->Cell(35, 5, "Total$:", 1, 0, 'C');
        $pdf->Cell(70, 5, $claim->total, 1, 0, 'L');
        $pdf->Ln();
        $pdf->Cell(35, 5, "Membership#:", 1, 0, 'C');
        $pdf->Cell(70, 5, $claim->member_id, 1, 0, 'L');
        $pdf->Ln();
        $pdf->Cell(35, 5, "Customer", 1, 0, 'C');
        $pdf->Cell(70, 5, $claim->nom, 1, 0, 'L');
        $pdf->Ln();
        $pdf->Cell(35, 5, "Phone #:", 1, 0, 'C');
        $pdf->Cell(70, 5, $claim->telefono, 1, 0, 'L');
        $pdf->Ln(10);

        $labeltc = "";

        switch ($claim->tipoclaim) {
            case 1:
                $labeltc = "TOWING";
                break;
            case 2:
                $labeltc = "FLAT TIRE POSIBLE TOW";
                break;
            case 3:
                $labeltc = "FUEL DELIVERY";
                break;
            case 4:
                $labeltc = "LOCKOUT";
                break;
            case 5:
                $labeltc = "GLASS";
                break;
            case 6:
                $labeltc = "BATTERY JUMPSTART";
                break;
        }
        $pdf->Cell(35, 5, "Provider:", 1, 0, 'C');
        $pdf->Cell(70, 5, $claim->provider, 1, 0, 'L');
        $pdf->Ln();
        $pdf->Cell(35, 5, "Type Claim:", 1, 0, 'C');
        $pdf->Cell(70, 5, $labeltc, 1, 0, 'L');
        $pdf->Ln();
        $pdf->Cell(35, 5, "Disablement Location:", 1, 0, 'C');
        $pdf->Cell(70, 5, $claim->dirorigen, 1, 0, 'L');
        $pdf->Ln();
        $pdf->Cell(35, 5, "Destination Location:", 1, 0, 'C');
        $pdf->Cell(70, 5, $claim->dirdestino, 1, 0, 'L');
        $pdf->Ln(10);
        $pdf->Cell(35, 5, "Vin:", 1, 0, 'C');
        $pdf->Cell(70, 5, $claim->vin, 1, 0, 'L');
        $pdf->Ln();
        $pdf->Cell(35, 5, "Make:", 1, 0, 'C');
        $pdf->Cell(70, 5, $claim->make, 1, 0, 'L');
        $pdf->Ln();
        $pdf->Cell(35, 5, "Model:", 1, 0, 'C');
        $pdf->Cell(70, 5, $claim->model, 1, 0, 'L');
        $pdf->Ln();
        $pdf->Cell(35, 5, "Year:", 1, 0, 'C');
        $pdf->Cell(70, 5, $claim->year, 1, 0, 'L');
        $pdf->Ln();
        $pdf->Cell(35, 5, "Color:", 1, 0, 'C');
        $pdf->Cell(70, 5, $claim->color, 1, 0, 'L');
        $pdf->Ln(10);
        $pdf->Cell(35, 5, "Obervations:", 1, 0, 'C');
        $pdf->Ln();
        $pdf->MultiCell(0, 4, $claim->claim);

        return $pdf->Output();
    }

    public function actionAutopayform($id) {
        $client = Client::findOne($id);
        $cardNumberFull = $client->CardNumber;

        $arrCard = explode("|", $cardNumberFull);

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
        $pdf->Cell(60, 5, "Mail to :Contact@americadriversclub.com", 0, 0, 'L');
        $pdf->Ln();
        $pdf->Ln();
        //$pdf->Cell(60,5,"Date:__/__/____",0,0,'L');  $pdf->Ln();
        $pdf->Cell(60, 5, "Start Date:", 0, 0, 'L');
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Cell(60, 5, "Member ID:" . $client->id, 0, 0, 'L');
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Cell(60, 5, "Customer Name:" . $client->name . " " . $client->apellido, 0, 0, 'L');
        $pdf->Ln();
        $pdf->Ln();

        $pdf->Ln(15);
        $pdf->SetFont('Arial', '', 12);
        $txt = "My signature below autorizes the company to initiate entries to my banking account
for premium payments via the method selected below.This authority will remain 
in effect until I notify you in writing to cancel it in such time as to afford the 
financial institution a reasonable opportunity to act on it. I can stop payment or any 
entry by notifying my financial institution  6 days before my account is charged.";
        $pdf->Cell(20, 7, "", 0, 0, 'C');
        $pdf->MultiCell(0, 4, $txt);
        $pdf->Ln(5);
        $pdf->SetTextColor(240, 255, 240); //Letra color blanco
        $pdf->Cell(60, 5, "Card Number", 1, 0, 'C', true);
        $pdf->Cell(60, 5, "Exp Date", 1, 0, 'C', true);
        $pdf->Cell(60, 5, "CVV (3-Digit Code On Back)", 1, 0, 'C', true);
        $pdf->Ln();
        $pdf->SetTextColor(10, 10, 10); //Letra color negroi
        $pdf->Cell(60, 9, $arrCard[0], 1, 0, 'C');
        $pdf->Cell(60, 9, $arrCard[1], 1, 0, 'C');
        $pdf->Cell(60, 9, $arrCard[2], 1, 0, 'C');
        $pdf->Ln();
        $pdf->SetTextColor(240, 255, 240); //Letra color blanco
        $pdf->Cell(60, 5, "Cardholder Name", 1, 0, 'C', true);
        $pdf->Cell(60, 5, "Credit Card Type", 1, 0, 'C', true);
        $pdf->Cell(60, 5, "Cardholder Address", 1, 0, 'C', true);
        $pdf->Ln();
        $pdf->SetTextColor(10, 10, 10); //Letra color negroi
        $pdf->Cell(60, 9, $client->name . " " . $client->apellido, 1, 0, 'C');
        $pdf->Cell(60, 9, "", 1, 0, 'C');
        $pdf->Cell(60, 9, "", 1, 0, 'C');
        $pdf->SetTextColor(10, 10, 10); //Letra color negroi
        $pdf->Ln(15);
        $pdf->Cell(60, 9, "Member Name's Signature:", 0, 0, 'C');
        $pdf->Cell(60, 9, " ", 0, 0, 'C');
        //$pdf->Cell(60,9,"Account Holder's Signature:",0,0,'C');
        $pdf->ln(10);
        $pdf->Cell(60, 9, "______________________", 0, 0, 'C');
        $pdf->Cell(60, 9, " ", 0, 0, 'C');
        // $pdf->Cell(60,9,"______________________",0,0,'C');
        $pdf->ln();
        $pdf->Cell(60, 9, "Date:________________", 0, 0, 'C');

        return $pdf->Output();
    }

    public function actionMember_card($id) {
        $client = Client::findOne($id);
        if ($client) {
            $pdf = new FPDF();

            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 15);
            $pdf->Image(Yii::getAlias('@common') . '/images/logo.png', 10, 1, 35, 38, 'png', 'http://azdriversclub.com/azaz/maketa/');
            $pdf->SetXY(50, 15);
            $pdf->Cell(40, 7, "America Drivers Club", 0, 1, ' R ');
            $pdf->SetXY(50, 10);
// $pdf->Cell(40,7,'Report'.$f1."/".$f2,0, 1 , ' R ');
            $pdf->Ln(29);

            $agente = $client->id_users;
            $name = $client->name;
            $lastname = $client->apellido;
            $cardCustomer = $client->CardNumber;
            $city = $client->city;
            $dir = $client->address;
            $phone = $client->phone;
            $mail = $client->email;
            $eff = $client->effective;
            if ($client->plan == 3) {
                $exp = $client->exp;
            } else {
                $exp = $client->dateexpfinal;
            }
            $paymethod = $client->pay;
            $plan = $client->plan;
            if ($plan == 1) {
                $plan = "Monthly";
            } if ($plan == 2) {
                $plan = " 6 Months";
            } if ($plan == 3) {
                $plan = " 1 Year";
            }
            $auto = $client->Activebank;
            $service = $client->service;

            $subtotal = $client->plan;
            $total = $client->total;

            if ($paymethod == 1) {
                $paymethod = "Cash";
                $auto = "NO";
            } else if ($paymethod == 2 && $auto == 9999) {
                $paymethod = "Card-Auto";
                $auto = "YES";
            } else if ($paymethod == 2 && $auto != 9999) {
                $paymethod = "Card";
                $auto = "NO";
            }

            $fullnameCustomer = $name . " " . $lastname;

            $pdf->SetFont('Arial', 'B', 9);
//$pdf->SetFillColor(28,51,92);//Fondo rgb
//$pdf->SetTextColor(240, 255, 240); //Letra color blanco
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->SetTextColor(10, 10, 10);

            $pdf->Cell(100, 5, "Membership Information", 0, 0, 'C');
            $pdf->Cell(100, 5, "Account Information", 0, 0, 'C');
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetTextColor(10, 10, 10);
            $pdf->Ln(10);
            $pdf->Cell(100, 5, "Name:" . $name . "  " . $lastname, 0, 0, 'C');
            $pdf->Cell(100, 5, "MemberID:" . $id, 0, 0, 'C');
            $pdf->Ln(8);
//$fecha=split("-", $eff);
//$eff=$fecha[1]."-".$fecha[2]."-".$fecha[0];
            $eff = date("m-d-Y", strtotime($eff));
            $pdf->Cell(100, 5, "Effective:" . $eff, 0, 0, 'C');
            $pdf->Ln(8);
//$fecha1=split("-", $exp);
//$exp=$fecha1[1]."-".$fecha1[2]."-".$fecha1[0];
            $exp = date("m-d-Y", strtotime($exp));
            $pdf->Cell(100, 5, "Expiration:" . $exp, 0, 0, 'C');
            $pdf->Ln(8);

            $pdf->Cell(100, 5, "Agency#" . $client->id_agencia, 0, 0, 'C');
            $pdf->Ln(8);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->SetFillColor(28, 51, 92); //Fondo rgb
            $pdf->SetTextColor(240, 255, 240); //Letra color blanco
            $pdf->Ln();

            $service3 = "Road Side";
            if ($service == 1) {
                foreach ($client->vehicles as $v) {
                    if ($v->glass == "Yes" || $v->glass == "YES") {
                        $service3 = "All Services";
                    }
                }
            } else {
                $service3 = "Safety Equipment";
            }

            $pdf->Cell(65, 5, "Payment Method", 0, 0, 'C', true);
            $pdf->Cell(65, 5, "Plan", 0, 0, 'C', true);
            $pdf->Cell(65, 5, "Auto-pay", 0, 0, 'C', true);

            $pdf->Ln();
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetTextColor(10, 10, 10);
            $pdf->Cell(65, 5, $paymethod, 0, 0, 'C');
            $pdf->Cell(65, 5, $plan, 0, 0, 'C');
            $pdf->Cell(65, 5, $auto, 0, 0, 'C');

            $pdf->Ln(20);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->SetFillColor(28, 51, 92); //Fondo rgb
            $pdf->SetTextColor(240, 255, 240); //Letra color blanco
            $pdf->Ln();
            $pdf->Cell(30, 5, "Roadside", 1, 0, 'C', true);
            $pdf->Cell(30, 5, "Glass", 1, 0, 'C', true);
            $pdf->Cell(30, 5, "Year", 1, 0, 'C', true);
            $pdf->Cell(30, 5, "Make", 1, 0, 'C', true);
            $pdf->Cell(30, 5, "Model", 1, 0, 'C', true);
            $pdf->Cell(40, 5, "Vin", 1, 0, 'C', true);
            $pdf->Ln();
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetTextColor(10, 10, 10);

            foreach ($client->vehicles as $v) {
                if ($service == 1) {
                    $pdf->Cell(30, 5, "YES", 1, 0, 'C');
                } else {
                    $pdf->Cell(30, 5, "NO", 1, 0, 'C');
                }
                $pdf->Cell(30, 5, $v->glass, 1, 0, 'C');
                $pdf->Cell(30, 5, $v->year, 1, 0, 'C');
                $pdf->Cell(30, 5, $v->make, 1, 0, 'C');
                $pdf->Cell(30, 5, $v->model, 1, 0, 'C');
                $pdf->Cell(40, 5, $v->vin, 1, 0, 'C');
                $pdf->Ln();
            }

            $pdf->Ln(130);
            $pdf->SetFont('Arial', 'B', 18);
            $pdf->Cell(190, 5, "Phone:   (855)500-6912", 0, 0, 'C');

            $banderas = 0;
            if ($banderas == 1) {
                $pdf->AddPage();
                $pdf->SetFont('Arial', 'B', 15);
                $pdf->Image(Yii::getAlias('@common') . '/images/logo.png', 10, 1, 35, 38, 'png', 'http://azdriversclub.com/azaz/maketa/');
                $pdf->SetXY(50, 15);
                $pdf->Cell(40, 7, "AUTOMATIC WITHDRAWAL AUTORIZATHION FORM ", 0, 1, ' R ');
                $pdf->SetXY(50, 10);
// $pdf->Cell(40,7,'Report'.$f1."/".$f2,0, 1 , ' R ');
                $pdf->Ln(40);
                $pdf->SetFont('Arial', 'B', 9);

                $pdf->Cell(60, 5, "Mail to :Contact@americadriversclub.com", 0, 0, 'L');
                $pdf->Ln();
                $pdf->Ln();
//$pdf->Cell(60,5,"Date:__/__/____",0,0,'L');  $pdf->Ln();
                $todayEFT = date("m-d-Y");
                $pdf->Cell(60, 5, "Start Date:" . $todayEFT . "", 0, 0, 'L');
                $pdf->Ln();
                $pdf->Ln();
                $pdf->Cell(60, 5, "Member ID:" . $id . "", 0, 0, 'L');
                $pdf->Ln();
                $pdf->Ln();
                $pdf->Cell(60, 5, "Customer Name:" . $fullnameCustomer . "", 0, 0, 'L');
                $pdf->Ln();
                $pdf->Ln();

                $pdf->Ln(15);
                $pdf->SetFont('Arial', '', 12);
                $txt = "My signature below autorizes the company to initiate entries to my banking account
for premium payments via the method selected below.This authority will remain 
in effect until I notify you in writing to cancel it in such time as to afford the 
financial institution a reasonable opportunity to act on it. I can stop payment or any 
entry by notifying my financial institution  6 days before my account is charged.";
                $pdf->Cell(20, 7, "", 0, 0, 'C');
                $pdf->MultiCell(0, 4, $txt);
                $pdf->Ln(5);
                $pdf->SetTextColor(240, 255, 240); //Letra color blanco
                $pdf->Cell(60, 5, "Card Number", 1, 0, 'C', true);
                $pdf->Cell(60, 5, "Exp Date", 1, 0, 'C', true);
                $pdf->Cell(60, 5, "CVV (3-Digit Code On Back)", 1, 0, 'C', true);
                $pdf->Ln();
                $pdf->Cell(60, 9, "" . $cardCustomer . "", 1, 0, 'C');
                $pdf->Cell(60, 9, "" . $cardExpiry2 . "", 1, 0, 'C');
                $pdf->Cell(60, 9, "" . $securitycode . "", 1, 0, 'C');
                $pdf->Ln();
                $pdf->Cell(60, 5, "Cardholder Name", 1, 0, 'C', true);
                $pdf->Cell(60, 5, "Credit Card Type", 1, 0, 'C', true);
                $pdf->Cell(60, 5, "Cardholder Address", 1, 0, 'C', true);
                $pdf->Ln();
                $pdf->Cell(60, 9, "" . $fullnameCustomer . "", 1, 0, 'C');
                $pdf->Cell(60, 9, " ", 1, 0, 'C');
                $pdf->Cell(60, 9, "", 1, 0, 'C');
                $pdf->SetTextColor(10, 10, 10); //Letra color negroi
                $pdf->Ln(15);
                $pdf->Cell(60, 9, "Member Name's Signature:", 0, 0, 'C');
                $pdf->Cell(60, 9, " ", 0, 0, 'C');
                $pdf->Cell(60, 9, "Account Holder's Signature:", 0, 0, 'C');
                $pdf->ln(10);
                $pdf->Cell(60, 9, "______________________", 0, 0, 'C');
                $pdf->Cell(60, 9, " ", 0, 0, 'C');
                $pdf->Cell(60, 9, "______________________", 0, 0, 'C');
                $pdf->ln();
                $pdf->Cell(60, 9, "Date:" . $todayEFT . "", 0, 0, 'C');
                $pdf->Cell(60, 9, " ", 0, 0, 'C');
                $pdf->Cell(60, 9, "Date:" . $todayEFT . "", 0, 0, 'C');
            }

            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Image(Yii::getAlias('@common') . '/images/logo.png', 10, 1, 35, 38, 'png', 'http://AmericaDriverClub.com/');
            $pdf->SetXY(50, 15);
            $pdf->Cell(40, 7, "America Drivers Club", 0, 1, ' R ');
            $pdf->SetXY(50, 10);
// $pdf->Cell(40,7,'Report'.$f1."/".$f2,0, 1 , ' R ');
            $pdf->Ln(29);
            $pdf->Cell(180, 7, "America Drivers Club                                                                               SERVICE AND BENEFITS CONTRACT", 1, 1, 'L');
            $pdf->Ln(15);
            $pdf->Cell(180, 7, "***IMPORTANT NUMBERS:                   ", 0, 1, 'L');
            $pdf->Ln(5);
            $pdf->Cell(180, 7, "24/7- Customer service & claims                                                                                      1-855-500-6912", 1, 1, 'L');
            $pdf->Ln(15);
            $pdf->SetFont('Arial', 'I', 7);
            $txt2 = "GENERAL PROVISIONS:
Telephone monitoring:
* Members of ADC, are deemed to consent to the monitoring and recording of incoming follow-up calls.
Address/Email/Name/Debit or Credit Card Changes:
* In order to keep your membership active, and to allow us to send your information that may affect your membership, you must notify us of any name, address, email, or debit/credit card changes.
Bank charges:
* We are not responsible for any fees or charges imposed by any bank or debit/credit card issuer relating to the use of your debit/credit card or checking or savings account,  including but not limited to overdraft or credit limit fees.

MEMBERSHIP INFORMATION:
Start Date:
* Your membership begins one business day after the first payment was made and approved by the financial institution, (payments in cash still apply next business day after the signage of the contract).

Elegible Members:
* You must be a named primary or associate member on an active membership to utilize ADC benefits. A maximum of 3 associate members can be added to the primary members membership. Members must  live in the same household as the primary member. Children living away from home between the ages of 16-25 are elegible for associate membership.
Elegible Vehicles:
* Benefits can be used to provide service for any of the following private passenger vehicles eligible members may be driving or riding in. vehicles must be intended for primarily for personal use. Eligible vehicles may be owned, leased, rented or borrowed (including company cars assigned to the member for full time personal use).
Note: commercial vehicles are not eligible for this coverage.
";

            $txt3 = "Number of calls included:
* Each member will be covered up to 3, service calls per membership per 6 months. Service calls are not transferable to other members on a membership and do not carry over. ADC, will dispatch the service for additional service calls at members expense. Membership benefits will only apply to incidents that occur while membership is active.

Cancellation, Temporary suspension and Non-Renewal:
* You may cancel your membership at any time by calling 1855-500-6912, or writing to our membership office (email or regular mail). You will receive a prorated refund for the remaining full months of unused membership minus the early cancellation fee of $15.
We may suspend or cancel certain membership benefits during the membership period for the excessive use of the benefits and service we provide. Use of our emergency roadside assistance or towing service four (4) times period within any one membership period will result in the automatic suspension of that benefit until the beginning of the next membership period. If your membership includes one or more paid associate members each of those members is entitled to four additional emergency road service occurrences. Throughout the suspension period, we will continue to dispatch a service provider at your own expense.
We may cancel your membership during a membership period for any of the following reasons: 1) failure to pay membership dues; 2) material misrepresentations or fraudulent submission of a request for reimbursement ; 3) excessive use of the benefits and services. We will sent you (at your address in our membership records) written notice at least 10 days prior indicating the reason for such action.
";

            $txt4 = "ROADSIDE ASSISTANCE SERVICES DETAIL:
* TOWING, BATTERY JUMPSTARTS, FLAT TIRE CHANGE, FUEL DELIVERY, LOCKOUT SERVICES.
Service providers require that you be in your vehicle when they arrive. - you are responsible for staying in a safe place until the service provider arrives. When the service provider arrives, sign the receipt for covered expenses up to your benefit limit. You are responsible for the payment of any additional expenses not covered or in excess of your benefit limit. Emergency road service is not intended as an alternative to proper vehicle maintenance. Please maintain you vehicle in good mechanical condition.

You must call our 24/7 dispatch service to receive an emergency road service or towing benefits- 
ADC, dispatches emergency roadside assistance through a network of an independent service providers authorized to perform road and towing service to our members. If you  call us and we can not dispatch service through our network, the representative will give you an authorization number. You may then call any service provider and pay them directly for service rendered. You will be reimbursed up to your benefit limit when your submit your written reimbursement request within the 90 days of service. 
Accidents-
If roadside assistance or towing is required due to an accident, the local law enforcement official will usually arrange for service. If not please call 1-855-500-6912. For dispatch of a service provider. Towing is usually covered under auto insurance policies. If not, please send in your towing invoice for reimbursement.";
            $txt5 = "Note:
Emergency road and towing service is rendered by service providers who are independent contractors and who are neither agents nor employees of ADC. Because these independent contractors have exclusive control over their own equipment and personnel, ADC is not responsible for their acts or omissions or for the quality of any service they provide. For those same reasons, ADC assumes no liability for property damage or bodily injury, If any, caused by a service provider. Any claim involving such damage or injury should be filed directly with the responsible service provider. ADC can't guaranteed repairs, the hours of operation of repair facilities, the promptness of repairs, or provide more than one tow per breakdown. It is the members responsibility to arrange for repairs with the service facility.


TOWING:
Up to 15 miles per occurrence in arizona , out of state reimbursement up to 15 miles.
BATTERY JUMPSTART:
The service provider will provide battery jumpstart or tow if your vehicle won't start due a dead or weak battery.
FLAT TIRE CHANGE:
The service provider will change a flat tire with your inflated spared tire. If for any reason your spare is not usable, the lug nuts can not be removed, or your vehicle has multiple flat tires, towing will be provided. Towing benefits will be apply. Costs of tire repair, installing new tire on the wheel, or a second service call to return a tire to the disable vehicle are not covered.
FUEL DELIVERY:
If you are run out of gas, the service provider will deliver an emergency supply of gasoline/diesel, fuel or tow your vehicle to the nearest gas station. Towing benefits limits apply. (gas/diesel are provided as courtesy up to 2 gallons)
LOCKOUT SERVICE:
If you are locked out of your eligible vehicle, you must call the 1-855-500-6912 for service. Expenses that are not covered include, but are not limited to, labor to produce keys, replacement keys, and mechanical failure of locks or ignition system.
WINDSHIELD PROTECTION:
If your windshield is damaged please follow the instructions below: 
ALL windshield claims will be handled by calling the 1-855-500-6912.   Once you have submitted a claim, and receive a claim number, you will be entitled to receive services throughout ADC windshield providers. You are entitled up to 2 glass claims per six months per membership.
";
            $txt6 = "
___  6 Months , Month to  Month Payment.                                         
___  6 Months ,  Pay In Full.                                                
___  12 Months , Month to  Month Payment
___  12 Months , Pay In Full.
";
            $pdf->MultiCell(0, 5, $txt2);
            $pdf->MultiCell(0, 5, $txt3);
            $pdf->MultiCell(0, 5, $txt4);
            $pdf->MultiCell(0, 5, $txt5);
            $pdf->MultiCell(0, 5, $txt6);
            $pdf->Ln();
            $pdf->Cell(60, 9, "Member Name's /Signature:", 0, 0, 'C');
            $pdf->Cell(60, 9, " ", 0, 0, 'C');
            $todayDATE = date("m-d-Y");
            $pdf->Cell(60, 9, "Date:" . $todayDATE . "", 0, 0, 'C');
            $pdf->ln(10);
            $pdf->Cell(60, 9, "______________________", 0, 0, 'C');
            $pdf->Cell(60, 9, " ", 0, 0, 'C');
            $pdf->Cell(60, 9, "______________________", 0, 0, 'C');
            $pdf->ln();

            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 15);
            $pdf->Image(Yii::getAlias('@common') . '/images/logo.png', 10, 1, 35, 38, 'png', 'http://azdriversclub.com/azaz/maketa/');
            $pdf->SetXY(50, 15);
            $pdf->Cell(40, 7, "America Drivers Club", 0, 1, ' R ');
            $pdf->SetXY(50, 10);
            $pdf->Ln(30);
            $pdf->SetFont('Arial', 'I', 7);
            $txt42 = "ROADSIDE ASSISTANCE SERVICES DETAIL:
* TOWING, BATTERY JUMPSTARTS, FLAT TIRE CHANGE, FUEL DELIVERY, LOCKOUT SERVICES.
Service providers require that you be in your vehicle when they arrive. - you are responsible for staying in a safe place until the service provider arrives. When the service provider arrives, sign the receipt for covered expenses up to your benefit limit. You are responsible for the payment of any additional expenses not covered or in excess of your benefit limit. Emergency road service is not intended as an alternative to proper vehicle maintenance. Please maintain you vehicle in good mechanical condition.

You must call our 24/7 dispatch service to receive an emergency road service or towing benefits- 
ADC, dispatches emergency roadside assistance through a network of an independent service providers authorized to perform road and towing service to our members. If you  call us and we can not dispatch service through our network, the representative will give you an authorization number. You may then call any service provider and pay them directly for service rendered. You will be reimbursed up to your benefit limit when your submit your written reimbursement request within the 90 days of service. 
Accidents-
If roadside assistance or towing is required due to an accident, the local law enforcement official will usually arrange for service. If not please call 1-855-500-6912. For dispatch of a service provider. Towing is usually covered under auto insurance policies. If not, please send in your towing invoice for reimbursement.";
            $txt52 = "Note:
Emergency road and towing service is rendered by service providers who are independent contractors and who are neither agents nor employees of ADC. Because these independent contractors have exclusive control over their own equipment and personnel, ADC is not responsible for their acts or omissions or for the quality of any service they provide. For those same reasons, ADC assumes no liability for property damage or bodily injury, If any, caused by a service provider. Any claim involving such damage or injury should be filed directly with the responsible service provider. ADC can't guaranteed repairs, the hours of operation of repair facilities, the promptness of repairs, or provide more than one tow per breakdown. It is the members responsibility to arrange for repairs with the service facility.
TOWING:
Up to 15 miles per occurrence in arizona , out of state reimbursement up to 15 miles.
BATTERY JUMPSTART:
The service provider will provide battery jumpstart or tow if your vehicle won't start due a dead or weak battery.
FLAT TIRE CHANGE:
The service provider will change a flat tire with your inflated spared tire. If for any reason your spare is not usable, the lug nuts can not be removed, or your vehicle has multiple flat tires, towing will be provided. Towing benefits will be apply. Costs of tire repair, installing new tire on the wheel, or a second service call to return a tire to the disable vehicle are not covered.
FUEL DELIVERY:
If you are run out of gas, the service provider will deliver an emergency supply of gasoline/diesel, fuel or tow your vehicle to the nearest gas station. Towing benefits limits apply. (gas/diesel are provided as courtesy up to 2 gallons)
LOCKOUT SERVICE:
If you are locked out of your eligible vehicle, you must call the 1-855-500-6912 for service. Expenses that are not covered include, but are not limited to, labor to produce keys, replacement keys, and mechanical failure of locks or ignition system.
WINDSHIELD PROTECTION:
If your windshield is damaged please follow the instructions below: 
ALL windshield claims will be handled by calling the 1-855-500-6912.   Once you have submitted a claim, and receive a claim number, you will be entitled to receive services throughout ADC windshield providers. You are entitled up to 2 glass claims per six months per membership.
";
            $txt62 = "
___  6 Months , Month to  Month Payment.                                         
___  6 Months ,  Pay In Full.                                                
___  12 Months , Month to  Month Payment
___  12 Months , Pay In Full.
";

            $pdf->MultiCell(0, 5, $txt42);
            $pdf->MultiCell(0, 5, $txt52);
            $pdf->MultiCell(0, 5, $txt62);
            $pdf->Ln();
            $pdf->Cell(60, 9, "Member Name's /Signature:", 0, 0, 'C');
            $pdf->Cell(60, 9, " ", 0, 0, 'C');
            $pdf->Cell(60, 9, "Date:" . $todayDATE . "", 0, 0, 'C');
            $pdf->ln(10);
            $pdf->Cell(60, 9, "______________________", 0, 0, 'C');
            $pdf->Cell(60, 9, " ", 0, 0, 'C');
            $pdf->Cell(60, 9, "______________________", 0, 0, 'C');
            $pdf->ln();

            return $pdf->Output();
        } else {
            throw new \yii\web\NotFoundHttpException();
        }
    }

    public function CreateSalesReport($tickets) {
// $request = Yii::$app->request;
//$tickets = Yii::$app->getRequest()->getBodyParams('tickets');
//$tickets = $request->post('tickets');
        $array = array();
        array_push($array, $tickets);
//$val = $tickets['tickets'][0]['agcode'];



        if ($tickets != null) {

            $pdf = new \FPDF();

            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 15);
            $pdf->Image(Yii::getAlias('@common') . '/images/logo.png', 10, 1, 35, 38, 'png', 'http://azdriversclub.com/azaz/maketa/');
            $pdf->SetXY(50, 15);
            $pdf->Cell(40, 7, "America Drivers Club", 0, 1, ' R ');
            $pdf->SetXY(50, 10);
// $pdf->Cell(40,7,'Report'.$f1."/".$f2,0, 1 , ' R ');
            $pdf->Ln(35);
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(15, 5, "#", 1, 0, 'C');
            $pdf->Cell(25, 5, "Agency", 1, 0, 'C');
            $pdf->Cell(25, 5, "Agent", 1, 0, 'C');
            $pdf->Cell(45, 5, "Customer", 1, 0, 'C');
            $pdf->Cell(35, 5, "PM", 1, 0, 'C');
            $pdf->Cell(35, 5, "Type", 1, 0, 'C');
            $pdf->Cell(15, 5, "Total", 1, 0, 'C');
            $pdf->Ln();
            $cont = 1;
            for ($i = 0; $i < count($tickets); $i++) {

                $pdf->Cell(15, 5, $cont, 1, 0, 'C');
                $pdf->Cell(25, 5, $tickets[$i]['ag'], 1, 0, 'C');
                $pdf->Cell(25, 5, $tickets[$i]['agente'], 1, 0, 'C');
                $pdf->Cell(45, 5, $tickets[$i]['name'] . " " . $tickets[$i]['lastname'], 1, 0, 'C');
                $pdf->Cell(35, 5, $tickets[$i]['payment'], 1, 0, 'C');
                $pdf->Cell(35, 5, $tickets[$i]['label_status'], 1, 0, 'C');
                $pdf->Cell(15, 5, $tickets[$i]['total'], 1, 0, 'C');
                $pdf->Ln();
                $cont++;
            }
            $folder = Yii::getAlias('@common') . '/pdf/temp_pdf/';
            $name = $folder . "SalesReport.pdf";

            return $pdf->Output($name, "F");
        }
    }

    public function CreateClaimReport($claims) {
// $request = Yii::$app->request;
//$tickets = Yii::$app->getRequest()->getBodyParams('tickets');
//$tickets = $request->post('tickets');
        $array = array();
        array_push($array, $claims);
//$val = $tickets['tickets'][0]['agcode'];



        if ($claims != null) {

            $pdf = new \FPDF();

            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 15);
            $pdf->Image(Yii::getAlias('@common') . '/images/logo.png', 10, 1, 35, 38, 'png', 'http://azdriversclub.com/azaz/maketa/');
            $pdf->SetXY(50, 15);
            $pdf->Cell(40, 7, "America Drivers Club", 0, 1, ' R ');
            $pdf->SetXY(50, 10);
// $pdf->Cell(40,7,'Report'.$f1."/".$f2,0, 1 , ' R ');
            $pdf->Ln(35);
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(35, 5, "Agency", 1, 0, 'C');
            $pdf->Cell(35, 5, "Customer", 1, 0, 'C');
            $pdf->Cell(35, 5, "Status", 1, 0, 'C');
            $pdf->Cell(35, 5, "Claim#", 1, 0, 'C');
            $pdf->Cell(35, 5, "Total", 1, 0, 'C');
            $pdf->Ln();

            for ($i = 0; $i < count($claims); $i++) {
                $value = "";
                if ($claims[$i]['status'] == 2) {
                    $value = "Close";
                } else {
                    $value = "Open";
                }
                $pdf->Cell(35, 5, $claims[$i]['id_agencia'], 1, 0, 'C');
                $pdf->Cell(35, 5, $claims[$i]['nom'], 1, 0, 'C');
                $pdf->Cell(35, 5, $value, 1, 0, 'C');
                $pdf->Cell(35, 5, $claims[$i]['id'], 1, 0, 'C');
                $pdf->Cell(35, 5, $claims[$i]['total'], 1, 0, 'C');
                $pdf->Ln();
            }
            $folder = Yii::getAlias('@common') . '/pdf/temp_pdf/';
            $name = $folder . "ClaimsReport.pdf";

            return $pdf->Output($name, "F");
        }
    }

    public function actionConfigure_autopay() {
        $request = Yii::$app->request;
        $id = $request->post('id_client');
        $total = $request->post('paid');
        $modelClient = new Client();
        $modelClient = Client::findOne($id);
        $modelClient->paid = $total;
        $cardInfo = Yii::$app->getRequest()->getBodyParams('cardInfo');
        return $this->DoPaymentAutoPay($modelClient, $cardInfo);
    }

    public function actionAutopayautomatic() {
        file_put_contents("transaction.log", "\n\n/********************** " . date('Y-m-d H:i:s') . " *************************/\n", FILE_APPEND);
        file_put_contents("transaction.log", print_r($_POST, true), FILE_APPEND);
// Get the subscription ID if it is available. 
// Otherwise $subscription_id will be set to zero. 
        $request = Yii::$app->request;
        $subscription_id = (int) $request->post('x_subscription_id');
// Check to see if we got a valid subscription ID. 
// If so, do something with it. 
        if ($subscription_id) {
// Get the response code. 1 is success, 2 is decline, 3 is error 
            $response_code = (int) $request->post('x_response_code');
// Get the reason code. 8 is expired card. 
            $reason_code = (int) $request->post('x_response_reason_code');
            if ($response_code == 1) {
// Approved! 
// Some useful fields might include: 
// $authorization_code = $_POST['x_auth_code']; 
// $avs_verify_result = $_POST['x_avs_code']; 
// $transaction_id = $_POST['x_trans_id']; 
// $customer_id = $_POST['x_cust_id']; 
//save Ticket here
                $modelClient = new Client();
                $modelClient = Client::find('subscriptionID=' . $subscription_id)->where()->one();

                $agente = "ADC" . $modelClient->id_agencia;
//DoPaymentCash($modelClient, "INVOICE", Yii::$app->user->identity->user, 0, 0, "CASH", true, $cardInfo, $enbaleAutopay);
                $this->DoARB($modelClient, "INVOICE", $agente, 0, 0, "Auto-Pay", false);
            } else if ($response_code == 2) {
// Declined 
            } else if ($response_code == 3 && $reason_code == 8) {
// An expired card 
            } else {
// Other error 
            }
        }
    }

    public function DoARB(Client $modelClient, $type, $agente, $tpaymode, $addcover, $payment, $newBusiness) {


        $today = date("Y-m-d");
        $plan = $modelClient->plan;
        $effectiveDate = $modelClient->effective;
        $marginDatePayment = $modelClient->fechaMed;
        $expirationDate = $modelClient->exp;
        $total = $modelClient->paid;

        $newBusiness = false;
        $isARB = true;

        if ($newBusiness == true) {// firt payment firt day policy is today not necessary changes
            $label = "New_Business";
            $r = $this->Save_ticket($modelClient->id, $type, $today, $agente, $modelClient->total, $payment, $tpaymode, $addcover, $label);
            if ($r == true) {
                return true;
            } else {
                return false;
            }
        } else if ($today <= $marginDatePayment && $today < $expirationDate && $effectiveDate != $expirationDate) {/* payment period */

            $label = "Payment";
            $r = $this->Save_ticket($modelClient->id, $type, $today, $agente, $modelClient->total, $payment, $tpaymode, $addcover, $label);

            if ($r == true) {
                return $this->updateDatesInPeriod($modelClient->id, 1, $label);
            } else {
                return false;
            }
        } else if ($today > $marginDatePayment && $today < $expirationDate) { /* plocy canceled heere */

            $label = "Re-Active";
            $r = $this->Save_ticket($modelClient->id, $type, $today, $agente, $modelClient->total, $payment, $tpaymode, $addcover, $label);
            if ($r == true) {
                return $this->updateDatesInPeriod($modelClient->id, 2, $label);
            } else {
                return false;
            }
        } else if ($today <= $marginDatePayment && $effectiveDate == $expirationDate) {
            $label = "Renew";
            $r = $this->Save_ticket($modelClient->id, $type, $today, $agente, $modelClient->total, $payment, $tpaymode, $addcover, $label);

            if ($r == true) {
                return $this->RenewMembership($modelClient->id, $label);
            } else {
                return false;
            }
        } else if ($today >= $expirationDate) { ///Rewrite
            $label = "Rewrite";
            $r = $this->Save_ticket($modelClient->id, $type, $today, $agente, $modelClient->total, $payment, $tpaymode, $addcover, $label);
            if ($r == true) {
                return $this->RewriteMembership($modelClient->id, $label);
            } else {
                return false;
            }
        }
    }

    public function actionGet_clients_by_status() {
        $request = Yii::$app->request;
        $status = $request->post('status');

        $idAgency = Yii::$app->user->identity->agencia;
        $agency = new Agency();
        $agency = Agency::find()->where("id=" . $idAgency)->one();
        $owner = $agency->id_owner;
        $agencies = Agency::find()->where("id_owner=" . $owner)->all();
        $strtlo = "";
        $iterator = 1;
        $number = count($agencies);

        foreach ($agencies as &$agency) {

            if ($iterator < $number) {
                $strtlo .= "id_agencia=" . $agency->id . " or  ";
            } else if ($iterator == $number) {
                $strtlo .= "id_agencia=" . $agency->id;
            }
            $iterator++;
        }

        $Clients;
        if ($status == 1) {
            $Clients = Client::find()
                    ->Where('habilitado=1')
                    ->andWhere("CURDATE() <= fechaMed")
                    ->andWhere($strtlo)
                    ->all();
        } else if ($status == 2) {
            $Clients = Client::find()
                    ->Where('habilitado=1')
                    ->andWhere("CURDATE() > fechaMed")
                    ->andWhere("CURDATE() <= DATE_ADD(fechaMed, INTERVAL 7 DAY)")
                    ->andWhere($strtlo)
                    ->all();
            // DATE_ADD('1998-01-02', INTERVAL 31 DAY);
        } else if ($status == 3) {
            $Clients = Client::find()
                    ->Where('habilitado=1')
                    ->andWhere("CURDATE() > DATE_ADD(fechaMed, INTERVAL 7 DAY)")
                    ->andWhere("CURDATE() <= exp")
                    ->andWhere($strtlo)
                    ->all();
        } else if ($status == 4) {
            $Clients = Client::find()
                    ->Where('habilitado=1')
                    ->andWhere("CURDATE() > exp")
                    ->andWhere($strtlo)
                    ->all();
        }
        // $Clients = (new Query())->select('*')->from('clients')->where('Status_client=' . $status . ' and id_agencia=' . $agency . ' and habilitado=1')->all();
        if ($Clients != null) {
            return $Clients;
        }
    }

    public function actionStatusform($status) {

        $pdf = new \FPDF();

        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 15);
        $pdf->Image(Yii::getAlias('@common') . '/images/logo.png', 10, 1, 35, 38, 'png', 'http://azdriversclub.com/azaz/maketa/');
        $pdf->SetXY(50, 15);
        $pdf->Cell(40, 7, "America Drivers Club", 0, 1, ' R ');
        $pdf->SetXY(50, 10);
// $pdf->Cell(40,7,'Report'.$f1."/".$f2,0, 1 , ' R ');
        $pdf->Ln(35);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(15, 5, "#", 1, 0, 'C');
        $pdf->Cell(20, 5, "Agency", 1, 0, 'C');
        $pdf->Cell(25, 5, "Membership #", 1, 0, 'C');
        $pdf->Cell(45, 5, "Customer", 1, 0, 'C');
        $pdf->Cell(35, 5, "Phone", 1, 0, 'C');
        $pdf->Cell(20, 5, "Total", 1, 0, 'C');
        $pdf->Cell(35, 5, "Status", 1, 0, 'C');
        $pdf->Ln();
        $label = "";
        $Clients;

        $idAgency = Yii::$app->user->identity->agencia;
        $agency = new Agency();
        $agency = Agency::find()->where("id=" . $idAgency)->one();
        $owner = $agency->id_owner;
        $agencies = Agency::find()->where("id_owner=" . $owner)->all();
        $strtlo = "";
        $iterator = 1;
        $number = count($agencies);

        foreach ($agencies as &$agency) {

            if ($iterator < $number) {
                $strtlo .= "id_agencia=" . $agency->id . " or  ";
            } else if ($iterator == $number) {
                $strtlo .= "id_agencia=" . $agency->id;
            }
            $iterator++;
        }
        if ($status == 1) {
            $label = "Active";
            $Clients = Client::find()
                    ->Where('habilitado=1')
                    ->andWhere("CURDATE() <= fechaMed")
                    ->andWhere($strtlo)
                    ->all();
        } else if ($status == 2) {
            $label = "Pending Cancellation";
            $Clients = Client::find()
                    ->Where('habilitado=1')
                    ->andWhere("CURDATE() > fechaMed")
                    ->andWhere("CURDATE() <= DATE_ADD(fechaMed, INTERVAL 7 DAY)")
                    ->andWhere($strtlo)
                    ->all();
            // DATE_ADD('1998-01-02', INTERVAL 31 DAY);
        } else if ($status == 3) {
            $label = "Cancelled";
            $Clients = Client::find()
                    ->Where('habilitado=1')
                    ->andWhere("CURDATE() > DATE_ADD(fechaMed, INTERVAL 7 DAY)")
                    ->andWhere("CURDATE() <= exp")
                    ->andWhere($strtlo)
                    ->all();
        } else if ($status == 4) {
            $label = "Expired";
            $Clients = Client::find()
                    ->Where('habilitado=1')
                    ->andWhere("CURDATE() > exp")
                    ->andWhere($strtlo)
                    ->all();
        }
        $cont = 1;
        foreach ($Clients as $c) {

            $pdf->Cell(15, 5, $cont, 1, 0, 'C');
            $pdf->Cell(20, 5, $c->id_agencia, 1, 0, 'C');
            $pdf->Cell(25, 5, $c->id, 1, 0, 'C');
            $pdf->Cell(45, 5, $c->name . " " . $c->apellido, 1, 0, 'C');
            $pdf->Cell(35, 5, $c->phone, 1, 0, 'C');
            $pdf->Cell(20, 5, $c->total, 1, 0, 'C');
            $pdf->Cell(35, 5, $label, 1, 0, 'C');
            $pdf->Ln();
            $cont++;
        }

        return $pdf->Output();
    }

    public function actionGet_notes_last() {
        $request = Yii::$app->request;
        $id_client = $request->post('id_client');
        $notes = NotasAzdriver::find()->where("client_id=" . $id_client)->limit(5)->orderBy(['date' => SORT_DESC])->all();
        return $notes;
        //  $tickets = Tickets::find()->where(['>', 'total', 0])->andWhere(['id_client' => $id_client])->limit(5)->orderBy(['date' => SORT_DESC])->all();
    }

    public function actionGet_all_notes() {
        $request = Yii::$app->request;
        $id_client = $request->post('id_client');
        $notes = NotasAzdriver::find()->where("client_id=" . $id_client)->orderBy(['date' => SORT_DESC])->all();
        return $notes;
    }

    public function actionSave_note() {
        $request = Yii::$app->request;
        $note = $request->post('note');
        $user = Yii::$app->user->identity;
        $nota = new NotasAzdriver();

        $nota->client_id = $note['client_id'];
        $nota->date = date("Y-m-d");
        $nota->note = $note['note'];
        $nota->user_id = $user->id;
        return $nota->save(false);
    }

    public function actionVin_decode() {
        $request = Yii::$app->request;
        $vin = $request->post('vin');
        $ch = curl_init('https://api.dataonesoftware.com/webservices/vindecoder/decode');
        $post_vars = 'client_id=13595 &authorization_code=768eb998f8ae24225426db31da5f70bf5dcc46c0 &decoder_query=';
        $post_vars .= '
<decoder_query>
   <decoder_settings>
      <display>full</display>
      <styles>on</styles>
      <style_data_packs>
         <basic_data>on</basic_data>
         <pricing>on</pricing>
         <engines>on</engines>
         <transmissions>on</transmissions>
         <specifications>on</specifications>
         <installed_equipment>on</installed_equipment>
         <optional_equipment>off</optional_equipment>
         <generic_optional_equipment>on</generic_optional_equipment>
         <colors>on</colors>
         <warranties>on</warranties>
         <fuel_efficiency>on</fuel_efficiency>
         <green_scores>on</green_scores>
         <crash_test>on</crash_test>
      </style_data_packs>
      <common_data>on</common_data>
      <common_data_packs>
         <basic_data>on</basic_data>
         <pricing>on</pricing>
         <engines>on</engines>
         <transmissions>on</transmissions>
         <specifications>on</specifications>
         <installed_equipment>on</installed_equipment>
         <generic_optional_equipment>on</generic_optional_equipment>
      </common_data_packs>
   </decoder_settings>
   <query_requests>
      <query_request identifier="PHP Example">
         <vin>' . $vin . '</vin>
         <year/>
         <make/>
         <model/>
         <trim/>
         <model_number/>
         <package_code/>
         <drive_type/>
         <vehicle_type/>
         <body_type/>
         <doors/>
         <bedlength/>
         <wheelbase/>
         <msrp/>
         <invoice_price/>
         <engine description="">
            <block_type/>
            <cylinders/>
            <displacement/>
            <fuel_type/>
         </engine>
         <transmission description="">
            <trans_type/>
            <trans_speeds/>
         </transmission>
         <optional_equipment_codes/>
         <installed_equipment_descriptions/>
         <interior_color description="">
            <color_code/>
         </interior_color>
         <exterior_color description="">
            <color_code/>
         </exterior_color>
      </query_request>
   </query_requests>
</decoder_query>';

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_vars);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this accepts any SSL certificate
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $result = curl_exec($ch); // $result now contains the response JSON or XML.

        $xml = simplexml_load_string($result) or die("Error");


        $market = "";
        $year = "";
        $model = "";
        $query_responses = $xml->query_responses;
        $vehicle = new Vehicles();
        $vehicle->vin = $vin;
        foreach ($query_responses as $key => $value) {
            $result = $value->xpath("query_response/us_market_data/common_us_data");
            foreach ($result[0] as $k => $v) {
                //echo "D".$k."<br>";
                foreach ($v as $kk => $vv) {
                    if (!$vv->attributes()) {
                        if ($kk == "make") {

                            $vehicle->make = $vv . "";
                        }

                        if ($kk == "model") {
                            $vehicle->model = $vv . "";
                        }
                        if ($kk == "year") {
                            $vehicle->year = $vv . "";
                        }
                    }
                }
            }
        }






        return $vehicle;
    }

    public function actionGet_postulans() {
        $users = \common\models\Users::find()
                ->where("isReseller=1")
                ->andWhere("habilitado=0")
                ->all();
        return $users;
    }

}
