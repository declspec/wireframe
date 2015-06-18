(function(ng) {
    "use strict";
    
    ng.module("shared").factory("AjaxService", [ "$http", function($http) {
        function request_impl(promise, callback) {
            return promise.success(function(response) {
                if (response.hasOwnProperty("success") && response.success === true) 
                    callback(true, response.data);
                else
                    callback(false, response.hasOwnProperty("errors") ? response.errors : [ "An unknown error occurred" ]);
            }).error(function(status, response) {
                callback(false, (response && response.hasOwnProperty("errors") ? response.errors : [ "An unknown error occurred" ]), status);
            });
        }
        
        return {
            "get" : function(url, callback, config) { 
                return request_impl($http.get(url, config), callback);
            },
            "post" : function(url, params, callback, config) {
                return request_impl($http.post(url, params, config), callback);
            },
            "delete" : function(url, callback, config) {
                return request_impl($http.delete(url, config), callback);
            },
            "put" : function(url, params, callback, config) {
                return request_impl($http.put(url, params, config), callback);
            }
        };
    }]);
}(angular));