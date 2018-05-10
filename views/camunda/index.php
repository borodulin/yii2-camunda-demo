<?php
/** @var $this yii\web\View */
/** @var $models \app\models\TaskModel[] */

use yii\bootstrap\Tabs;
use yii\widgets\Pjax;

$items = [];

foreach ($models as $model) {
    $items[] = [
        'label' => $model->formName(),
        'content' => $this->render('_task', ['model' => $model]),
    ];
}

Pjax::begin([
]);

echo Tabs::widget([
    'items' => $items,
]);

echo \yii\helpers\Html::a('kill', ['camunda/kill']);

Pjax::end();