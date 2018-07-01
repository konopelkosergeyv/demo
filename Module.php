<?php

namespace api\v2;

use Yii;
use yii\base\BootstrapInterface;
use yii\base\Module as BaseModule;
use yii\web\GroupUrlRule;

/**
 * Class Module
 *
 * @package api\v2
 */
class Module extends BaseModule implements BootstrapInterface
{
    const VERSION = '2.0.0';

    public $controllerNamespace = 'api\v2\controllers';

    /**
     * @var string The prefix for user module URL.
     *
     * @See [[GroupUrlRule::prefix]]
     */
    public $urlPrefix = 'api/v2';

    /** @var array The rules to be used in URL management. */
    public $urlRules = [
        [
            'verb' => ['POST', 'PUT', 'PATCH'],
            'pattern' => '<controller:[\w\-]+>/update',
            'route' => '<controller>/update',
        ],
        [
            'verb' => ['DELETE'],
            'pattern' => '<controller:[\w\-]+>/delete/<id:\w+>',
            'route' => '<controller>/delete',
        ],
        [
            'verb' => ['GET', 'HEAD'],
            'pattern' => '<controller:[\w\-]+>/<id:\d+>',
            'route' => '<controller>/view',
        ],
        [
            'verb' => ['POST'],
            'pattern' => '<controller:[\w\-]+>/create',
            'route' => '<controller>/create',
        ],
        [
            'verb' => ['GET', 'HEAD'],
            'pattern' => '<controller:[\w\-]+>',
            'route' => '<controller>/index',
        ],
        [
            'pattern' => '<controller:[\w\-]+>/<action:[\w\-]+>',
            'route' => '<controller>/<action>',
        ],
        [
            'pattern' => '<controller:[\w\-]+>/<action:[\w\-]+>/<id:\d+>',
            'route' => '<controller>/<action>',
        ],
    ];

    public function bootstrap($app)
    {
        $app->urlManager->addRules([
            new GroupUrlRule([
                "prefix" => $this->urlPrefix,
                "rules" => $this->urlRules,
            ]),
        ], false);
    }
}