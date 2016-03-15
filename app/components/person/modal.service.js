angular.module("App.Person")

.service("PersonModal", ['$rootScope', function($rootScope)
{
	var service = {
		person: null,
		open: function(person) {
			service.person = person;
			$rootScope.$broadcast( 'PersonModal.open', person );

			$('.person_modal').modal({show:true});
		},
		close: function() {
			$('.person_modal').modal('hide');
		},
		emailClientLink: function(email) {
			return impresslist.util.mailtoClient(defaultEmail, emailSubject, emailBody, emailBCC);
		},
		emailGmailLink: function(email) {
			return impresslist.util.mailtoGmail(defaultEmail, emailSubject, emailBody, emailBCC, emailGmailIndex);
		}
	};

	return service;
}])

.filter("emailArrayFilter", function() {
	return function(input) {
		var out = [];
		angular.forEach(input, function(email) {
			if (email.email.length > 0) {
				out.push(email);
			}
	    });
		return out;
	}
})
