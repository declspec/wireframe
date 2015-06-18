(function(ng) {
    "use strict";
    
    function findParent(node, parentTag) {
        var parent = null,
            tag = parentTag.toLowerCase();
        
        while((parent = node.parentNode) !== null) {
            if (parent.tagName.toLowerCase() === tag)
                return parent;
            node = parent;
        }
        return null;
    }
    
    function labelize(field) {
        return field.replace(/([_-]|[A-Z])+/g, function(m, g) {
            return " " + ("_-".indexOf(g[0]) < 0 ? g.toLowerCase() : "");
        });
    }

    ng.module("shared").directive("formControl", function() {
        return {
            restrict: "A",
            link: function(scope, $element, attrs) {
                var parentForm = findParent($element[0], "form");
                    
                if (parentForm !== null) {
                    var formName = parentForm.name || "form",
                        identifier = attrs["formControl"],
                        message = attrs["message"] || ("You have specified an invalid value for '" + labelize(identifier) + "'; please enter a valid value."),
                        prop = formName + "." + identifier,
                        $child = ng.element('<span class="error-message">' + message + '</span>');
                    
                    scope.$watchCollection("[ " + prop + ".$invalid, " + prop + ".$dirty]", function(newValues, oldValues) {
                        var dirty = newValues[1],
                            invalid = newValues[0];
                        $element[dirty && invalid ? "addClass" : "removeClass"]("has-error");
                        $child[dirty && invalid ? "removeClass" : "addClass"]("ng-hide");
                    });
                    
                    $element.append($child);
                }
            }
        };
    });
}(angular));