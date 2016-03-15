angular.module("App", [
	'ui.router',
	'App.Api',
	'App.Filters',
	'App.Shell',
	'App.Model',
	'App.Person',
	'App.Publication',
	'App.Priority',
	'App.User',
	'App.Views',
])

.config(['$stateProvider', '$urlRouterProvider', '$locationProvider',
		function($stateProvider, $urlRouterProvider, $locationProvider) {

	$stateProvider.state('home', { url: 'home/', templateUrl: 'app/views/index/index.template.html' });
	$stateProvider.state('login', { url: 'login/', templateUrl: 'app/views/login/login.template.html' });

	$stateProvider.state('home.people', { url: 'people/' });
	$stateProvider.state('home.publications', { url: 'publications/' });
	$stateProvider.state('home.youtubers', { url: 'youtubers/' });
	$stateProvider.state('home.chat', { url: 'chat/' });
	$stateProvider.state('home.jobs', { url: 'jobs/' });

	$stateProvider.state('home.people.person', {
		url: ':id/',
		onEnter: ['$state', '$stateParams', 'App', 'PersonModal', function($state, $stateParams, App, PersonModal) {
			var person = App.people[$stateParams.id];
			console.log(person);
			PersonModal.open(person);
		}],
		resolve: {

		}
	});

	$stateProvider.state('admin', { url: 'admin/' });

}])

.run(['$rootScope', '$log', '$state', '$timeout', 'App', function($rootScope, $log, $state, $timeout, App) {
	$rootScope.$log = $log;
	$rootScope.App = App;

	$timeout(function() {
		$rootScope.App.start();
	});
}])
/*
.directive('impressShow', function(){
	return {
		restrict: "A",
		transclude: true,
		template: '<div ng-transclude ng-show="vis"></div>',
		scope: {
			impressShow: '@'
		},
		controller: ['$scope', 'App', function($scope, App) {
			$scope.App = App;
			// $scope.vis = false;
	  //   	$scope.$watch($scope.App.parts, function(value) {
		 //    	console.log( 'link impreess show' );
		 //    	console.log( attr.impressShow );
		 //    	console.log( value );
		 //    	console.log( scope.App.parts );
		 //    	//App.parts[0] == 'home'

		 //    	$scope.vis = attr.impressShow == value[0];

		 //    });
		}],
	    link: function(scope, element, attr, ctrl) {
	    	//console.log('what');
	    	//console.log(ctrl);
	    }
	}
})*/

.filter('priority', function(){
	return function(v) {
		if (v == 0) {
			return "N/A";
		} else if (v == 1) {
			return "Low";
		} else if (v == 2) {
			return "Medium";
		} else if (v == "3") {
			return "High";
		}
		return "-";
	}
})

.filter('relativetime', function() {
	return function(v) {
		return impresslist.util.relativetime_contact(v);
	}
});
