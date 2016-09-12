<?php
namespace wsserver\base;

use Yii;
use yii\base\Controller as BaseController;
use wsserver\base\InlineAction;
use wsserver\exceptions\ChatRunException;
use wsserver\base\Res;
use wsserver\base\RbacControl;

/**
 *
 */
class Controller extends BaseController
{
    public function say(){
        console("I say, " . $this->id . ':' . $this->action->id, '~');
    }
    public function error($message = null, $data = null){
        return $this->response($status = Res::STATUS_ERR, $data = null, $message = null);
    }
    public function success($data = null, $message = null){
        return $this->response($status = Res::STATUS_SUCC, $data = null, $message = null);
    }
    public function response($status = Res::STATUS_SUCC, $data = null, $message = null){
        $res = Res::getRouteRes();
        $res['route'] = Yii::$app->req->route;
        $res['body'] = [
            'status' => $status,
            'data' => $data,
            'message' => $message
        ];
        return Res::sendToCurrent($res);
    }

    public function runAction($id, $params = [])
    {
        $action = $this->createAction($id);
        if ($action === null) {
            throw new ChatRunException('Unable to resolve the request: ' . $this->getUniqueId() . '/' . $id);
        }
        $this->action = $action;
        $runAction = true;
        $result = null;
        if ($runAction && $this->beforeAction($action)) {
            $result = $action->runWithParams($params);
            $result = $this->afterAction($action, $result);
        }
        return $result;
    }
    public function createAction($id)
    {
        if ($id === '') {
            $id = $this->defaultAction;
        }
        $actionMap = $this->actions();
        if (isset($actionMap[$id])) {
            return Yii::createObject($actionMap[$id], [$id, $this]);
        } elseif (preg_match('/^[a-z0-9\\-_]+$/', $id) && strpos($id, '--') === false && trim($id, '-') === $id) {
            $methodName = 'action' . str_replace(' ', '', ucwords(implode(' ', explode('-', $id))));
            if (method_exists($this, $methodName)) {
                $method = new \ReflectionMethod($this, $methodName);
                if ($method->isPublic() && $method->getName() === $methodName) {
                    return new InlineAction($id, $this, $methodName);
                }
            }
        }
        return null;
    }
    public function bindActionParams($action, $params)
    {
        if ($action instanceof InlineAction) {
            $method = new \ReflectionMethod($this, $action->actionMethod);
        } else {
            $method = new \ReflectionMethod($action, 'run');
        }

        $args = [];
        $missing = [];
        $actionParams = [];
        foreach ($method->getParameters() as $param) {
            $name = $param->getName();
            if (array_key_exists($name, $params)) {
                if ($param->isArray()) {
                    $args[] = $actionParams[$name] = (array) $params[$name];
                } elseif (!is_array($params[$name])) {
                    $args[] = $actionParams[$name] = $params[$name];
                } else {
                    $errors = [['Invalid data received for parameter :' . $name]];
                    $this->response(Res::STATUS_ERR, null, $errors);
                    return false;
                }
                unset($params[$name]);
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $actionParams[$name] = $param->getDefaultValue();
            } else {
                $missing[] = $name;
            }
        }

        if (!empty($missing)) {
            $errors = [['Missing required parameters: params:' . implode(', ', $missing)]];
            $this->response(Res::STATUS_ERR, null, $errors);
            return false;
        }
        return $args;
    }
}
