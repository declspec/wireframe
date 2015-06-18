(function(ng) {
    "use strict";
    
    var module = ng.module("home", [ "shared", "ui.router" ]);
    
    module.config([ "$authenticatedStateProvider", "$urlRouterProvider", function($authenticatedStateProvider, $urlRouterProvider) {
        $authenticatedStateProvider
            .state("index", {
                url: "/",
                title: "Home",
                templateUrl: "partials/home/index.html",
                authenticate: false
            });
        
        $urlRouterProvider.otherwise("/");
    }]);
}(angular));