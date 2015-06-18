(function(ng) {
    "use strict";
    
    ng.module("shared").factory("ValidationService", function() {
        return {
            validate: function(form) {
                var valid = true;
                for(var key in form) {
                    if (form.hasOwnProperty(key) && key[0] !== "$") {
                        form[key].$dirty = true;
                        valid = valid && !form[key].$invalid;
                    }
                }
                return valid;
            }
        };
    });
}(angular));