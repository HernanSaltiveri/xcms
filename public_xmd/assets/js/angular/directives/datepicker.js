// Generated by CoffeeScript 1.8.0
angular.module('ximdex.common.directive').directive('datepicker', function() {
  return {
    restrict: 'A',
    require: 'ngModel',
    link: function(scope, element, attrs, ngModelCtrl) {
      $(function() {
        element.datepicker({
          dateFormat: 'dd/mm/yy',
          onSelect: function(date) {
            scope.$apply(function() {
              ngModelCtrl.$setViewValue(date);
            });
          }
        });
      });
    }
  };
});