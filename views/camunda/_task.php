<?php
/** @var $this yii\web\View */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/** @var $model \app\models\TaskModel */

$form =  ActiveForm::begin([
    'options' => ['data-pjax' => true]
]);

foreach ($model->attributes() as $attribute) {
    echo $form->field($model, $attribute)->textInput();
}

echo Html::submitButton('Submit', ['class' => 'btn btn-primary', 'name' => 'submit-button']);

ActiveForm::end();

// echo $model->getRenderedForm();