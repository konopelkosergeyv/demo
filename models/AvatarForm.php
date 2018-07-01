<?php
/**
 * @author Sergey Konopelko <konopelkosergeyv@gmail.com>
 * @copyright Copyright (c) 2018.
 */

namespace api\v2\models;

use app\helpers\AssureTrait;
use yii\base\Model;
use yii\web\UploadedFile;
use Yii;

/**
 * Class AvatarForm
 * @package api\v2\models
 */
class AvatarForm extends Model
{
    use AssureTrait;

    /**
     * @var UploadedFile
     */
    public $avatar;

    /**
     * @var \app\entities\User
     */
    private $user;

    /**
     * AvatarForm constructor.
     * @param array $user
     * @param array $config
     */
    public function __construct($user, array $config = [])
    {
        $this->user = $user;
        parent::__construct($config);
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['avatar', 'required'],
            ['avatar', 'image','skipOnEmpty' => false,],
        ];
    }

    /**
     * @return bool
     */
    public function save()
    {
        if ($this->avatar) {
            $logoName = '/images/avatar/' . uniqid('a_') . '.' . $this->avatar->extension;
            $this->avatar->saveAs(Yii::getAlias('@webroot') . $logoName);
            $avatarUrl = Yii::getAlias('@web') . $logoName;

            $this->user->avatarUrl = $avatarUrl;
            $this->user->saveOrFail();
        }
    }

    /**
     * @return \app\entities\User|array
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function formName()
    {
        return '';
    }

}