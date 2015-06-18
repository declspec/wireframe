(function(ng) {
    "use strict";
    
    ng.module("shared").factory("User", [ function() {
        var user = null;
        
        return {
            isAuthenticated: function() { return !!user; },
            login: function(state) { user = state; },
            logout: function() { user = null; }
        };
    }]);
}(angular));
