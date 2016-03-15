angular.module("App.Priority").directive("impressPriorityName", function() {
 	return {
 		restrict: "A",
 		scope: {
 			priority: "@"
 		},
 		controller: ['$scope', '$element', function($scope, $element) {
 			$scope.name = $element.attr('priority');
 		}],
 		/*link: function(scope, iElement, iAttrs) {

	      iAttrs.$observe('priority', function(v) {

	        var sv = "";
	      	if (v == 0) {
				sv = "N/A";
			} else if (v == 1) {
				sv = "Low";
			} else if (v == 2) {
				sv = "Medium";
			} else if (v == "3") {
				sv = "High";
			}
	        scope.val = sv;
	        console.log(sv);
	      });
	    },*/
 		template: "<td>{{ $scope.name }}</td>"
 	}
})
