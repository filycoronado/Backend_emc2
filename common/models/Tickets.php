<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tickets".
 *
 * @property int $id
 * @property int $id_client
 * @property string $file
 * @property string $type
 * @property string $date
 * @property string $agente
 * @property double $total
 * @property string $payment
 * @property int $tpaymode
 * @property int $id_agencia
 * @property int $agcode
 * @property int $addcover
 * @property int $free
 * @property string $label_status
 */
class Tickets extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'tickets';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['id_client', 'type', 'date', 'agente', 'total', 'payment', 'agcode', 'addcover'], 'required'],
            [['id_client', 'tpaymode', 'id_agencia', 'agcode', 'addcover', 'free'], 'integer'],
            [['date'], 'safe'],
            [['total'], 'number'],
            [['file', 'type', 'agente', 'payment','label_status'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'id_client' => 'Id Client',
            'file' => 'File',
            'type' => 'Type',
            'date' => 'Date',
            'agente' => 'Agente',
            'total' => 'Total',
            'payment' => 'Payment',
            'tpaymode' => 'Tpaymode',
            'id_agencia' => 'Id Agencia',
            'agcode' => 'Agcode',
            'addcover' => 'Addcover',
            'free' => 'Free',
            'label_status'=>'label_status',
        ];
    }

    public function getClient() {
        return $this->hasOne(Client::className(), ['id' => 'id_client']);/*fk, lk*/
    }

}
