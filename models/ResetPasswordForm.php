<?php
namespace api\v2\models;

use api\v2\entities\Profile;
use app\entities\UserToken;
use yii\base\InvalidArgumentException;
use Yii;
use yii\base\Model;

/**
 * Class ResetPasswordForm
 * @package api\v2\models
 */
class ResetPasswordForm extends Model
{
    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $repeatPassword;

    /**
     * @var Profile
     */
    private $user;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['password','repeatPassword'], 'required'],

            [['password','repeatPassword'], 'string'],
            [['password','repeatPassword'], 'trim'],
            [['password','repeatPassword'], 'string', 'min' => 6, 'tooShort' => Yii::t('app','Пароль должен содержать минимум 6 символов')],
            ['repeatPassword', 'compare', 'compareAttribute' => 'password', 'message' => Yii::t('app', 'Пароли не совпадают')]

        ];
    }

    /**
     * Creates a form model given a token.
     *
     * @param  string $token
     * @param  array  $config
     *
     * @throws InvalidArgumentException
     */
    public function __construct($token, $config = [])
    {
        if (empty($token) || !is_string($token)) {
            throw new InvalidArgumentException(Yii::t('app', 'Неверная ссылка для восстановления пароля.'));
        }

        /* @var UserToken $user */
        $token = UserToken::findOne(['value' => $token]);

        if (is_null($token)) {
            throw new InvalidArgumentException(Yii::t('app', 'Ссылка на восстановление пароля недействительна.'));
        }

        $user = Profile::findOne((int)$token->userId);

        if (!$user) {
            throw new InvalidArgumentException(Yii::t('app', 'Неверная ссылка для восстановления пароля.'));
        }

        if (UserToken::isTokenExpired($token)) {
            throw new InvalidArgumentException(Yii::t('app', 'Эта ссылка для восстановления пароля больше недействительна.'));
        }

        $this->user = $user;

        parent::__construct($config);
    }

    /**
     * Resets password.
     */
    public function save()
    {
        /* @var $user \app\entities\User */
        $user = $this->getUser();
        $user->setPassword($this->password);
        $user->setEmailConfirmed();
        $user->saveOrFail();
    }

    /**
     * @return Profile|UserToken
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function formName()
    {
        return '';
    }
}