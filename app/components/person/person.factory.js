angular.module("App.Person")

.factory("Person", ['$rootScope', '$state', '$compile', 'Model', 'Filters', 'PersonModal', function($rootScope, $state, $compile, Model, Filters, PersonModal) {

	function Person(data) {
		angular.extend( this, Model );
		if (data) {
			angular.extend( this, data );
		}

		this.twitter_followers_formatted = new Number(data.twitter_followers).toLocaleString();
		this.modal = PersonModal;
	}

	Person.prototype.testFunction = function() {

	}




	Person.prototype.fullname = function() {
		return this["firstname"] + " " + this["surnames"];
	}

	Person.prototype.search = function(text) {
		var ret = true;

		if (text.length == 0) { return ret; }

		ret = this.fullname().toLowerCase().indexOf(text) != -1;
		if (ret) { return ret; }

		ret = this['notes'].toLowerCase().indexOf(text) != -1;
		if (ret) { return ret; }

		ret = this['email'].toLowerCase().indexOf(text) != -1;
		if (ret) { return ret; }

		// Search all publications too.
		/*var len = impresslist.personPublications.length;
		for(var i = 0; i < len; ++i) {
			if (impresslist.personPublications[i].fields['person'] == this.id) {
				var pub = impresslist.findPublicationById( impresslist.personPublications[i].fields['publication'] );
				ret = pub.search(text);
				if (ret) { return ret; }
			}
		}

		// Search all youtube channels too.
		len = impresslist.personYoutubeChannels.length;
		for(var i = 0; i < len; ++i) {
			if (impresslist.personYoutubeChannels[i].fields['person'] == this.id) {
				var pub = impresslist.findYoutuberById( impresslist.personYoutubeChannels[i].fields['youtuber'] );
				ret = pub.search(text);
				if (ret) { return ret; }
			}
		}
		*/

		return false;
	}


	Person.prototype.visible = function() {

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


	return Model.create( Person );

}])


.filter("personArrayFilter", function() {

	return function(input) {
		var out = {};
		angular.forEach(input, function(person) {
			if (person.visible()) {
				out[person.id] = person;
			}
	    });
		return out;
	}

});




