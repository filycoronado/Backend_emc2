<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "clients_changelog".
 *
 * @property int $Id
 * @property int $id_client
 * @property int $id_user
 * @property string $description
 * @property double $total_before
 * @property double $total_after
 * @property string $date
 * @property int $status
 */
class ClientsChangelog extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'clients_changelog';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_client', 'id_user', 'description', 'total_before', 'total_after'], 'required'],
            [['id_client', 'id_user', 'status'], 'integer'],
            [['description'], 'string'],
            [['total_before', 'total_after'], 'number'],
            [['date'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'Id' => 'ID',
            'id_client' => 'Id Client',
            'id_user' => 'Id User',
            'description' => 'Description',
            'total_before' => 'Total Before',
            'total_after' => 'Total After',
            'date' => 'Date',
            'status' => 'Status',
        ];
    }
    
      public function getUser()
    {
        return $this->hasMany(Users::className(), ['id' => 'id_user']);
    }
}
