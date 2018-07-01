<?php

namespace api\v2\controllers;

use api\v2\entities\Profile;
use api\v2\models\ResetPasswordForm;
use app\entities\UserToken;
use app\jobs\mailer\SendPasswordFromOauth;
use app\jobs\mailer\SendPasswordResetToken;
use api\v2\models\PasswordResetRequestForm;
use api\v2\models\SignUpForm;
use app\repositories\UserRepository;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;

/**
 * Class UsersController
 * @package api\v2\controllers
 */
class UsersController extends BaseController
{
    /**
     * @var UserRepository
     */
    public $users;

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        unset($behaviors['authenticator']);
        return $behaviors;
    }

    /**
     * @return array
     */
    public function verbs()
    {
        $verbs = parent::verbs();
        $verbs['reset-password'] = ['POST'];
        $verbs['reset-password-request'] = ['POST'];
        return $verbs;
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->users = new UserRepository();
    }

    /**
     * Регистрация пользователя
     *
     * @SWG\Post(
     *     path="/users/create",
     *     tags={"Пользователи"},
     *     summary="Регистрация пользователя",
     *     @SWG\Parameter(
     *      name="email",
     *      in="formData",
     *      type="string",
     *      required=true,
     *      description="Email",
     *     ),
     *     @SWG\Parameter(
     *      name="password",
     *      in="formData",
     *      type="string",
     *      required=true,
     *      description="Пароль",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "Профиль пользователя",
     *         @SWG\Schema(ref = "#/definitions/Profile" ),
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "Не переданы данные",
     *     ),
     *     @SWG\Response(
     *         response = 422,
     *         description = "Не прошло валидацию",
     *         examples = {
     *          "application/json": {
     *              {
     *              "field": "email",
     *              "message": "Неверный email"
     *              },
     *           }
     *         }
     *      ),
     * )
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionCreate()
    {
        $model = new SignUpForm();

        if (!$model->load(Yii::$app->request->post())) {
            throw new BadRequestHttpException();
        }

        if (!$model->validate()) {
            return $model;
        }

        $user = $this->users->create($model->attributes, $model->password);

        Yii::$app->jobs->dispatch(SendPasswordFromOauth::class, ['user' => $user, 'password' => $model->password]);

        $user->createToken('MONTH', UserToken::TYPE_API);

        $profile = Profile::findOne((int)$user->id);

        return $profile;
    }

    /**
     * Восстановление пароля пользователя
     *
     * @SWG\Post(
     *     path="/users/reset-password",
     *     tags={"Пользователи"},
     *     summary="Восстановление пароля пользователя",
     *     @SWG\Parameter(
     *      name="token",
     *      in="query",
     *      type="string",
     *      required=true,
     *      description="Token для сброса",
     *     ),
     *     @SWG\Parameter(
     *      name="password",
     *      in="formData",
     *      type="string",
     *      required=true,
     *      description="Новый пароль",
     *     ),
     *     @SWG\Parameter(
     *      name="repeatPassword",
     *      in="formData",
     *      type="string",
     *      required=true,
     *      description="Повтор нового пароля",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "Результат запроса сброса прароля",
     *         examples = {
     *          "application/json": {
     *              "success": 1,
     *              "message": "Пароль успешно изменен"
     *           },
     *         }
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "Не переданы данные или неверный токен",
     *     ),
     *     @SWG\Response(
     *         response = 422,
     *         description = "Не прошло валидацию",
     *         examples = {
     *          "application/json": {
     *              {
     *              "field": "password",
     *              "message": "Неверный пароль"
     *              },
     *           }
     *         }
     *      ),
     * )
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     * @return PasswordResetRequestForm|array
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if (!$model->load(Yii::$app->request->post())) {
            throw new BadRequestHttpException();
        }

        if (!$model->validate()) {
            return $model;
        }

        $model->save();

        return $this->successMessage(Yii::t('app', 'Пароль успешно изменен.'));
    }

    /**
     * Запрос на восстановление пароля пользователя
     *
     * @SWG\Post(
     *     path="/users/reset-password-request",
     *     tags={"Пользователи"},
     *     summary="Запрос на восстановление пароля пользователя",
     *     @SWG\Parameter(
     *      name="email",
     *      in="formData",
     *      type="string",
     *      required=true,
     *      description="Email",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "Результат запроса сброса прароля",
     *         examples = {
     *          "application/json": {
     *              "success": 1,
     *              "message": "Инструкция по восстановлению пароля выслана на ваш e-mail"
     *           },
     *         }
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "Не переданы данные",
     *     ),
     *    @SWG\Response(
     *         response = 404,
     *         description = "Не найден пользователь с таким email",
     *     ),
     *     @SWG\Response(
     *         response = 422,
     *         description = "Не прошло валидацию",
     *         examples = {
     *          "application/json": {
     *              {
     *              "field": "email",
     *              "message": "Неверный email"
     *              },
     *           }
     *         }
     *      ),
     * )
     * @return mixed
     * @throws BadRequestHttpException
     * @return PasswordResetRequestForm|array
     */
    public function actionResetPasswordRequest()
    {
        $model = new PasswordResetRequestForm();

        if (!$model->load(Yii::$app->request->post())) {
            throw new BadRequestHttpException();
        }

        if (!$model->validate()) {
            return $model;
        }

        $user = $this->users->getByEmail($model->email);

        $this->ensureExists($user);

        Yii::$app->jobs->dispatch(SendPasswordResetToken::class, compact('user'));

        return $this->successMessage(Yii::t('app', 'Инструкция по восстановлению пароля выслана на ваш e-mail'));
    }

}
