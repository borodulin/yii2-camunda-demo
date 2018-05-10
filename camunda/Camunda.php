<?php
/**
 * Created by PhpStorm.
 * User: borodulin
 * Date: 26.01.18
 * Time: 11:47
 */

namespace app\camunda;

use borodulin\camunda\CamundaApi;
use borodulin\camunda\Deployment;
use borodulin\camunda\ProcessDefinition;
use Yii;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

/**
 * Class Camunda
 * @property ProcessDefinition $definition
 * @package common\components
 */
class Camunda extends CamundaApi
{

    public $bpmnPath = '@app/camunda/bpmn';

    /**
     */
    public function init()
    {
        parent::init();
        $this->bpmnPath = rtrim(Yii::getAlias($this->bpmnPath), '/') . '/';
    }

    /**
     * @param $name
     * @throws Exception
     * @throws \borodulin\camunda\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function deploy($name)
    {
        $filename = $this->getFilename($name);
        (new Deployment())->create($name, $filename, true, true);
    }

    /**
     * @param $name
     * @return ProcessDefinition
     * @throws Exception
     * @throws \borodulin\camunda\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function getDefinition($name)
    {
        $definition = new ProcessDefinition();
        try {
            $definition->getDefinitionByKey($name);
            return $definition;
        } catch (\borodulin\camunda\Exception $exception) {
            $this->deploy($name);
        }
        $definition->getDefinitionByKey($name);
        return $definition;
    }

    /**
     * @param $name
     * @param $bk
     * @param null $variables
     * @return mixed
     * @throws Exception
     * @throws \borodulin\camunda\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function start($name, $bk, $variables = null)
    {
        $definition = $this->getDefinition($name);
        $result = $definition->startInstanceByKey($name, [
            'businessKey' => $bk,
            'variables' => $definition::translateVariables($variables),
        ]);
        if (!$instanceId = ArrayHelper::getValue($result, 'id')) {
            throw new Exception(VarDumper::dumpAsString($result));
        }
        return $instanceId;
    }

    /**
     * @throws \borodulin\camunda\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function clear()
    {
        $api = new Deployment();
        foreach ($api->getDeployments() as $deployment) {
            $api->deleteDeployment($deployment['id']);
        }
    }


    /**
     * @param $name
     * @param $bk
     * @param $variables
     * @return mixed
     * @throws Exception
     * @throws \borodulin\camunda\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function startForm($name, $bk, $variables)
    {
        $definition = $this->getDefinition($name);
        $result = $definition->submitStartFormByKey($name, $variables, $bk);
        if (!$instanceId = ArrayHelper::getValue($result, 'id')) {
            throw new Exception(VarDumper::dumpAsString($result));
        }
        return $instanceId;
    }

    /**
     * @param $name
     * @return string
     * @throws Exception
     */
    public function getFilename($name)
    {
        $filename = $this->bpmnPath . $name . '.bpmn';
        if (!file_exists($filename)) {
            throw new Exception("BPMN file $filename is not found");
        }
        return $filename;
    }
}