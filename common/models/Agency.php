<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "agency".
 *
 * @property int $id
 * @property string $nombre
 * @property string $direccion
 * @property string $email
 * @property string $ciudad
 * @property string $estado
 * @property string $zip
 * @property string $DocumentoReporte
 * @property string $ReporteClaims
 * @property string $su
 * @property string $password
 * @property int $Mactive
 * @property int $Cactive
 * @property int $Dactive
 * @property double $Comision
 * @property string $CommisionReport
 * @property string $mgtmreport
 * @property string $reportereloj
 * @property string $tel
 * @property string $fax
 * @property string $website
 * @property int $tipopago
 * @property string $reportdealer
 * @property int $id_owner
 */
class Agency extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'agency';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre', 'DocumentoReporte', 'ReporteClaims', 'su', 'password', 'CommisionReport', 'mgtmreport', 'reportereloj', 'tel', 'fax', 'website'], 'string'],
            [['DocumentoReporte', 'ReporteClaims', 'su', 'password', 'CommisionReport', 'mgtmreport', 'reportereloj', 'tel', 'fax', 'website', 'tipopago', 'reportdealer', 'id_owner'], 'required'],
            [['Mactive', 'Cactive', 'Dactive', 'tipopago', 'id_owner'], 'integer'],
            [['Comision'], 'number'],
            [['direccion'], 'string', 'max' => 50],
            [['email', 'ciudad', 'estado', 'zip'], 'string', 'max' => 30],
            [['reportdealer'], 'string', 'max' => 40],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nombre' => 'Nombre',
            'direccion' => 'Direccion',
            'email' => 'Email',
            'ciudad' => 'Ciudad',
            'estado' => 'Estado',
            'zip' => 'Zip',
            'DocumentoReporte' => 'Documento Reporte',
            'ReporteClaims' => 'Reporte Claims',
            'su' => 'Su',
            'password' => 'Password',
            'Mactive' => 'Mactive',
            'Cactive' => 'Cactive',
            'Dactive' => 'Dactive',
            'Comision' => 'Comision',
            'CommisionReport' => 'Commision Report',
            'mgtmreport' => 'Mgtmreport',
            'reportereloj' => 'Reportereloj',
            'tel' => 'Tel',
            'fax' => 'Fax',
            'website' => 'Website',
            'tipopago' => 'Tipopago',
            'reportdealer' => 'Reportdealer',
            'id_owner' => 'Id Owner',
        ];
    }
}
