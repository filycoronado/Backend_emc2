<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "vehicles".
 *
 * @property int $id
 * @property string $id_client
 * @property string $vin
 * @property string $year
 * @property string $make
 * @property string $model
 * @property string $glass
 * @property string $note
 * @property string $placas
 * @property int $id_agencia
 * @property string $imagen1
 * @property string $imagen2
 * @property string $imagen3
 * @property string $imagen4
 * @property double $premium
 */
class Vehicles extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vehicles';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['vin'], 'required'],
            [['year'], 'safe'],
            [['note', 'imagen1', 'imagen2', 'imagen3', 'imagen4'], 'string'],
            [['id_agencia'], 'integer'],
            [['premium'], 'number'],
            [['vin', 'make', 'model', 'glass'], 'string', 'max' => 50],
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
            'id_client' => 'Id Client',
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
        ];
    }
}
