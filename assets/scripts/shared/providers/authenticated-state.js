(function(ng) {
    "use strict";
    
    ng.module("shared").provider("$authenticatedState", [ "$stateProvider", function($stateProvider) {
        var policies = {};
        
        // Passive authentication
        var passiveAuth = ["$q", "AuthService", "User", 
            function($q, AuthService, User) {
                var defer = $q.defer();
                if (User.isAuthenticated())
                    defer.resolve();
                else {
                    AuthService.passiveAuthenticate(function(success, user) {
                        if (success) 
                            User.login(user.uid, user.sid, user.friendly);
                        return defer.resolve();
                    });
                }
                return defer.promise;
            }
        ];

        // Active authentication
        var activeAuth = [ "$q", "$state", "$modalDialog", "AuthService", "User",
            function($q, $state, $modalDialog, AuthService, User) {
                var defer = $q.defer();

                passiveAuth($q, AuthService, User).then(function() {
                    if (User.isAuthenticated())
                        defer.resolve();
                    else {
                        $modalDialog.show("login-dialog", function() {
                            if (User.isAuthenticated())
                                defer.resolve();
                            else {
                                defer.reject();
                                $state.go("denied");
                            }
                        });
                    }
                });

                return defer.promise;
            }
        ];

        this.decorator = function(name, func) {
            return $stateProvider.decorator.call(this, name, func);   
        };
        
        this.state = function(name, definition) {
            patchDef(name, "object" === typeof(name) ? name : definition);
            return $stateProvider.state.call(this, name, definition);  
        };
        
        if (typeof($stateProvider.$get) === "function")
            this.$get = $stateProvider.$get.bind(this);
        else {
            var get = $stateProvider.$get;
            get[get.length - 1] = get[get.length - 1].bind(this);
            this.$get = get;
        }

        function patchDef(name, def) {
            var authenticate = def.authenticate !== false;
            
            if ("object" !== typeof(name)) {
                // If the name indicates a child state, try inherit the auth policy of the
                // immediate parent
                if (name.indexOf(".") > 0) {
                    var parent = name.substring(0, name.lastIndexOf("."));
                    if (policies.hasOwnProperty(parent)) 
                        authenticate = policies[parent];
                }
                policies[name] = authenticate;
            }
            
            var resolver = { auth: (authenticate ? activeAuth : passiveAuth) };
            
            def.resolve = "undefined" !== typeof(def.resolve)
                ? ng.extend(def.resolve, resolver)
                : resolver;
        } 
    }]);
}(angular));