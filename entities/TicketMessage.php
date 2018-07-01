<?php
/**
 * @author Sergey Konopelko <konopelkosergeyv@gmail.com>
 * @copyright Copyright (c) 2018.
 */

namespace api\v2\entities;

use app\helpers\Url;

/**
 * Class TicketMessage
 * @package api\v2\entities
 *
 * @SWG\Definition(definition="TicketMessage", type="object", description="Сообщение тикета")
 *
 * @SWG\Property(property="id", type="integer", description="Id")
 * @SWG\Property(property="type", type="string", enum={"CLIENT","AGENT"}, description="Тип сообщения (Саппорт/Пользователь)")
 * @SWG\Property(property="message", type="string", description="Текст сообщения")
 * @SWG\Property(property="agent", type="object", description="Данные сотрудника техподдержки если сообщение саппорта",ref = "#/definitions/Agent")
 * @SWG\Property(property="image", type="string", description="Прикрепленное изображение")
 * @SWG\Property(property="timeCreated", type="string", format="date-time", description="Дата добавления")
 */
class TicketMessage extends \app\entities\TicketMessage
{
    /**
     * @return array
     */
    public function fields()
    {
        return [
            'id',
            'type',
            'message',
            'agent',
            'image',
            'timeCreated',
        ];
    }

    /**
     * @return array
     */
    public function extraFields()
    {
        return [];
    }

    /**
     * @return ActiveQuery|TicketAgent
     */
    public function getAgent()
    {
        return $this->hasOne(TicketAgent::class,['id' => 'agentId']);
    }

    /**
     * @return null|string
     */
    public function getImage()
    {
        return $this->image_path ? Url::to($this->image_path, true) : null;
    }
}
