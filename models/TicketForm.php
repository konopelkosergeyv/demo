<?php
namespace api\v2\models;

use app\entities\Ticket;
use app\entities\TicketMessage;
use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

/**
 * Class TicketForm
 * @package api\v2\models
 */
class TicketForm extends Model
{
    /**
     * @var string
     */
    public $message;

    /**
     * @var string
     */
    public $name;

    /**
     * @var UploadedFile
     */
    public $image;

    /**
     * @var string
     */
    public $image_path;


    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['message', 'string', 'max' => 2000, 'tooLong' => Yii::t('app', 'Максимум {characters} символов', ['characters' => 2000])],
            ['message', 'trim'],
            ['message', 'required', 'message' => Yii::t('app', 'Вы не заполнили поле!')],

            ['name', 'string', 'max' => 250, 'tooLong' => Yii::t('app', 'Максимум {characters} символов', ['characters' => 250])],
            ['name', 'trim'],
            ['name', 'required', 'message' => Yii::t('app', 'Вы не заполнили поле!')],

            ['image', 'image', 'mimeTypes' => 'image/*', 'extensions' => ['png', 'jpg', 'jpeg', 'tif', 'tiff', 'bmp', 'gif'], 'skipOnError' => false],
            ['image_path', 'string'],

        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'message' => Yii::t('app', 'Сообщение'),
            'name' => Yii::t('app', 'Тема сообщение'),
        ];
    }

    /**
     * @return boolean | null
     */
    public function save()
    {
        $data = $this->getAttributes();
        unset($data['message']);
        $ticketMessage = new Ticket($data);

        if(!$ticketMessage->save()){
            return null;
        }

        return $ticketMessage;
    }

    /**
     * @return string
     */
    public function formName()
    {
        return '';
    }

    /**
     * @param array $data
     * @param null $formName
     * @return bool
     */
    public function load($data, $formName = null)
    {
        $this->image = UploadedFile::getInstanceByName('image');
        return parent::load($data, $formName);
    }

    /**
     *
     */
    public function saveImage()
    {
        if (!$this->image instanceof UploadedFile){
            $this->image = UploadedFile::getInstance($this, 'image');
        }

        if ($this->image) {
            $imagePath = '/images/tickets/' . uniqid() . '.' . $this->image->extension;
            $this->image->saveAs(Yii::getAlias('@webroot') . $imagePath);
            $this->image_path = $imagePath;
        }

    }
}
