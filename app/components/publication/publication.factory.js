angular.module("App.Publication")

.factory("Publication", ['$rootScope', '$state', 'Model', 'Filters', function($rootScope, $state, Model, Filters) {

	function Publication(data) {
		angular.extend( this, Model );
		if (data) {
			angular.extend( this, data );
		}

		this.twitter_followers_formatted = new Number(data.twitter_followers).toLocaleString();
	}

	Publication.prototype.open = function() {
		console.log('open');
	}

	Publication.prototype.search = function(text) {
		var ret = true;

		if (text.length == 0) { return ret; }

		ret = this.name.toLowerCase().indexOf(text) != -1;
		if (ret) { return ret; }

		ret = this['notes'].toLowerCase().indexOf(text) != -1;
		if (ret) { return ret; }

		//ret = this['email'].toLowerCase().indexOf(text) != -1;
		//if (ret) { return ret; }

		return false;
	}


	Publication.prototype.visible = function() {

		if (Filters.searchText.length > 0 && !this.search(Filters.searchText)) {
			return false;
		}

		if (Filters.recentContact && Number(this['lastcontacted']) < (Date.now()/1000) - (86400*7)) {
			return false;
		}

		if (Filters.highPriority && this.priority() < 3) {
			return false;
		}

		if (Filters.emailAttached && this['email'].length == 0) {
			return false;
		}

		if (Filters.assignedSelf && this['assigned'] != $rootScope.App.user.id ) {
			return false;
		}

		if (Filters.outOfDate && this['outofdate'] == 0) {
			return false;
		}

		if (Filters.personalContact && this.lastcontactedby_me == null) {
			return false;
		}

		return true;
	}


	return Model.create( Publication );

}])


.filter("publicationArrayFilter", function() {

	return function(input) {
		var out = [];
		angular.forEach(input, function(publication) {
			if (publication.visible()) {
				out.push(publication);
			}
	    });
		return out;
	}

});




