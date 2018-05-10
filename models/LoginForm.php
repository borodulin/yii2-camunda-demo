<?php

namespace app\models;

use app\camunda\Camunda;
use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @throws \borodulin\camunda\Exception
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function validatePassword($attribute)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return bool whether the user is logged in successfully
     * @throws \borodulin\camunda\Exception
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        }

        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User
     * @throws \borodulin\camunda\Exception
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            /** @var Camunda $camunda */
            $camunda = Yii::$app->get('camunda');
            $bk = uniqid('bk_');
            Yii::$app->session->set('bk', $bk);
            $result = $camunda->start('demo2', $bk, [
                'username' => $this->username,
//                'password' => $this->password,
            ]);
            $this->_user = new User([
                'id' => $result,
                'username' =>  $this->username,
            ]);
        }

        return $this->_user;
    }
}
