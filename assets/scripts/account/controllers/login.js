(function(ng) {
    "use strict";
    
    ng.module("account").controller("LoginController", [ "$scope", "$state", "AuthService", "ValidationService", "User", "DialogResult", 
        function($scope, $state, AuthService, ValidationService, User, DialogResult) {
            $scope.auth = {};
            $scope.errors = null;
            $scope.loading = false;

            $scope.submit = function() {
                $scope.errors = null;

                if (!ValidationService.validate($scope.login))
                    return;
                
                $scope.loading = true;
                AuthService.activeAuthenticate($scope.auth.email, $scope.auth.password, function(success, data) {
                    $scope.loading = false;

                    if (!success)
                        $scope.errors = data;
                    else {
                        User.login();
                        // Allow controller to be used in a modal context
                        ("undefined" !== typeof($scope.close)
                            ? $scope.close(DialogResult.Success, data)
                            : $state.go("index"));
                    }
                });
            };  
        }
    ]);
}(angular));