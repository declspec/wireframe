(function(ng) {
    "use strict";
    
    function inherit(parent, extra) {
        return ng.extend(Object.create(parent), extra);
    }
    
    var module = ng.module("modalDialog", []);
    
    module.constant("DialogResult", {
        Success: 0,
        Cancelled: 1,
        Error: 2
    });
    
    module.directive("modalDialog", [ "$modalDialog", "$document", "DialogResult", function($modalDialog, $document, DialogResult) {
        return {
            restrict: "ECA",
            priority: 400,
            terminal: true,
            transclude: "element",
            link: function(scope, $element, attr, ctrl, $transclude) {
                var currentScope,
                    currentElement,
                    currentDialog;
                
                if (attr["modalDialog"] === "root") {
                    // Shift element to body tag
                    var $body = ng.element($document[0].body);
                    $element.detach();
                    $body.append($element);
                }

                scope.$on("$dialogChangeSuccess", update);
                update();
                
                function cleanup() {
                    if (currentScope) {
                        currentScope.$destroy();
                        currentScope = null;
                    }
                    
                    if (currentElement) {
                        currentElement.remove();
                        currentElement = null;
                    }
                }
                
                function update() {
                    var current = $modalDialog.current,
                        locals = current && current.locals,
                        template = locals && locals.$template;
                    
                    // If replacing an existing dialog that has yet to be 
                    // closed...
                    if (currentDialog && currentDialog.callback)
                        currentDialog.callback(DialogResult.Cancelled);

                    currentDialog = current;

                    if (!current || ng.isUndefined(template))
                        cleanup();
                    else {
                        var newScope = scope.$new();
                        
                        $transclude(newScope, function(clone) {
                            cleanup();
                            $element.after(currentElement = clone);
                        });
                        
                        newScope.close = function() {
                            cleanup();
                            if (current.callback)
                                current.callback.apply(null, arguments);
                            
                            if (current === currentDialog)
                                currentDialog = null;
                        };
                            
                        // Give the 'current' dialog a close method
                        // so that external code can manually close the dialog if needed
                        // current.close = newScope.close;
                        currentScope = current.scope = newScope;
                        currentScope.$emit("$dialogContentLoaded");
                    }
                }
            }
        };
    }]);
    
    module.directive("modalDialog", ["$modalDialog", "$document", "$controller", "$compile", "DialogResult",
        function ($modalDialog, $document, $controller, $compile, DialogResult) {
            return {
                restrict: "ECA",
                priority: -400,
                link: function(scope, $element) {
                    var current = $modalDialog.current;

                    if (ng.isDefined(current) && current !== null) {
                        var locals = current.locals,
                            cancellable = current.cancellable !== false

                        scope.cancel = function() {
                            scope.close(DialogResult.Cancelled);
                        };

                        $element.html(
                            '<div class="modal-dialog-shadow"' + (cancellable ? ' ng-click="cancel()"' : '') + '></div>' +
                            '<div class="modal-dialog-wrapper">' + (cancellable ? '<span class="modal-dialog-cancel" ng-click="cancel()">&nbsp;</span>' : '') + locals.$template + '</div>'
                        );

                        var link = $compile($element.contents());

                        if (current.controller) {
                            locals.$scope = scope;
                            var controller = $controller(current.controller, locals);
                            if (current.controllerAs) {
                                scope[current.controllerAs] = controller;
                            }
                            $element.data("$ngControllerController", controller);
                            $element.children().data("$ngControllerController", controller);
                        }

                        link(scope);
                    }
                }
            };
        }
    ]);

    module.provider("$modalDialogParams", function() {
        this.$get = function () { return { }; };
    });
    
    module.provider("$modalDialog", function() {
        var dialogs = {};
        
        this.register = function(name, config) {
            dialogs[name] = ng.copy(config);
            return this;
        };
        
        this.$get = [ "$q", "$sce", "$rootScope", "$http", "$templateCache", "$modalDialogParams", "DialogResult", get ];
        
        function get($q, $sce, $rootScope, $http, $templateCache, $modalDialogParams, DialogResult) {
            var prepared = null;
            
            var $modal = {
                dialogs: dialogs,
                current: null,
                show: show
            };
            
            function prepare(name, params, callback) {
                var last = $modal.current,
                    current = $modal.dialogs[name];
                
                // Use inherit to clone the actual dialog config
                prepared = current && inherit(current, {
                    params: params,
                    callback: callback
                });
                
                prepared.$$dialog = prepared;
                
                return (last || prepared) && 
                  !$rootScope.$broadcast("$dialogChangeStart", prepared, last).defaultPrevented;
            }
            
            function show(name, params, callback) {
                if (ng.isUndefined(callback) && ng.isFunction(params)) {
                    callback = params;
                    params = {};
                }
                
                if (!prepare(name, params, callback))
                    return callback(DialogResult.Cancelled);
                
                var last = $modal.current,
                    next = $modal.current = prepared;
                
                $q.when(prepared).then(function() {
                    var locals = {},
                        template,
                        templateUrl;
                    
                    if (ng.isDefined(template = next.template)) {
                        if (ng.isFunction(template))
                            template = template(params);
                    }
                    else if (ng.isDefined(templateUrl = next.templateUrl)) {
                        if (ng.isFunction(templateUrl))
                            templateUrl = templateUrl(params);

                        templateUrl = $sce.getTrustedResourceUrl(templateUrl);
                        if (ng.isDefined(templateUrl)) {
                            next.loadedTemplateUrl = templateUrl;
                            template = $http.get(templateUrl, {cache: $templateCache}).then(function(response) { 
                                return response.data; 
                            });
                        }
                    }

                    if (ng.isDefined(template))
                        locals.$template = template;

                    return $q.all(locals);
                }).then(function(locals) {
                    if (next !== $modal.current)
                        return next.callback(DialogResult.Cancelled);
                    
                    next.locals = locals;
                    ng.copy(next.params, $modalDialogParams);
                    $rootScope.$broadcast("$dialogChangeSuccess", next, last);
                }, function(error) {
                    if (next !== $modal.current) 
                        return next.callback(DialogResult.Cancelled);
                    
                    $rootScope.$broadcast("$dialogChangeError", next, last, error);
                    next.callback(DialogResult.Error, error);
                });
                
                return function() {
                    if (next !== $modal.current)
                        next.callback(DialogResult.Cancelled);
                    else if ("undefined" !== typeof(next.scope) && "undefined" !== typeof(next.scope.close))
                        next.scope.close(DialogResult.Cancelled);
                    else {
                        // TODO: Investigate potential weird behaviour with this when called after $dialogChangeSuccess 
                        // but before ngModalDialog directive assigns the 'close' function.
                        // the assumption I'm making at the moment is that the only time 'close' is not yet assigned to the dialog
                        // is before the '$dialogChangeSuccess' block above has been reached. In which case next !== null will always be true
                        // and the callback will be fired. This is super racey and why would you open a dialog and immediately close it?
                        $modal.current = null;
                    }
                };
            }
            
            return $modal;
        }
    });
}(angular));