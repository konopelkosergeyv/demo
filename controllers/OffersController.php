<?php

namespace api\v2\controllers;

use app\entities\Favorite;
use api\v2\entities\Marketplace;
use app\entities\UserAction;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

/**
 * Class OffersController
 * @package api\v2\controllers
 */
class OffersController extends BaseController
{
    /**
     * @return array
     */
    public function verbs()
    {
        $verbs = parent::verbs();
        $verbs['favorite'] = ['GET'];
        $verbs['toggle-favorite'] = ['GET'];
        $verbs['visited'] = ['GET'];
        $verbs['visit'] = ['GET'];
        return $verbs;
    }

    /**
     * Список офферов
     *
     * @SWG\Get(
     *     path="/offers",
     *     tags={"Офферы"},
     *     summary="Получение списка офферов",
     *     @SWG\Response(
     *         response = 200,
     *         description = "Список офферов",
     *         @SWG\Schema(type="array", @SWG\Items(ref = "#/definitions/Offer") ),
     *     ),
     * )
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $query = Marketplace::find()
            ->withFavorites()
            ->byUserRegion()
            ->visibleToApi()
            ->popular();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);

        return $dataProvider;
    }


    /**
     * Список избранных офферов
     *
     * @SWG\Get(
     *     path="/offers/favorite",
     *     tags={"Офферы"},
     *     summary="Получение списка избранных офферов",
     *     @SWG\Response(
     *         response = 200,
     *         description = "Список избранных офферов",
     *         @SWG\Schema(type="array", @SWG\Items(ref = "#/definitions/Offer") ),
     *     ),
     * )
     * @return ActiveDataProvider
     */
    public function actionFavorite()
    {
        $query = Marketplace::find()
            ->byUserRegion()
            ->favorites()
            ->visibleToApi()
            ->popular();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;
    }

    /**
     * Добавление/удаление из избранного
     *
     * @SWG\Get(
     *     path="/offers/toggle-favorite/{id}",
     *     tags={"Офферы"},
     *     summary="Добавление/удаление из избранного",
     *     @SWG\Parameter(
     *      name="id",
     *      in="path",
     *      type="integer",
     *      required=true,
     *      description="Id оффера",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "Статус магазина в избранном",
     *         examples = {
     *          "application/json": {
     *              "inFavorites": 1,
     *           },
     *         }
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "Не передан id",
     *     ),
     *      @SWG\Response(
     *         response = 404,
     *         description = "Оффер не найден",
     *     ),
     * )
     * @param integer $id
     * @return array
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionToggleFavorite($id)
    {
        $marketplace = Marketplace::findOne($id);
        $this->ensureExists($marketplace);

        $model = Favorite::findOne(['marketplaceId' => $marketplace->id, 'userId' => Yii::$app->user->id]);

        if (!$model) {
            $model = new Favorite();
            $model->marketplaceId = $id;
            $model->userId = Yii::$app->user->id;
            $model->save();
            return ['inFavorites' => 1];
        } else {
            $model->delete();
            return ['inFavorites' => 0];
        }
    }

    /**
     * Список посещенных офферов
     *
     * @SWG\Get(
     *     path="/offers/visited",
     *     tags={"Офферы"},
     *     summary="Получение списка посещенных офферов",
     *     @SWG\Response(
     *         response = 200,
     *         description = "Список посещенных офферов",
     *         @SWG\Schema(type="array", @SWG\Items(ref = "#/definitions/Offer") ),
     *     ),
     * )
     * @return ActiveDataProvider
     */
    public function actionVisited()
    {
        $query = Marketplace::find()
            ->withFavorites()
            ->visitedByUser(Yii::$app->user->id)
            ->visibleToApi()
            ->addOrderBy(['user_action.timeCreated' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;
    }

    /**
     * Отправка статуса посещения оффера
     *
     * @SWG\Get(
     *     path="/offers/visit/{id}",
     *     tags={"Офферы"},
     *     summary="Отправка статуса посещения оффера",
     *     @SWG\Parameter(
     *      name="id",
     *      in="path",
     *      type="integer",
     *      required=true,
     *      description="Id оффера",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "Статус сохранения посещения",
     *         examples = {
     *          "application/json": {
     *              "success": 1,
     *           },
     *         }
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "Не передан id",
     *     ),
     *      @SWG\Response(
     *         response = 404,
     *         description = "Оффер не найден",
     *     ),
     * )
     * @return array
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionVisit($id)
    {
        $marketplace = Marketplace::findOne($id);

        $this->ensureExists($marketplace);

        $action = new UserAction();
        $action->userId = Yii::$app->user->id;
        $action->marketplaceId = $marketplace->id;

        return ['success' => (int)$action->save()];
    }

    /**
     * Получение одного оффера
     *
     * @SWG\Get(
     *     path="/offers/{id}",
     *     tags={"Офферы"},
     *     summary="Получение одного оффера",
     *     @SWG\Parameter(
     *      name="id",
     *      in="path",
     *      type="integer",
     *      required=true,
     *      description="Id оффера",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "Полная инфо по офферу",
     *         @SWG\Schema(ref = "#/definitions/Offer",
     *       @SWG\Property(property="test", type="string", description="test") ),
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "Не передан id",
     *     ),
     *      @SWG\Response(
     *         response = 404,
     *         description = "Оффер не найден",
     *     ),
     * )
     *
     * @param integer $id
     * @return Marketplace
     */
    public function actionView($id)
    {
        $marketplace = Marketplace::find()
            ->byId($id)
            ->withFavorites()
            ->visibleToApi()
            ->one();

        $this->ensureExists($marketplace);
        $this->serializer['scenario'] = Marketplace::SCENARIO_SINGLE;

        return $marketplace;
    }

}
