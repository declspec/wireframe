<?php
class AuthModule implements IModule {
    public function build($app, $config, $ioc) {
        $ioc->singleton("PermissionManager", function($c) {
            require(__DIR__ . "/../lib/PermissionManager.php");            
            require(__DIR__ . "/../auth/store.php");
            
            // Build the providers
            $providers = array();
            
            $store = new PermissionStore($c->resolve("DatabaseConnection"));
            return new PermissionManager($store, $providers);
        });
        
        $app->registerComponent("user", function() use(&$ioc) {
            require(__DIR__ . "/../auth/user.php");
            return new UserComponent($ioc->resolve("Session"), $ioc->resolve("PermissionManager"));
        });
    }
};
?>