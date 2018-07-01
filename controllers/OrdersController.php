<?php
namespace api\v2\controllers;

use api\v2\entities\Order;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * Class OrdersController
 * @package api\v2\controllers
 */
class OrdersController extends BaseController
{

    /**
     * Список заказов пользователя
     *
     * @SWG\Get(
     *     path="/orders",
     *     tags={"Заказы"},
     *     summary="Получение списка заказов",
     *     @SWG\Response(
     *         response = 200,
     *         description = "Список заказов",
     *         @SWG\Schema(type="array", @SWG\Items(ref = "#/definitions/Order")),
     *     ),
     * )
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $query = Order::find()->forUser(Yii::$app->user->id);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['timeCreated' => SORT_DESC],
            ],
        ]);

        return $dataProvider;
    }

}
