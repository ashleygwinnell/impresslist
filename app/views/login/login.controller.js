angular.module("App.Views")

.controller("LoginController", ['$scope', '$state', 'App', 'Api', 'User', function($scope, $state, App, Api, User)
{
	$scope.App = App;
	$scope.Api = Api;

	$scope.errors = [];

	$scope.login = function(email, password) {
		Api.request("/auth/login/", {email: email, password: password})
			.then(function(data) {
				//console.log(data);
				//console.log(data.data.success);
				App.user = User.populate( data.data.user )[0];
				App.load();
				console.log(App.user);
				$state.go('home');

			});
	}
}])
