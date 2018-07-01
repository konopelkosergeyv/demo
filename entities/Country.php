<?php
/**
 * @author Sergey Konopelko <konopelkosergeyv@gmail.com>
 * @copyright Copyright (c) 2018.
 */

namespace api\v2\entities;

/**
 * Class Country
 * @package api\v2\models
 *
 * @SWG\Definition(definition="Country", type="object", description="Страна")
 *
 * @SWG\Property(property="id", type="integer", description="Id Страны")
 * @SWG\Property(property="code", type="string", description="Двухсимвольный код страны")
 * @SWG\Property(property="title", type="string", description="Название страны")
 */
class Country extends \app\entities\Country
{
    /**
     * @return array
     */
    public function fields()
    {
        return [
            'id',
            'code',
            'name' => 'title',
        ];
    }

    /**
     * @return array
     */
    public function extraFields()
    {
        return [];
    }
}
