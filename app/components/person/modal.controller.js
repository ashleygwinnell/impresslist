angular.module("App.Person")

.controller("PersonModalController", ['$scope', 'PersonModal', function($scope, PersonModal) {

	$scope.$on("PersonModal.open", function(event) {
		$scope.person = PersonModal.person;
		//$scope.$apply();
	})

	$scope.person = PersonModal.person;

}])