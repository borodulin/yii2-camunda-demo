<?php
/**
 * Created by PhpStorm.
 * User: borodulin
 * Date: 25.01.18
 * Time: 17:46
 */

namespace app\models;

use borodulin\camunda\Task;
use Yii;
use yii\base\Model;

class TaskModel extends Model
{
    private $task;
    private $_formName;

    private $_attributes = [];

    /**
     * TaskModel constructor.
     * @param $task
     * @param array $config
     * @throws \borodulin\camunda\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function __construct($task, array $config = [])
    {
        $this->task = $task;
        $this->_formName = $this->task['formKey'] ?? $this->task['name'] ?? $this->task['taskDefinitionKey'];
        $vars = (new Task())->getFormVariables($this->task['id']);
        foreach ((array)$vars as $key => $value) {
            $this->_attributes[$key] = $value['value'];
        }
        parent::__construct($config);
    }

    /**
     * @return string
     */
    public function formName()
    {
        return $this->_formName;
    }

    /**
     * @return array
     */
    public function attributes()
    {
        return array_keys($this->_attributes);
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [$this->attributes(), 'string']
        ];
    }

    /**
     * @param string $name
     * @return mixed
     * @throws \yii\base\UnknownPropertyException
     */
    public function __get($name)
    {
        if (isset($this->_attributes[$name]) || array_key_exists($name, $this->_attributes)) {
            return $this->_attributes[$name];
        } elseif ($this->hasAttribute($name)) {
            return null;
        }
        return parent::__get($name);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @throws \yii\base\UnknownPropertyException
     */
    public function __set($name, $value)
    {
        if ($this->hasAttribute($name)) {
            $this->_attributes[$name] = $value;
        } else {
            parent::__set($name, $value);
        }
    }

    public function hasAttribute($name)
    {
        return array_key_exists($name, $this->_attributes);
    }

    /**
     * @throws \borodulin\camunda\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function loadAndSave()
    {
        $request = Yii::$app->request;
        if ($request->isPost) {
            if ($this->load($request->post()) && $this->validate()){
                (new Task())->submitForm($this->task['id'], $this->getAttributes());
                return true;
            }
        }
        return false;
    }

    /**
     * @return mixed
     * @throws \borodulin\camunda\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function getRenderedForm()
    {
        return (new Task())->getRenderedForm($this->task['id']);
    }
}