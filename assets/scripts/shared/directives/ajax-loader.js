(function(ng) {
    "use strict";
    
    ng.module("shared").directive("ajaxLoader", function() {
        return {
            restrict: "A",
            link: function(scope, $element, attrs) {
                scope.$watch(attrs.ajaxLoader, function(value) {
                    $element[!!value ? "removeClass" : "addClass"]("ng-hide");
                });
                
                var size = attrs["size"] || "small";
                $element.addClass("ajax-loader ajax-loader-" + size);
            }
        };
    });
}(angular));