angular.module("App.User")

.factory("User", ['$rootScope', '$state', 'Model', function($rootScope, $state, Model) {

	function User(data) {
		if (data) {
			angular.extend( this, data );
		}
	}

	User.prototype.testFunction = function() {

	}

	User.prototype.fullname = function() {
		return this["forename"] + " " + this["surname"];
	}

	return Model.create( User );

}]);