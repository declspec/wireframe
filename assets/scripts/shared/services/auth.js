(function(ng) {
    "use strict";
    
    ng.module("shared").factory("AuthService", [ "AjaxService", function(AjaxService) {
        var active = function(email, password, callback) {
            callback(false, [ "This functionality has not yet been implemented" ]);
        };
        
        var passive = function(callback) {
            callback(false);
        };
        
        var terminate = function(callback) {
            callback(false);
        };
        
        return {
            activeAuthenticate: active,
            passiveAuthenticate: passive,
            terminateSession: terminate
        };
    }]);
}(angular));
