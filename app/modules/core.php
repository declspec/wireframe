<?php
require(__DIR__ . "/../lib/Session.php");

class CoreModule implements IModule {
    public function build($app, $config, $ioc) {
        $settings = isset($config["settings"]) ? $config["settings"] : array();
    
        $ioc->singleton("DatabaseConnection", function() use(&$app,&$config) {
            require(__DIR__ . "/../lib/sqlext.php");
            return new SQLExt(
                $app,
                $config["db"]["connectionString"], 
                $config["db"]["username"], 
                $config["db"]["password"]
            );
        })->singleton("Session", new Session());

        $app->registerComponent("settings", function() use(&$settings) {
            require(__DIR__ . "/../config/settings.php");
            return new SettingsComponent($settings);
        })->registerComponent("session", $ioc->resolve("Session"));
    }
};
?>