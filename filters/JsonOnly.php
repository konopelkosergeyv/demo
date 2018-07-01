<?php
/**
 * @author Sergey Konopelko <konopelkosergeyv@gmail.com>
 * @copyright Copyright (c) 2018.
 */

namespace api\v2\filters;

use yii\base\ActionFilter;
use Yii;
use yii\web\BadRequestHttpException;

/**
 * Class JsonOnly
 * @package api\v2\filters
 */
class JsonOnly extends ActionFilter
{

    /**
     * @param \yii\base\Action $action
     * @throws BadRequestHttpException
     * @return boolean
     */
    public function beforeAction($action)
    {
        Yii::$app->request->parsers['application/json'] = 'yii\web\JsonParser';

        if (Yii::$app->request->contentType != "application/json") {
            throw new BadRequestHttpException("Available only \"application/json\" format.");
        }

        return true;
    }
}