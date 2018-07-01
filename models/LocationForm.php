<?php
namespace api\v2\models;

use api\v2\entities\Profile;
use app\entities\Country;
use Yii;
use yii\base\Model;

/**
 * Class LocationForm
 * @package api\v2\models
 */
class LocationForm extends Model
{
    /**
     * @var string
     */
    public $country;

    /**
     * @var Profile
     */
    protected $user;

    /**
     * @param Profile  $user
     * @param array $config
     */
    public function __construct(Profile $user, $config = [])
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
            ['country', 'required'],
            ['country', 'string', 'max' => 2],
            ['country', 'exist', 'targetClass' => Country::className(), 'targetAttribute' => 'code'],
        ];
    }

    /**
     *
     */
    public function save()
    {
        $this->user->country = $this->country;
        $this->user->saveOrFail();
    }

    /**
     * @return Profile
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
