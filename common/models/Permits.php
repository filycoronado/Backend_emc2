<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "permits".
 *
 * @property int $id
 * @property int $id_user
 * @property int $agency
 * @property int $erase
 * @property int $editpolicy
 * @property int $ReportRetention
 * @property int $ReportEFT
 * @property int $ReportPolicy
 * @property int $DealerReport
 * @property int $external_login
 * @property int $shedule
 * @property int $Goals
 * @property int $TimeReport
 * @property int $EditTime
 * @property int $TakePayments
 * @property int $Atachment
 * @property int $CreateUsers
 * @property int $salesReport
 * @property int $permissions
 * @property int $asing
 */
class Permits extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'permits';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_user', 'agency', 'erase', 'editpolicy', 'ReportRetention', 'ReportEFT', 'ReportPolicy', 'DealerReport', 'external_login', 'shedule', 'Goals', 'TimeReport', 'EditTime', 'TakePayments', 'Atachment', 'CreateUsers', 'salesReport', 'permissions', 'asing'], 'required'],
            [['id_user', 'agency', 'erase', 'editpolicy', 'ReportRetention', 'ReportEFT', 'ReportPolicy', 'DealerReport', 'external_login', 'shedule', 'Goals', 'TimeReport', 'EditTime', 'TakePayments', 'Atachment', 'CreateUsers', 'salesReport', 'permissions', 'asing'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_user' => 'Id User',
            'agency' => 'Agency',
            'erase' => 'Erase',
            'editpolicy' => 'Editpolicy',
            'ReportRetention' => 'Report Retention',
            'ReportEFT' => 'Report E F T',
            'ReportPolicy' => 'Report Policy',
            'DealerReport' => 'Dealer Report',
            'external_login' => 'External Login',
            'shedule' => 'Shedule',
            'Goals' => 'Goals',
            'TimeReport' => 'Time Report',
            'EditTime' => 'Edit Time',
            'TakePayments' => 'Take Payments',
            'Atachment' => 'Atachment',
            'CreateUsers' => 'Create Users',
            'salesReport' => 'Sales Report',
            'permissions' => 'Permissions',
            'asing' => 'Asing',
        ];
    }
}
