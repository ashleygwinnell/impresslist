angular.module("App")

.service("App", ['$rootScope', '$state', '$location', '$stateParams', '$timeout', 'Api', 'User', 'Person', 'Publication', 'PersonModal', function($rootScope, $state, $location, $stateParams, $timeout, Api, User, Person, Publication, PersonModal) {
	var th = this;
	this.user = null;

	this.people = [];
	this.publications = [];
	this.users = [];

	this.config = {
		system: {
			email: "email"
		},
		user: {
			gmail: "gmail"
		}
	};

	this.name = "impress[]";
	this.description = "A service to help you impress people.";

	this.start = function() {
		Api.request("/auth/check/")
			.then(function(data) {

				if (data.success) {
					//if (location.hash == '') {
						$state.go('home');
					//}
					th.user = User.populate( data.user )[0];
					th.load();
				} else {
					$state.go('login');
				}
			});
	}

	this.load = function() {
		Api.request("/person/list/").then(function(data) {
			th.people = Person.populateKeyed(data.people, 'id');
		});
		Api.request("/publication/list/").then(function(data) {
			th.publications = Publication.populateKeyed(data.publications, 'id');
		});
		Api.request("/user/list/").then(function(data) {
			th.users = User.populateKeyed(data.users, 'id');
		});
	}

	this.parts = [''];
	this.part = function(index) {
		return th.parts[index] || false;
	}
	this.updateParts = function(str) {
		//console.log(str);
		th.parts = str.split('.');
		//console.log('Updating URL parts: ' + th.parts.join(' - '));
	}
	$rootScope.$on('$stateChangeStart', function(event, toState, toParams, fromState, fromParams, options){
		th.updateParts( toState.name );
	});

}]);
