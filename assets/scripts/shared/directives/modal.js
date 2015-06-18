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
    
    module.directive("modalDialog", [ "$modalDialog", "DialogResult", function($modalDialog, DialogResult) {
        return {
            restrict: "ECA",
            priority: 400,
            terminal: true,
            transclude: "element",
            link: function(scope, $element, attr, ctrl, $transclude) {
                var currentScope,
                    currentElement,
                    currentDialog;
                                
                scope.$on("$dialogChangeSuccess", update);
                update();
                
                function cleanup() {
                    if (currentScope) {
                        // Calling currentScope.close here will result in infinite recursion
                        if (!currentScope.__closed && currentDialog && currentDialog.callback)
                            currentDialog.callback(DialogResult.Cancelled);
                        
                        currentScope.$destroy();
                        currentScope = null;
                        currentDialog = null;
                    }
                    
                    if (currentElement) {
                        currentElement.remove();
                        currentElement = null;
                    }
                }
                
                function update() {
                    var current = currentDialog = $modalDialog.current,
                        locals = current && current.locals,
                        template = locals && locals.$template;

                    if (!current || ng.isUndefined(template))
                        cleanup();
                    else {
                        var newScope = scope.$new(),
                            cancellable = current.cancellable !== false;
                        
                        newScope.__closed = false;
                        
                        $transclude(newScope, function(clone) {
                            cleanup();
                            
                            if (cancellable) {
                                // Bind the click event to close the dialog with the "Cancelled" reason
                                clone.bind("click", function() {
                                    newScope.close(DialogResult.Cancelled); 
                                });
                            }
                            
                            $element.after(currentElement = clone);
                        });
                        
                        newScope.close = function() {
                            if (!newScope.__closed) {
                                // Important to set __closed before cleanup as cleanup
                                // will also invoke the callback with the "Cancelled" reason                                
                                newScope.__closed = true;
                                cleanup();
                                
                                if (current.callback)
                                    current.callback.apply(null, arguments);
                            }
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
    
    module.directive("modalDialog", [ "$modalDialog", "$controller", "$compile", function($modalDialog, $controller, $compile) {
        return {
            restrict: "ECA",
            priority: -400,
            link: function(scope, $element) {
                var current = $modalDialog.current;
                
                if (ng.isDefined(current) && current !== null) {
                    var locals = current.locals;

                    $element.html('<div class="modal-dialog-wrapper" onclick="event.stopPropagation();">' + locals.$template + '</div>');

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
    }]);
    
    module.provider("$modalDialog", function() {
        var dialogs = {};
        
        this.register = function(name, config) {
            dialogs[name] = ng.copy(config);
            return this;
        };
        
        this.$get = [ "$q", "$sce", "$rootScope", "$templateRequest", "DialogResult", get ];
        
        function get($q, $sce, $rootScope, $templateRequest, DialogResult) {
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
                if (ng.isUndefined(callback)) {
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
                            template = $templateRequest(templateUrl);
                        }
                    }

                    if (ng.isDefined(template))
                        locals.$template = template;

                    return $q.all(locals);
                }).then(function(locals) {
                    if (next !== $modal.current)
                        return next.callback(DialogResult.Cancelled);
                    
                    next.locals = locals;
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