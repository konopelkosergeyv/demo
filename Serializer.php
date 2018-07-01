<?php
/**
 * @author Sergey Konopelko <konopelkosergeyv@gmail.com>
 * @copyright Copyright (c) 2018.
 */

namespace api\v2;

use yii\base\Arrayable;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class Serializer
 * @package api\v2
 */
class Serializer extends \yii\rest\Serializer
{
    /**
     * @var string
     */
    public $collectionEnvelope = 'items';

    /**
     * @var string
     */
    public $scenario;


    /**
     * Serializes a model object.
     * @param Arrayable $model
     * @return array the array representation of the model
     */
    protected function serializeModel($model)
    {
        if ($this->scenario && ($model instanceof Model)){
            $model->scenario = $this->scenario;
        }
        if ($this->request->getIsHead()) {
            return null;
        }

        list($fields, $expand) = $this->getRequestedFields();
        return $model->toArray($fields, $expand);
    }

    /**
     * Serializes a set of models.
     * @param array $models
     * @return array the array representation of the models
     */
    protected function serializeModels(array $models)
    {
        list($fields, $expand) = $this->getRequestedFields();
        foreach ($models as $i => $model) {
            if ($this->scenario && ($model instanceof Model)){
                $model->scenario = $this->scenario;
            }
            if ($model instanceof Arrayable) {
                $models[$i] = $model->toArray($fields, $expand);
            } elseif (is_array($model)) {
                $models[$i] = ArrayHelper::toArray($model);
            }
        }

        return $models;
    }
}