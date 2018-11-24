<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "claims".
 *
 * @property int $id
 * @property int $id_poliza
 * @property string $claim
 * @property int $tipoclaim
 * @property int $servicio
 * @property string $nom
 * @property string $telefono
 * @property string $fecha
 * @property int $autoestado
 * @property string $color
 * @property int $member_id
 * @property string $dirorigen
 * @property string $dirdestino
 * @property int $cantuso
 * @property double $total
 * @property string $documento
 * @property string $vin
 * @property string $make
 * @property string $model
 * @property string $year
 * @property string $provider
 * @property string $placas
 * @property int $status
 * @property int $pagado
 * @property int $id_agencia
 * @property int $id_usuario
 * @property int $idprovedor
 * @property string $mtv
 * @property string $lm
 * @property int $tc
 * @property string $lat
 * @property string $lon
 */
class Claims extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'claims';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_poliza', 'tipoclaim', 'servicio', 'autoestado', 'member_id', 'cantuso', 'status', 'pagado', 'id_agencia', 'id_usuario', 'idprovedor', 'tc'], 'integer'],
            [['claim'], 'required'],
            [['claim', 'documento', 'vin', 'provider', 'lat', 'lon'], 'string'],
            [['fecha'], 'safe'],
            [['total'], 'number'],
            [['nom', 'telefono', 'color', 'dirorigen', 'dirdestino', 'make', 'year', 'mtv', 'lm'], 'string', 'max' => 25],
            [['model'], 'string', 'max' => 40],
            [['placas'], 'string', 'max' => 30],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_poliza' => 'Id Poliza',
            'claim' => 'Claim',
            'tipoclaim' => 'Tipoclaim',
            'servicio' => 'Servicio',
            'nom' => 'Nom',
            'telefono' => 'Telefono',
            'fecha' => 'Fecha',
            'autoestado' => 'Autoestado',
            'color' => 'Color',
            'member_id' => 'Member ID',
            'dirorigen' => 'Dirorigen',
            'dirdestino' => 'Dirdestino',
            'cantuso' => 'Cantuso',
            'total' => 'Total',
            'documento' => 'Documento',
            'vin' => 'Vin',
            'make' => 'Make',
            'model' => 'Model',
            'year' => 'Year',
            'provider' => 'Provider',
            'placas' => 'Placas',
            'status' => 'Status',
            'pagado' => 'Pagado',
            'id_agencia' => 'Id Agencia',
            'id_usuario' => 'Id Usuario',
            'idprovedor' => 'Idprovedor',
            'mtv' => 'Mtv',
            'lm' => 'Lm',
            'tc' => 'Tc',
            'lat' => 'Lat',
            'lon' => 'Lon',
        ];
    }
    
    public function getClient(){
         return $this->hasOne(Client::className(), ['member_id' => 'id']);
    }
}
