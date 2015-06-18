(function(ng) {
    "use strict";
    
    ng.module("shared").factory("User", [ function() {
        var loggedIn = false;
        
        return {
            isAuthenticated: function() { return loggedIn; },
            login: function() { loggedIn = true; },
            logout: function() { loggedIn = false }
        };
    }]);
}(angular));