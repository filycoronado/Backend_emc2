<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "companyclaims".
 *
 * @property int $id
 * @property string $nombre
 * @property string $email
 * @property string $username
 * @property string $password
 * @property int $agency
 * @property int $nivel
 * @property int $id_ciudad
 * @property int $sort_order
 */
class Companyclaims extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'companyclaims';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre', 'email', 'username', 'password', 'agency'], 'required'],
            [['nombre', 'email', 'username', 'password'], 'string'],
            [['agency', 'nivel', 'id_ciudad', 'sort_order'], 'integer'],
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
            'email' => 'Email',
            'username' => 'Username',
            'password' => 'Password',
            'agency' => 'Agency',
            'nivel' => 'Nivel',
            'id_ciudad' => 'Id Ciudad',
            'sort_order' => 'Sort Order',
        ];
    }
}
