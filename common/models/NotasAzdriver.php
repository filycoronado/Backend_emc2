<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "notas_azdriver".
 *
 * @property int $id
 * @property int $user_id
 * @property int $client_id
 * @property string $date
 * @property string $note
 */
class NotasAzdriver extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'notas_azdriver';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'client_id', 'date', 'note'], 'required'],
            [['user_id', 'client_id'], 'integer'],
            [['date'], 'safe'],
            [['note'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'client_id' => 'Client ID',
            'date' => 'Date',
            'note' => 'Note',
        ];
    }
}
