<?php

namespace api\v2\controllers;

use api\v2\entities\Country;
use yii\data\ActiveDataProvider;


/**
 * Class CountryController
 *
 * @package api\v2\controllers
 */
class CountriesController extends BaseController
{

    /**
     * Получение списка доступных стран
     *
     * @SWG\Get(
     *     path="/countries",
     *     tags={"Страны"},
     *     summary="Получение списка стран",
     *     @SWG\Response(
     *         response = 200,
     *         description = "Список стран",
     *         @SWG\Schema(type="array", @SWG\Items(ref = "#/definitions/Country" )),
     *     ),
     * )
     *
     * @return ActiveDataProvider
     *
     */
    public function actionIndex()
    {
        $query = Country::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;
    }

}
