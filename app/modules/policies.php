<?php
require(__DIR__ . "/../lib/PolicyManager.php");

class PoliciesModule implements IModule {
    public function build($app, $config, $ioc) {
        $ioc->singleton("PolicyManager", function($c) {
            $manager = new PolicyManager();
            // Register policies here
            // $manager->registerPolicy("auth", new AuthPolicy());
            return $manager;
        });
    }
};