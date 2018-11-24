<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "clients".
 *
 * @property int $id
 * @property string $id_users
 * @property string $exp
 * @property string $user
 * @property string $pass
 * @property string $email
 * @property string $name
 * @property string $apellido
 * @property string $date_login
 * @property string $fecAlta
 * @property int $plan
 * @property string $pay
 * @property int $paymode
 * @property string $ip_address
 * @property string $subscriptionID
 * @property string $pass_status
 * @property int $nivel
 * @property int $active
 * @property string $phone
 * @property string $date_birth
 * @property string $effective
 * @property string $dln
 * @property string $address
 * @property string $zip
 * @property string $state
 * @property string $city
 * @property double $total
 * @property double $paid
 * @property double $pending
 * @property string $ticket_cars
 * @property string $ticket_bank
 * @property string $ticket_invoice
 * @property int $service
 * @property string $bankstatus
 * @property double $total3
 * @property double $total2
 * @property double $total4
 * @property string $expiration
 * @property double $total5
 * @property string $fechaMed
 * @property string $compania
 * @property string $CardNumber
 * @property int $Activebank
 * @property int $Plan_client
 * @property int $Status_client
 * @property string $Dpayment
 * @property int $habilitado
 * @property int $id_agencia
 * @property string $docbaja
 * @property string $contrato
 * @property double $premium
 * @property string $dateexpfinal
 * @property int $agcode
 * @property double $diff
 * @property int $estservicio
 * @property string $lat
 * @property string $lon
 * @property string $fechabaja
 * @property int $MF
 * @property int $idioma
 * @property string $notas
 * @property string $phone2
 * @property int $deleted
 * @property double $credit_value
 * @property string $payment_date_credit
 *
 *
 * @property Users $users
 */
class Client extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public function __construct() {
        
    }

    public static function tableName() {
        return 'clients';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['id_users', 'plan', 'pay', 'credit_value', 'payment_date_credit', 'phone', 'total3', 'total2', 'total4', 'total5', 'fechaMed', 'exp', 'compania', 'Activebank', 'dateexpfinal', 'agcode', 'idioma'], 'required'],
            [['exp', 'fecAlta', 'date_birth', 'effective', 'expiration', 'fechaMed', 'dateexpfinal', 'fechabaja'], 'safe'],
            [['plan', 'paymode', 'nivel', 'active', 'service', 'Activebank', 'Plan_client', 'Status_client', 'habilitado', 'id_agencia', 'agcode', 'estservicio', 'MF', 'idioma', 'deleted'], 'integer'],
            [['total', 'paid', 'pending', 'total3', 'total2', 'total4', 'total5', 'premium', 'diff'], 'number'],
            [['docbaja', 'contrato', 'lat', 'lon', 'notas'], 'string'],
            [['id_users', 'user', 'pass', 'email', 'name', 'apellido', 'date_login', 'pay', 'ip_address', 'pass_status', 'phone', 'dln', 'address', 'zip', 'state', 'city', 'bankstatus', 'compania', 'CardNumber'], 'string', 'max' => 50],
            [['subscriptionID', 'ticket_cars', 'ticket_bank', 'ticket_invoice'], 'string', 'max' => 255],
            [['Dpayment'], 'string', 'max' => 25],
            [['phone2'], 'string', 'max' => 20],
            [['id_users'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['id_users' => 'user']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'id_users' => 'Id Users',
            'exp' => 'Exp',
            'user' => 'User',
            'pass' => 'Pass',
            'email' => 'Email',
            'name' => 'Name',
            'apellido' => 'Apellido',
            'date_login' => 'Date Login',
            'fecAlta' => 'Fec Alta',
            'plan' => 'Plan',
            'pay' => 'Pay',
            'paymode' => 'Paymode',
            'ip_address' => 'Ip Address',
            'subscriptionID' => 'Subscription I D',
            'pass_status' => 'Pass Status',
            'nivel' => 'Nivel',
            'active' => 'Active',
            'phone' => 'Phone',
            'date_birth' => 'Date Birth',
            'effective' => 'Effective',
            'dln' => 'Dln',
            'address' => 'Address',
            'zip' => 'Zip',
            'state' => 'State',
            'city' => 'City',
            'total' => 'Total',
            'ticket_cars' => 'Ticket Cars',
            'ticket_bank' => 'Ticket Bank',
            'ticket_invoice' => 'Ticket Invoice',
            'service' => 'Service',
            'bankstatus' => 'Bankstatus',
            'total3' => 'Total3',
            'total2' => 'Total2',
            'total4' => 'Total4',
            'expiration' => 'Expiration',
            'total5' => 'Total5',
            'fechaMed' => 'Fecha Med',
            'compania' => 'Compania',
            'CardNumber' => 'Card Number',
            'Activebank' => 'Activebank',
            'Plan_client' => 'Plan Client',
            'Status_client' => 'Status Client',
            'Dpayment' => 'Dpayment',
            'habilitado' => 'Habilitado',
            'id_agencia' => 'Id Agencia',
            'docbaja' => 'Docbaja',
            'contrato' => 'Contrato',
            'premium' => 'Premium',
            'dateexpfinal' => 'Dateexpfinal',
            'agcode' => 'Agcode',
            'diff' => 'Diff',
            'estservicio' => 'Estservicio',
            'lat' => 'Lat',
            'lon' => 'Lon',
            'fechabaja' => 'Fechabaja',
            'MF' => 'M F',
            'idioma' => 'Idioma',
            'notas' => 'Notas',
            'phone2' => 'Phone2',
            'deleted' => 'Deleted',
            'credit_value' => 'Credit_value',
            'payment_date_credit' => 'Payment_date_credit',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers() {
        return $this->hasOne(Users::className(), ['user' => 'id_users']);
    }

    public function getTickets() {
        return $this->hasMany(Tickets::className(), ['id_client' => 'id']);
    }

    public function getVehicles() {
        return $this->hasMany(Vehicles::className(), ['id_client' => 'id']);
    }

}
