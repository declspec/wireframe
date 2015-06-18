(function(ng) {
    "use strict";
    
    var app = ng.module("my-app", [ "home", "account", "shared", "ui.router" ]);
    
    app.run(["$rootScope", function($rootScope) {
        $rootScope.APP_TITLE = "My Application";
        $rootScope.APP_VERSION = "1.0.0";
        
        $rootScope.$on("$stateChangeSuccess", function(event, toState) {
            $rootScope.STATE_TITLE = toState.title || "Untitled";
        });
    }]);
}(angular));