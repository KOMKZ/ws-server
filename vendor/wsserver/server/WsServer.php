<?php
namespace wsserver\server;

use Yii;
use yii\helpers\ArrayHelper;
use yii\base\Component;
use wsserver\exceptions\NotFoundCommandException;
use \GatewayWorker\BusinessWorker;
use \GatewayWorker\Gateway as GatewayWorker;
use \GatewayWorker\Lib\Gateway;
use \GatewayWorker\Register;
use yii\base\InvalidRouteException;
use wsserver\exceptions\ChatRunException;
use wsserver\base\Res;
use Workerman\Lib\Timer;
use yii\base\BootstrapInterface;
use wsserver\helpers\Curl;





/**
 *
 */
class WsServer extends Component implements BootstrapInterface
{
    private $_commandNameSpace = 'wsserver\\server\\commands';
    private $_commandClass = [];
    private $_requestRoute = null;
    private $_requestParams = null;

    CONST RUN_IN_DAEMON = true;
    CONST RUN_OUT_DAEMON = false;

    public $name = 'c-chat';

    public $secretKey = null;

    private $defaultWorkerConfig = [
        'workerCount' => 4,
        'name' => 'BusinessWorker',
        'eventHandler' => '\\wsserver\\server\\wsserver',
        'registerAddress' => '127.0.0.1:1236',
    ];
    private $defaultGatewayConfig = [
        'port' => '7272',
        'name' => 'gateway-worker',
        'count' => 4,
        'lanIp' => '127.0.0.1',
        'startPort' => 2300,
        'pingInterval' => 3,
        'pingData' => 'hello world',
        'registerAddress' => '127.0.0.1:1236',
    ];
    private $defaultRegisterConfig = [
        'address' => '127.0.0.1:1236'
    ];
    private $workerStarter = [
        'worker' => 'initBussinessWorker',
        'gateway' => 'initGatewayWorker',
        'register' => 'initRegisterWorker'
    ];


    private $_workerConfig = [];
    private $_gatewayConfig = [];
    private $_registerConfig = [];

    public $workers = ['worker', 'gateway', 'register'];


    public $defaultRoute = 'index';
    public $coreControlleNamespace = 'common\\controllers';

    public $closeNotLogin = true;
    public $ifClientNoLogin = null;
    public $maxDelayInLogin = 2;
    public $maxFailedLoginTime = 10;
    public $loadRemoteConfig = true;
    public $remoteConfigCallback = null;

    public function bootstrap($app){
        // if($this->loadRemoteConfig){
        //     $this->loadConfigFromRemote();
        // }
    }


    public function loadConfigFromRemote(){
        if(is_array($this->remoteConfigCallback) &&
           count($this->remoteConfigCallback) >=2 &&
           class_exists($this->remoteConfigCallback[0]) &&
           method_exists($this->remoteConfigCallback[0], $this->remoteConfigCallback[1])){
               $config = call_user_func_array($this->remoteConfigCallback, []);
               foreach($config as $configName => $value){
                   Yii::$app->wsserver->$configName = $value;
               }
        }else{
            throw new \Exception('您设置了必须加载远程配置，但是配置远程配置的处理方式发生错误' . get_class($this) . ' ' . __METHOD__);
        }

    }



    public function getWorkerStarter($name){
        if(array_key_exists($name, $this->workerStarter)){
            return $this->workerStarter[$name];
        }
        return null;
    }

    public function setWorkerConfig($config){
        if(!$this->_workerConfig) $this->_workerConfig = $this->defaultWorkerConfig;
        $config = ArrayHelper::merge($this->_workerConfig, $config);
        $this->_workerConfig = $config;
    }
    public function getWorkerConfig(){
        if(empty($this->_workerConfig)){
            $this->setWorkerConfig([]);
        }
        return $this->_workerConfig;
    }

    public function setRegisterConfig($config){
        if(!$this->_registerConfig) $this->_registerConfig = $this->defaultRegisterConfig;
        $config = ArrayHelper::merge($this->_registerConfig, $config);
        $this->_registerConfig = $config;
    }
    public function getRegisterConfig(){
        if(empty($this->_registerConfig)){
            $this->setRegisterConfig([]);
        }
        return $this->_registerConfig;
    }

    public function setGatewayConfig($config){
        if(!$this->_gatewayConfig) $this->_gatewayConfig = $this->defaultGatewayConfig;
        $config = ArrayHelper::merge($this->_gatewayConfig, $config);
        $config['pingData'] = Res::format(Res::getPingRes());
        $this->_gatewayConfig = $config;
    }
    public function getGatewayConfig(){
        if(empty($this->_gatewayConfig)){
            $this->setGatewayConfig([]);
        }
        return $this->_gatewayConfig;
    }


    /**
     * 实现程序运行逻辑
     * @param  [type] $action    [description]
     * @param  [type] $daemonize [description]
     * @return [type]            [description]
     */
    public function run($action, $daemonize  = self::RUN_OUT_DAEMON){
        try {
            $this->initCoreWorker();
            $this->runCommand($action, $daemonize);
        } catch (NotFoundCommandException $e) {
            console('发生错误:', $e->getMessage(), '~');
        } catch (\Exception $e){
            throw $e;
        }
    }
    public function runCommand($action, $daemonize){
        $commandClass = $this->getCommandClass($action);
        if($commandClass){
            // 执行对应的命令
            $commandClass::$daemonize = $daemonize;
            $commandClass::execute();
        }else{
            throw new NotFoundCommandException("{$action}不存在对应的命令" . get_class($this) . ',' . __METHOD__);
        }
    }
    public function getCommandClass($action){
        if(array_key_exists($action, $this->_commandClass)){
            return $this->_commandClass[$action];
        }
        $commandClass = $this->_commandNameSpace . '\\' . ucfirst($action) . 'Command';
        if(class_exists($commandClass)){
            return $this->_commandClass[$action] = $commandClass;
        }else{
            return null;
        }
    }
    public function initCoreWorker(){
        foreach($this->workers as $worker){
            $starter = $this->getWorkerStarter($worker);
            if($starter !== null){
                call_user_func_array([static::className(), $starter], []);
            }
        }
    }
    /**
     * 初始化工作worker
     * @return [type] [description]
     */
    public static function initBussinessWorker(){
        $worker = new BusinessWorker();
        $worker->name = Yii::$app->wsserver->workerConfig['name'];
        $worker->count = Yii::$app->wsserver->workerConfig['workerCount'];
        $worker->eventHandler = Yii::$app->wsserver->workerConfig['eventHandler'];
        $worker->registerAddress = Yii::$app->wsserver->workerConfig['registerAddress'];
    }
    /**
     * 初始化路由worker
     * @return [type] [description]
     */
    public static function initGatewayWorker(){
        $port = Yii::$app->wsserver->gatewayConfig['port'];
        $gateway = new GatewayWorker("Websocket://0.0.0.0:{$port}");
        $gateway->name = Yii::$app->wsserver->gatewayConfig['name'];
        $gateway->count = Yii::$app->wsserver->gatewayConfig['count'];
        $gateway->lanIp = Yii::$app->wsserver->gatewayConfig['lanIp'];
        $gateway->startPort = Yii::$app->wsserver->gatewayConfig['startPort'];
        $gateway->pingInterval = Yii::$app->wsserver->gatewayConfig['pingInterval'];
        $gateway->pingData = Yii::$app->wsserver->gatewayConfig['pingData'];
        $gateway->registerAddress = Yii::$app->wsserver->gatewayConfig['registerAddress'];
    }
    /**
     * 初始化中转worker
     * @return [type] [description]
     */
    public static function initRegisterWorker(){
        $address = Yii::$app->wsserver->registerConfig['address'];
        $register = new Register("text://" . $address);
    }
    /**
     * 实现聊天服务逻辑
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function chatRun($data){
        $this->handleRequest(Yii::$app->req, $data);

        // try {
        //     $this->handleRequest(Yii::$app->req, $data);
        // } catch (\Exception $e) {
        //     throw new $e;
        //     // console($e->getMessage(), '~');
        // }

    }
    public function handleRequest($request, $data){
        list($route, $params) = $request->resolve($data);
        $this->_requestRoute = $route;
        $this->_requestParams = $params;
        $this->runAction($route, $params);
    }
    public function runAction($route, $params){
        $parts = $this->createController($route);
        if (is_array($parts)) {
            /* @var $controller Controller */
            list($controller, $actionID) = $parts;
            return $controller->runAction($actionID, $params);
        } else {
            throw new ChatRunException('Unable to resolve the request:' . $route);
        }
    }
    public function createController($route){
        if ($route === '') {
            $route = $this->defaultRoute;
        }
        $route = trim($route, '/');
        if (strpos($route, '//') !== false) {
            return false;
        }
        if (($pos = strpos($route, '/')) !== false) {
            $id = substr($route, 0, $pos);
            $route = substr($route, $pos + 1);
        }else{
            return false;
        }
        $controller = $this->createControllerByID($id);
        return $controller === null ? false : [$controller, $route];
    }

    public function createControllerByID($id){
        $className = str_replace(' ', '', ucwords(str_replace('-', ' ', $id))) . 'Controller';
        $className = ltrim($this->coreControlleNamespace . '\\'  . $className);

        if (strpos($className, '-') !== false || !class_exists($className)) {
            return null;
        }
        if (is_subclass_of($className, 'yii\base\Controller')) {
            $controller = Yii::createObject($className, [$id, $this]);
            return get_class($controller) === $className ? $controller : null;
        } else{
            return null;
        }
    }

    public function getMsgManager(){
        return Yii::$app->msg;
    }
    public static function onMessage($clientId, $data){
        Yii::$app->wsserver->chatRun(['clientId' => $clientId, 'body' => $data]);
    }
    public static function onConnect($clientId){
        printf("new connect:%s\n", $clientId);
        Yii::$app->req->clientId = $clientId;
        if(Yii::$app->wsserver->closeNotLogin){
            $maxFailedTime = Yii::$app->wsserver->getNotLoginTime($_SERVER['REMOTE_ADDR']);
            if($maxFailedTime >= Yii::$app->wsserver->maxFailedLoginTime){
                self::warning('当前ip被禁止登录' . $_SERVER['REMOTE_ADDR']);
                console($clientId, '当前ip被禁止登录', '~');
                //  todo 这里如果close掉的话，客户端断开重连的话会非常消耗io
                // 应该在协议层搞死他
            }else{
                self::addAuthTimer($clientId);
                Yii::$app->wsserver->incrementNotLogin($_SERVER['REMOTE_ADDR']);
            }
        }
    }
    public function getNotLoginTime($address){
        $key = "failed-login" . $address;
        return (int)Yii::$app->cache->get($key);
    }
    public function incrementNotLogin($address){
        $key = "failed-login" . $address;
        $value = Yii::$app->cache->get($key);
        if(false === $value){
            Yii::$app->cache->add($key, 1, 0);
        }else{
            Yii::$app->cache->set($key, ++$value, 0);
        }
    }
    public static function onClose($clientId){

    }

    public static function onWorkerStart(){
        // todo 考虑是否要擦擦
        // 1 静止登录ip
        Yii::$app->cache->flush();
    }

    public static function addAuthTimer($clientId){
        $limitTime = Yii::$app->wsserver->maxDelayInLogin;
        $ifClientNoLogin = Yii::$app->wsserver->ifClientNoLogin;
        if(count($ifClientNoLogin) >= 2 && class_exists($ifClientNoLogin[0]) && method_exists($ifClientNoLogin[0], $ifClientNoLogin[1])){
            $timer_id = Timer::add($limitTime, $ifClientNoLogin, [$clientId], false);
        }else{
            self::warning(__METHOD__ . ' 出错，addAuthTimer不能够找到相关定义');
        }
    }


    public function getRequestRoute(){
        return $this->_requestRoute;
    }
    public function getRequestParams(){
        return $this->_requestParams;
    }

    public static function warning($msg){
        Yii::warning($msg, Yii::$app->wsserver->name);
    }
    public static function error($msg){
        Yii::error($msg, Yii::$app->wsserver->name);
    }






}
