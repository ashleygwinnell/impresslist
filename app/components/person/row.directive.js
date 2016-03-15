angular.module("App.Person")

.directive("impressPersonRow", ['PersonModal', function(PersonModal) {
	return {
		restrict: 'A',
		templateUrl: 'app/components/person/row.template.html',
		link: function(scope, element, attrs) {
			//element.bind('click', function() {
			//	PersonModal.open( scope.person );
			//})
		}
	}
}])
