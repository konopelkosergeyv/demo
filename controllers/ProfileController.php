<?php

namespace api\v2\controllers;

use api\v2\entities\Profile;
use api\v2\models\AvatarForm;
use api\v2\models\LocationForm;
use api\v2\models\ProfileForm;
use app\entities\SocialProfile;
use app\entities\User;
use app\modules\api\filters\HttpBearerAuth;
use app\modules\api\filters\HttpOAuth;
use app\modules\api\oauth\OAuthFactory;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\helpers\FileHelper;
use yii\web\BadRequestHttpException;
use yii\web\UnauthorizedHttpException;
use yii\web\UnprocessableEntityHttpException;
use yii\web\UploadedFile;

/**
 * Class ProfileController
 * @package api\v2\controllers
 */
class ProfileController extends BaseController
{

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                HttpBasicAuth::class,
                HttpBearerAuth::class,
                HttpOAuth::class,
            ],
        ];

        return $behaviors;
    }

    /**
     * @return array
     */
    public function verbs()
    {
        $verbs = parent::verbs();
        $verbs['avatar'] = ['POST', 'DELETE'];
        $verbs['change-country'] = ['POST'];
        $verbs['socials'] = ['POST'];
        return $verbs;
    }

    /**
     * Профиль пользователя
     *
     * @SWG\Get(
     *     path="/profile",
     *     tags={"Профиль пользователя"},
     *     summary="Профиль пользователя",
     *     @SWG\Response(
     *         response = 200,
     *         description = "Профиль пользователя",
     *         @SWG\Schema(ref = "#/definitions/Profile" ),
     *     ),
     *     )
     * @return User
     */
    public function actionIndex()
    {
        $user = Profile::findOne((int)Yii::$app->user->id);

        return $user;
    }

    /**
     * Изменение профиля
     *
     * @SWG\Post(
     *     path="/profile/update",
     *     tags={"Профиль пользователя"},
     *     summary="Изменение профиля пользователя",
     *     @SWG\Parameter(
     *      name="name",
     *      in="formData",
     *      type="string",
     *      description="Отображаемое имя",
     *      @SWG\Schema(type="string")
     *     ),
     *     @SWG\Parameter(
     *      name="email",
     *      in="formData",
     *      type="string",
     *      description="Email",
     *      @SWG\Schema(type="string")
     *     ),
     *      @SWG\Parameter(
     *      name="birthday",
     *      in="formData",
     *      type="string",
     *      description="День рождения",
     *      @SWG\Schema(type="string", format="date")
     *     ),
     *     @SWG\Parameter(
     *      name="phone",
     *      in="formData",
     *      type="string",
     *      description="Номер телефона",
     *      @SWG\Schema(type="string")
     *     ),
     *      @SWG\Parameter(
     *      name="currentPassword",
     *      in="formData",
     *      type="string",
     *      description="Текущий пароль",
     *      @SWG\Schema(type="string", format="password")
     *     ),
     *      @SWG\Parameter(
     *      name="password",
     *      in="formData",
     *      type="string",
     *      description="Новый пароль",
     *      @SWG\Schema(type="string", format="password")
     *     ),
     *     @SWG\Parameter(
     *      name="repeatPassword",
     *      in="formData",
     *      type="string",
     *      description="Повтор нового пароля",
     *      @SWG\Schema(type="string", format="password")
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "Обновленный профиль пользователя",
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
     *              "field": "name",
     *              "message": "Вы не заполнили поле!"
     *              },
     *          }
     *     }
     *     ),
     * )
     * @return Profile|ProfileForm
     * @throws BadRequestHttpException
     */
    public function actionUpdate()
    {
        $user = Profile::findOne(Yii::$app->user->id);
        $form = new ProfileForm($user);

        if (!$form->load(Yii::$app->request->getBodyParams())) {
            throw new BadRequestHttpException();
        }

        if (!$form->validate()) {
            return $form;
        }
        $form->save();
        return $form->getUser();
    }

    /**
     * Загрузка аватара
     *
     * @SWG\Post(
     *     path="/profile/avatar",
     *     tags={"Профиль пользователя"},
     *     summary="Загрузка аватара",
     *     @SWG\Parameter(
     *      name="avatar",
     *      in="formData",
     *      type="file",
     *      required=true,
     *      description="Файл аватара",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "Обновленный профиль пользователя",
     *         @SWG\Schema(ref = "#/definitions/Profile" ),

     *     ),
     *     @SWG\Response(
     *         response = 422,
     *         description = "Не прошло валидацию",
     *         examples = {
     *          "application/json": {
     *              {
     *              "field": "name",
     *              "message": "Вы не заполнили поле!"
     *              },
     *          }
     *     }
     *     ),
     * )
     *
     *  @SWG\Delete(
     *     path="/profile/avatar",
     *     tags={"Профиль пользователя"},
     *     summary="Удаление загруженого аватара",
     *
     *     @SWG\Response(
     *         response = 200,
     *         description = "Обновленный профиль пользователя",
     *         @SWG\Schema(ref = "#/definitions/Profile" ),
     *
     *     ),
     *
     * )
     * @return AvatarForm|array|null|Profile
     */
    public function actionAvatar()
    {
        $user = Profile::findOne(Yii::$app->user->id);
        if (Yii::$app->request->isDelete){
            if ($user->avatarUrl){
                FileHelper::unlink(Yii::getAlias("@webroot"). $user->avatarUrl);
                $user->avatarUrl = null;
                $user->saveOrFail();
            }
            return $user;
        }

        $form = new AvatarForm($user);
        $form->avatar = UploadedFile::getInstance($form, 'avatar');

        if (!$form->validate()) {
            return $form;
        }

        $form->save();

        return $form->getUser();
    }


    /**
     * Изменение страны по умолчанию
     *
     * @SWG\Post(
     *     path="/profile/change-country",
     *     tags={"Профиль пользователя"},
     *     summary="Изменение страны по умолчанию",
     *     @SWG\Parameter(
     *      name="country",
     *      in="formData",
     *      type="string",
     *      required=true,
     *      description="Код страны формате ISO 3166-1 Alpha-2",
     *      @SWG\Schema(type="string")
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "Обновленный профиль пользователя",
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
     *              "field": "name",
     *              "message": "Вы не заполнили поле!"
     *              },
     *          }
     *     }
     *     ),
     * )
     * @return Profile|LocationForm
     * @throws BadRequestHttpException
     */
    public function actionChangeCountry()
    {
        $user = Profile::findOne((int)Yii::$app->user->id);
        $form = new LocationForm($user);

        if (!$form->load(Yii::$app->request->post())) {
            throw new BadRequestHttpException();
        }

        if (!$form->validate()) {
            return $form;
        }
        $form->save();

        return $form->getUser();
    }


    /**
     * Привязка профиля соцсети
     *
     * @SWG\Post(
     *     path="/profile/socials",
     *     tags={"Профиль пользователя"},
     *     summary="Привязка профиля соцсети",
     *     @SWG\Parameter(
     *      name="socialNetwork",
     *      in="formData",
     *      type="string",
     *      required=true,
     *      description="",
     *     ),
     *      @SWG\Parameter(
     *      name="accessToken",
     *      in="formData",
     *      type="string",
     *      required=true,
     *      description="",
     *     ),
     *      @SWG\Parameter(
     *      name="socialId",
     *      in="formData",
     *      type="string",
     *      required=true,
     *      description="",
     *     ),
     *     @SWG\Parameter(
     *      name="email",
     *      in="formData",
     *      type="string",
     *      required=true,
     *      description="",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "Профиль успешно привязан",
     *     ),
     *      @SWG\Response(
     *         response = 400,
     *         description = "Не передан один из параметров",
     *     ),
     *     @SWG\Response(
     *         response = 422,
     *         description = "Профиль уже привязан к какому-то аккаунту",
     *     ),
     * )
     * @throws UnauthorizedHttpException
     * @throws UnprocessableEntityHttpException
     */
    public function actionSocials()
    {
        $socialNetwork = Yii::$app->request->post('socialNetwork');
        $accessToken = Yii::$app->request->post('accessToken');
        $socialUserId = Yii::$app->request->post('socialId');
        $email = Yii::$app->request->post('email');

        if (!$socialNetwork || !$accessToken || !$socialUserId || !$email) {
            throw new BadRequestHttpException();
        }

        if (SocialProfile::find()->andWhere(['socialId' => $socialUserId])->exists()) {
            throw new UnprocessableEntityHttpException('Такой профиль уже привязан');
        }

        $client = OAuthFactory::createClient($socialNetwork);

        if (!$client->hasValidToken($accessToken, $socialUserId)) {
            throw new UnauthorizedHttpException();
        }

        $data = $client->getUserInfo($accessToken, $socialUserId);
        $data['userId'] = Yii::$app->user->id;
        $data['email'] = $email;
        $this->saveProfile($data);

        return Yii::$app->response->setStatusCode('200');
    }

    /**
     * @param $data
     */
    protected function saveProfile($data)
    {
        $profile = new SocialProfile();
        $profile->setAttributes($data);
        $this->ensureSave($profile);
    }
}
