(function(ng) {
    "use strict";
    
    ng.module("account").controller("RegisterController", [ "$scope", "$window", "AjaxService", "ValidationService", 
        function($scope, $window, AjaxService, ValidationService) {
            $scope.user = {};
            $scope.errors = null;
            $scope.loading = false;
           
            $scope.submit = function() {
                if (!ValidationService.validate($scope.register))
                    return;
                
                $scope.errors = ["This functionality has not yet been implemented"];
            };
        }
    ]);
}(angular));