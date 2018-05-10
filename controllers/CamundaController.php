<?php
/**
 * Created by PhpStorm.
 * User: borodulin
 * Date: 26.01.18
 * Time: 15:21
 */

namespace app\controllers;


use borodulin\camunda\dto\TaskQuery;
use borodulin\camunda\Message;
use borodulin\camunda\Task;
use app\models\TaskModel;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

class CamundaController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
            ],
        ];
    }

    /**
     * @return string
     * @throws \borodulin\camunda\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndex()
    {
        $this->getModels(true);

        $models = $this->getModels();
        if (empty($models)) {
            Yii::$app->user->logout();
            return $this->goHome();
        }
        return $this->render('index', ['models' => $models]);
    }

    /**
     * @param bool $update
     * @return array
     * @throws \borodulin\camunda\Exception
     * @throws \yii\base\InvalidConfigException
     */
    protected function getModels($update = false)
    {
        $tasks = (new Task())->getList(new TaskQuery([
            'processInstanceId' => Yii::$app->user->id,
        ]));
        $models = [];
        foreach ($tasks as $task) {
            $model = new TaskModel($task);
            $update && $model->loadAndSave();
            $models[] = $model;
        }
        return $models;
    }

    /**
     * @throws \borodulin\camunda\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionKill()
    {
        $bk = Yii::$app->session->get('bk');
        (new Message())->messageOne('killMessage', $bk);
        return $this->redirect(['camunda/index']);
    }
}