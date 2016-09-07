<?php
namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use yii\rbac\PhpManager;

/**
 * rbac控制命令
 */
class RbacController extends Controller{

    public $controllerSpace = "common\\controllers";

    public function options($actionID)
    {
        return array_merge(
            parent::options($actionID),
            ['controllerSpace']
        );
    }
    public function actionRun(){
        $this->updateRole();
        $this->updatePermissions();
    }
    public function beforeAction(){
        @unlink(Yii::getAlias('@console/rbac/rules.php'));
        @unlink(Yii::getAlias('@console/rbac/items.php'));
        @unlink(Yii::getAlias('@console/rbac/assigments.php'));
        return true;
    }
    private function updatePermissions(){
        $auth = new PhpManager();
        list($validActions, $unValidActions) = $this->getDef();
        $validPermissions = [];
        foreach($validActions as $controllerClass => $actions){
            $id = $this->getControllerName($controllerClass);
            foreach($actions as $actionItem){
                $action = $this->getActionName($actionItem['name']);
                $permissionName = $id . ':' . $action;
                $newPermission = $auth->createPermission($permissionName);
                if($actionItem['rule']){
                    $rule = new $actionItem['rule'];
                    $auth->add($rule);
                    printf("发现权限%s的规则%s\n", $permissionName, $rule->name);
                    $newPermission->ruleName = $rule->name;
                }
                $newPermission->description = $actionItem['summary'];
                $auth->add($newPermission);
                echo "已经保存权限：{$permissionName}\n";
                $validPermissions[$id][] = $permissionName;
            }
        }
        echo "--------\n";
        $permissionAssign = Yii::$app->auth->permissionAssign;
        foreach($permissionAssign as $roleName => $item){
            $role = $auth->getRole($roleName);
            if('*' === $item){
                foreach($validPermissions as $id => $subItem){
                    foreach($subItem as $permissionName){
                        $permission = $auth->getPermission($permissionName);
                        if(!$auth->hasChild($role, $permission)){
                            $auth->addChild($role, $permission);
                            printf("已经将权限%s分配到角色%s\n", $permissionName, $roleName);
                        }else{
                            printf("角色%s已经拥有权限%s\n", $roleName, $permissionName);
                        }
                    }
                }
            }elseif(is_array($item)){
                foreach($item as $id => $subItem){
                    if('*' === $subItem && array_key_exists($id, $validPermissions)){
                        foreach($validPermissions[$id] as $permissionName){
                            $permission = $auth->getPermission($permissionName);
                            if(!$auth->hasChild($role, $permission)){
                                $auth->addChild($role, $permission);
                                printf("已经将权限%s分配到角色%s\n", $permissionName, $roleName);
                            }else{
                                printf("角色%s已经拥有权限%s\n", $roleName, $permissionName);
                            }
                        }
                    }elseif(is_array($subItem) && array_key_exists($id, $validPermissions)){
                        foreach ($subItem as $action) {
                            $permissionName = $id . ':' . $action;
                            if(in_array($permissionName, $validPermissions[$id])){
                                $permission = $auth->getPermission($permissionName);
                                if(!$auth->hasChild($role, $permission)){
                                    $auth->addChild($role, $permission);
                                    printf("已经将权限%s分配到角色%s\n", $permissionName, $roleName);
                                }else{
                                    printf("角色%s已经拥有权限%s\n", $roleName, $permissionName);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    private function updateRole(){
        $roleAssign = Yii::$app->auth->roleAssign;
        $auth = new PhpManager();
        foreach($roleAssign as $roleName => $item){
            $newRole = $auth->createRole($roleName);
            $newRole->description = $item['summary'];
            $auth->add($newRole);
            if(!$auth->getAssignment($roleName, $item['assignId'])){
                $auth->assign($newRole, $item['assignId']);
                printf("已经创建角色%s,同时分配%d到该角色\n", $roleName, $item['assignId']);
            }else{
                printf("角色%s已经被注入到%d\n", $roleName, $item['assignId']);
            }
        }
    }
    public function actionGetRole(){
        $roleAssign = Yii::$app->auth->roleAssign;
        printf("%-30s%-30s%-30s\n", 'role-name', 'assign-id', 'summary');
        foreach($roleAssign as $roleName => $item){
            printf("%-30s%-30s%-30s\n", $roleName, $item['assignId'], $item['summary']);
        }
    }
    public function actionGetPermissions(){
        list($validActions, $unValidActions) = $this->getDef();
        if(!empty($validActions)){
            printf("以下是需要鉴权的动作，请检查：\n");
            printf("%-30s%-30s%-30s\n", 'controller', 'action', 'summary');
            foreach($validActions as $key => $actions){
                echo "\n";
                foreach($actions as $action){
                    printf("%-30s%-30s%-30s\n", substr($key, strrpos($key, '\\') + 1), $action['name'], $action['summary']);
                }
            }
        }
        if(!empty($unValidActions)){
            echo "----------\n";
            printf("以下是不需要鉴权的动作，请检查：\n");
            printf("%-30s%-30s%-30s\n", 'controller', 'action', 'summary');
            foreach($unValidActions as $key => $actions){
                echo "\n";
                foreach($actions as $action){
                    printf("%-30s%-30s%-30s\n", substr($key, strrpos($key, '\\') + 1), $action['name'], $action['summary']);
                }
            }
        }
    }
    private function getDef(){
        $validControllers = $this->getControllers();
        $validActions = [];
        $unValidActions = [];
        foreach($validControllers as $controllerClass){
            list($valid, $unValid) = $this->getActions($controllerClass);
            if(!empty($valid)){
                $validActions[$controllerClass] = $valid;
            }
            if(!empty($unValid)){
                $unValidActions[$controllerClass] = $unValid;
            }
        }
        return [$validActions, $unValidActions];
    }
    private function getActions($controllerClass){
        $actions = [];
        if(class_exists($controllerClass)){
            return $this->getMethods($controllerClass);
        }else{
            return [];
        }
    }
    private function getMethods($controllerClass){
        $valid = [];
        $unValid = [];
        if(class_exists($controllerClass)){
            $reflection = new \ReflectionClass($controllerClass);
            $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
            foreach($methods as $method){
                if(0 === strpos($method->getName(), 'action') && 0 !== strpos($method->getName(), 'actions')){
                    $summary = "";
                    $ruleClass = "";
                    $docLines = false;
                    $docBlock = $method->getDocComment();
                    if(false !== $docBlock){
                        $docLines = preg_split('~\R~u', $docBlock);
                        if (isset($docLines[1])) {
                            $summary = trim($docLines[1], "\t *");
                        }
                        $ruleClass = $this->findRule($docLines);
                    }
                    if($this->isNeedAuth($docLines)){
                        $valid[] = [
                            'name' => $method->getName(),
                            'summary' => $summary,
                            'rule' => $ruleClass,
                        ];
                    }else{
                        $unValid[] = [
                            'name' => $method->getName(),
                            'summary' => $summary,
                            'rule' => $ruleClass
                        ];
                    }
                }
            }

            return [$valid, $unValid];
        }else{
            return [$valid, $unValid];
        }
    }
    private function getControllers(){
        $valid = [];
        $namespace = $this->controllerSpace;
        $coreDir = Yii::getAlias('@' . preg_replace('/\\\/', '/', $namespace) );
        if(is_dir($coreDir)){
            foreach(glob($coreDir . '/*')  as $file){
                if(is_file($file) &&
                false !== strpos(basename($file), 'Controller.php') &&
                class_exists($className = $namespace . '\\' . trim(basename($file), '.php'))){
                    $reflection = new \ReflectionClass($className);
                    $docLines = preg_split('~\R~u', $reflection->getDocComment());
                    // if($this->isNeedAuth($docLines)){
                        $valid[] = $className;
                    // }
                }
            }
            return $valid;
        }else{
            $this->stderr("{$coreDir}内核控制器不存在\n");
        }
    }
    private function isNeedAuth($docLines){
        if(!is_array($docLines)){
            return false;
        }
        foreach($docLines as $line){
            if(strpos($line, '@auth') && preg_match('/:\s*true[\s]*$/', $line)){
                return true;
            }
        }
        return false;
    }
    private function findRule($docLines){
        if(!is_array($docLines)){
            return false;
        }
        foreach($docLines as $line){
            if(strpos($line, '@rule')){
                $content = trim(substr($line, strpos($line, ':') + 1), ' ');
                if(class_exists($content)){
                    return $content;
                }
            }
        }
        return false;
    }
    private function getControllerName($controllerClass){
        $id = substr($controllerClass, strrpos($controllerClass, '\\') + 1);
        $id = preg_replace('/Controller/', '', $id);
        $id = strtolower(trim(preg_replace('/([A-Z])/', ' $1', $id), ' '));
        return preg_replace('/\s/', '-', $id);
    }
    private function getActionName($actionName){
        $name = preg_replace('/action/', '', $actionName);
        $name = strtolower(trim(preg_replace('/([A-Z])/', ' $1', $name), ' '));
        return preg_replace('/\s/', '-', $name);
    }
}
