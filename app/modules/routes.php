<?php
// Module for building routes
class RoutesModule implements IModule {
    public function build($app, $config, $ioc) {
        // Customize routing here or leave the default
        $defaultController = isset($config["defaultController"]) ? $config["defaultController"] : null;
        
        foreach($config["routes"] as $route=>$settings) {
            $controllerName = isset($settings["controller"]) ? $settings["controller"] : $defaultController;
            if (empty($controllerName))
                throw new UnexpectedValueException("No controller was specified for the '$route' route and no 'defaultController' was configured");
                
            $app->route($route, function($app,$params) use(&$ioc, $controllerName, $settings) {
                $action = isset($settings["action"])
                    ? $settings["action"] 
                    : "index";

                if (isset($settings["policies"]) && !$ioc->resolve("PolicyManager")->run($settings["policies"], $app, $params))
                    return;

                $controller = $ioc->resolve(ucfirst($controllerName) . "Controller");
                if (method_exists($controller, $action))
                    return $controller->$action($app, $params);
                else if (method_exists($controller, "onMissingAction"))
                    return $controller->onMissingAction($action, $app, $params);
                else    
                    throw new RuntimeException("No action '{$action}' found in '{$controllerName}' controller and no onMissingAction method was available");
            });
        }

        // TODO: potentially set up a generic route.
    }
};