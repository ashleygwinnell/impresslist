angular.module("App.Shell")

.controller("NavController", ['$scope', '$http', '$location', 'App', 'Filters', function($scope, $http, $location, App, Filters)
{
	//$scope.App = App;
	$scope.Filters = Filters;
}])
