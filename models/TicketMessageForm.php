<?php

namespace api\v2\models;

use api\v2\entities\Ticket;
use api\v2\entities\TicketMessage;
use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

/**
 * Class TicketMessageForm
 * @package api\v2\models
 */
class TicketMessageForm extends Model
{
    /**
     * @var string
     */
    public $message;

    /**
     * @var string
     */
    public $ticketId;

    /**
     * @var string
     */
    public $type;

    /**
     * @var UploadedFile
     */
    public $image;

    /**
     * @var string
     */
    public $image_path;


    /**
     * Returns the validation rules for attributes.
     *
     * @return array
     */
    public function rules()
    {
        return [
            ['message', 'string', 'max' => 2000, 'tooLong' => Yii::t('app', 'Максимум {characters} символов', ['characters' => 2000])],
            ['message', 'trim'],
            ['message', 'required', 'message' => Yii::t('app', 'Вы не заполнили поле!')],

            ['ticketId', 'safe'],

            ['type', 'default', 'value' => Ticket::TYPE_CLIENT],

            ['image', 'image', 'mimeTypes' => 'image/*', 'extensions' => ['png', 'jpg', 'jpeg', 'tif', 'tiff', 'bmp', 'gif'], 'skipOnError' => false],
            ['image_path', 'string'],
        ];
    }

    /**
     * Returns the attribute labels.
     *
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'message' => Yii::t('app', 'Сообщение'),
            'image' => Yii::t('app', 'Картинка'),
        ];
    }

    /**
     *
     * @return TicketMessage
     */
    public function save()
    {
        $this->saveImage();

        $message = new TicketMessage();
        $message->setAttributes($this->attributes);
        $message->save();

        return $message;
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
}
