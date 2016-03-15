angular.module("App.Filters")

.controller("FiltersController", ['$scope', '$http', '$location', 'App', 'Filters', function($scope, $http, $location, App, Filters)
{
	$scope.Filters = Filters
}]);