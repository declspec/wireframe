<?php
class ControllersModule implements IModule {
    public function build($app, $config, $ioc) {
        $ioc->singleton("HomeController", function() {
            require(__DIR__ . "/../controllers/home.php");
            return new HomeController();
        });
    }
};