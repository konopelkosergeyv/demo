<?php
/**
 * @author Sergey Konopelko <konopelkosergeyv@gmail.com>
 * @copyright Copyright (c) 2018.
 */

namespace api\v2\entities;

use app\entities\User;
use app\helpers\Url;

/**
 * Class TicketAgent
 * @package api\v2\entities
 *
 * @SWG\Definition(definition="Agent", type="object", description="Сотрудник техподдержки")
 *
 * @SWG\Property(property="id", type="integer")
 * @SWG\Property(property="name", type="string", description="Отображаемое имя")
 * @SWG\Property(property="avatarUrl", type="string", description="Ссылка на аватар")
 *
 */
class TicketAgent extends User
{
    /**
     * @return array
     */
    public function fields()
    {
        return [
            'id',
            'name',
            'avatarUrl' => function () {
                return $this->getAvatarUrl();
            },
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
     * @return null|string
     */
    protected function getAvatarUrl()
    {
        if ($this->avatarUrl){
            return Url::to('@web'.$this->avatarUrl, true);
        }

        foreach ($this->socialProfiles as $userSocialProfile) {
            if ($userSocialProfile->userAvatarUrl) {
                return $userSocialProfile->userAvatarUrl;
            }
        }
        return null;
    }

}
