<?php
namespace frontend\models;

use yii\base\Model;
use frontend\models\User;
use common\models\Agency;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $name;
    public $apellido;
    public $email;

    public $user;
    public $pass;
    public $repass;

    public $agname;
    public $agemail;
    public $agadress;
    public $agcity;
    public $agstate;
    public $agzip;

    public $agphone;
    public $agfax;
    public $agwebsite;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'apellido'], 'required'],
            [['name', 'apellido'], 'string', 'max' => 50],

            [['agname', 'agemail', 'agadress', 'agcity', 'agstate', 'agzip', 'agphone', 'agfax', 'agwebsite'], 'safe'],

            ['user', 'trim'],
            ['user', 'required'],
            ['user', 'unique', 'targetClass' => '\frontend\models\User', 'message' => 'This user has already been taken.'],
            ['user', 'string', 'min' => 2, 'max' => 255],

            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\frontend\models\User', 'message' => 'This email address has already been taken.'],

            ['pass', 'required'],
            ['pass', 'string', 'min' => 6],

            ['repass', 'required'],
            ['repass', 'compare', 'compareAttribute'=>'pass', 'message'=>"Passwords don't match" ],
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }
        $agency = new Agency;

        $agency->nombre = $this->agname;
        $agency->email = $this->agemail;
        $agency->direccion = $this->agadress;
        $agency->ciudad = $this->agcity;
        $agency->estado = $this->agstate;
        $agency->zip = $this->agzip;

        $agency->tel = $this->agphone;
        $agency->fax = $this->agfax;
        $agency->website = $this->agwebsite;
        
        $agency->su = $this->user;
        $agency->password = $this->pass;

        $agency->tipopago = 0;



        if ( $agency->save(false) ) {
            $user = new User();
            $user->user = $this->user;
            $user->email = $this->email;
            $user->pass = $this->pass;

            $user->name = $this->name;
            $user->apellido = $this->apellido;
            $user->fecAlta = date('Y-m-d');
            $user->agent_number = '';
            $user->agencia = $agency->id;
            $user->agcode = 0;
            $user->habilitado = 0;
            $user->isReseller = 1;
            
            if ( $user->save() ) {
                $agency->id_owner = $user->id;
                $agency->save(false);
                return $user;
            } else {
                $agency->delete();
                return null;
            }
        }
        return null;   
    }
}
