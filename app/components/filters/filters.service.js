angular.module("App.Filters")

.service("Filters", ['$rootScope', '$state', function($rootScope, $state) {

	this.searchText = '';

	this.recentContact = false;
	this.highPriority = false;
	this.personalContact = false;
	this.emailAttached = false;
	this.assignedSelf = false;
	this.outOfDate = false;

}]);