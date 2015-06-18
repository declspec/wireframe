(function(ng) {
    "use strict";
    
    var mod = ng.module("account", [ "shared", "ui.router" ]);
    
    mod.config([ "$stateProvider", "$urlRouterProvider", 
        function($stateProvider, $urlRouterProvider) {
            $stateProvider
                .state("register", {
                    url: "/account/register",
                    templateUrl: "partials/account/register.html",
                    controller: "RegisterController",
                    title: "Register"
                })
                .state("login", {
                    url: "/account/login",
                    templateUrl: "partials/account/login.html",
                    controller: "LoginController",
                    title: "Log In"
                });
        }
    ]);
}(angular));