<?php
/**
 * @author Sergey Konopelko <konopelkosergeyv@gmail.com>
 * @copyright Copyright (c) 2018.
 */

namespace api\v2\entities;
use yii\helpers\ArrayHelper;


/**
 * Class Ticket
 * @package api\v2\entities
 *
 * @SWG\Definition(
 *     definition="Ticket",
 *     type="object",
 *     description="Тикет пользователя",
 *
 * @SWG\Property(property="id", type="integer", description="Id"),
 * @SWG\Property(property="userId", type="integer", description="Id пользователя"),
 * @SWG\Property(property="name", type="string", description="Тема"),
 * @SWG\Property(property="status", type="string", enum={"NEW","PROCESSING","COMPLETED","IN_WORK"}, description="Статус"),
 * @SWG\Property(property="messagesCount", type="integer", description="Количество сообщений"),
 * @SWG\Property(property="newMessages", type="integer", description="Количество новых сообщений"),
 * @SWG\Property(property="timeCreated", type="string", format="date-time", description="Дата создания"),
 * @SWG\Property(property="timeUpdated", type="string", format="date-time", description="Дата обновления"),
 * ),
 *
 *
 * @SWG\Definition(
 *   definition="ExtendedTicket",
 *   allOf={
 *     @SWG\Schema(
 *       ref="#definitions/Ticket"
 *     ),
 *     @SWG\Schema(
 *       type="object",
 *       @SWG\Property(
 *          property="messages",
 *          type="array",
 *          @SWG\Items(ref = "#/definitions/TicketMessage")
 *      ),
 *     )
 *   }
 * )

 */
class Ticket extends \app\entities\Ticket
{
    /**
     * При просморте одного тикета
     */
    const SCENARIO_VIEW = 'view';

    /**
     * @return array
     */
    public function extraFields()
    {
        return [];
    }

    /**
     * @return array
     */
    public function fields()
    {
        return array_merge($this->commonFields(), $this->getScenarioFields());
    }

    /**
     * @return array
     */
    public function commonFields()
    {
        return [
            'id',
            'userId',
            'name',
            'status',
            'messagesCount',
            'newMessages',
            'timeCreated',
            'timeUpdated',
        ];
    }

    /**
     * @return array
     */
    public function getScenarioFields()
    {
        return ($this->getScenario() === self::SCENARIO_VIEW) ?
            [
                'messages',
            ] :
            [];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMessages()
    {
        return $this->hasMany(TicketMessage::class, ['ticketId' => 'id'])->orderBy(['timeCreated' => SORT_DESC]);
    }


    /**
     * @return mixed
     */
    public function getTimeUpdated()
    {
        $dates = ArrayHelper::getColumn($this->messages, 'timeCreated');
        rsort($dates);
        return ArrayHelper::getValue($dates, '0');
    }

    /**
     * @return int
     */
    public function getMessagesCount()
    {
        return count($this->messages);
    }

    /**
     * @return int
     */
    public function getNewMessages()
    {
        $newMessages = 0;
        foreach ($this->messages as $message){
            if ($message->type == Ticket::TYPE_AGENT && $message->timeCreated > $this->timeViewed){
                $newMessages++;
            }
        }
        return $newMessages;
    }
}
