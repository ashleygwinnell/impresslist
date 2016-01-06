API = function() {

}
API.prototype = {

}

API.listSocialTimeline = function(fromInit) {
	if (typeof fromInit == 'undefined') { fromInit = true; }
	
	impresslist.loading.set('socialTimelineItem', true); 
	var url = "api.php?endpoint=/social/timeline/";
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			}
			
			for(var i = 0; i < json.timeline.length; ++i) { 
				var item = new SocialTimelineItem(json.timeline[i]);
				impresslist.addSocialTimelineItem(item, fromInit);
			}
			impresslist.loading.set('socialTimelineItem', false); 
		})
		.fail(function() {
			API.errorMessage("Could not pull Social Timeline.");
		});
}
API.addSocialTimelineItem = function() {
	var url = "api.php?endpoint=/social/timeline/item/add/";
	console.log(url);
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			}
			API.successMessage("Schedule Item added.");
			console.log(json);

			var item = new SocialTimelineItem(json.socialTimelineItem);
			impresslist.addSocialTimelineItem(item, false);
		})
		.fail(function() {
			API.errorMessage("Could not add Schedule Item.");
		});
}
API.addSocialTimelineItemRetweets = function(obj, accounts, timesep, donecallback) {
	var url = "api.php?endpoint=/social/timeline/item/add-retweets/" +
				"&id=" + encodeURIComponent(obj.id) + 
				"&accounts=" + encodeURIComponent(accounts) + 
				"&timesep=" + encodeURIComponent(timesep);
	console.log(url);
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			}
			API.successMessage("Shares added.");
			console.log(json);

			for(var i = 0; i < json.socialTimelineItems.length; ++i) {
				var item = new SocialTimelineItem(json.socialTimelineItems[i]);
				impresslist.addSocialTimelineItem(item, false);	
			}

			if (typeof donecallback != 'undefined') {
				donecallback();
			}
			
		})
		.fail(function() {
			API.errorMessage("Could not add Shares.");
		});
}
API.saveSocialTimelineItem = function(obj, type, typedata, timestamp, ready) {
	var url = "api.php?endpoint=/social/timeline/item/save/" + 
					"&id=" + encodeURIComponent(obj.id) + 
					"&type=" + encodeURIComponent(type) + 
					"&data=" + encodeURIComponent(JSON.stringify(typedata)) + 
					"&timestamp=" + encodeURIComponent(timestamp) + 
					"&ready=" + encodeURIComponent(ready);
	console.log(url);

	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return; 
			}
			API.successMessage("Schedule Item saved.");
			console.log(json);
			obj.init(json.socialTimelineItem);
			obj.updateRow();
			obj.update();
			SocialTimelineItem.displaySort();
		})
		.fail(function() {
			API.errorMessage("Could not save Schedule Item.");
		});
}
API.removeSocialTimelineItem = function(obj) {
	var url = "api.php?endpoint=/social/timeline/item/remove/&id=" + encodeURIComponent(obj.id);
	console.log(url);
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			}
			API.successMessage("Schedule Item removed.");
			impresslist.removeSocialTimelineItem(obj);
		})
		.fail(function() {
			API.errorMessage("Could not remove Schedule Item.");
		});
}
API.listSocialUploads = function(fromInit) {
	if (typeof fromInit == 'undefined') { fromInit = true; }
	
	impresslist.loading.set('socialUploads', true); 
	var url = "api.php?endpoint=/social/uploads/list/";
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			}
			
			for(var i = 0; i < json.uploads.length; ++i) { 
				var upload = new SocialUpload(json.uploads[i]);
				impresslist.addSocialUpload(upload, fromInit);
			}
			impresslist.loading.set('socialUploads', false); 
		})
		.fail(function() {
			API.errorMessage("Could not list Social Uploads.");
		});
}
API.addSocialUpload = function(d, successCallback) {
	var url = "api.php?endpoint=/social/uploads/add/";
	console.log(url);
	
	$.ajax({
		url: "api.php?endpoint=/social/uploads/add/", 
		type: "POST",             
		data: d,
		contentType: false, 
		cache: false,             
		processData: false,
		success: function(result)   
		{
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			}
			API.successMessage("Social Upload added.");
			console.log(json);

			var upload = new SocialUpload(json.upload);
			impresslist.addSocialUpload(upload, false);

			if (typeof successCallback != 'undefined') {
				successCallback();
			}
		},
		error: function(e, e2, e3) 
		{
			API.errorMessage("Could not add Social Upload.");
			console.log("error: " + e + " " + e2 + " " + e3);
		}
	});
}
API.removeSocialUpload = function(acc) {
	var url = "api.php?endpoint=/social/uploads/remove/&name=" + encodeURIComponent(acc.field("name"));
	console.log(url);
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { API.errorMessage(result); return; }
			
			var json = JSON.parse(result);
			if (!json.success) { API.errorMessage(json.message); return; }
			API.successMessage("Social Upload removed.");
			impresslist.removeSocialUpload(acc);
			
		})
		.fail(function() {
			API.errorMessage("Could not remove Social Upload.");
		});
}

API.listPeople = function(fromInit) {
	if (typeof fromInit == 'undefined') { fromInit = true; }
	
	var url = "api.php?endpoint=/person/list/";
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			}
			
			for(var i = 0; i < json.people.length; ++i) { 
				var person = new Person(json.people[i]);
				impresslist.addPerson(person, fromInit);
			}
			if (fromInit) { impresslist.refreshFilter(); }
		})
		.fail(function() {
			API.errorMessage("Could not list People.");
		});
}
API.listPublications = function(fromInit) {
	if (typeof fromInit == 'undefined') { fromInit = true; }

	var url = "api.php?endpoint=/publication/list/";
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			}
			
			for(var i = 0; i < json.publications.length; ++i) { 
				var publication = new Publication(json.publications[i]);
				impresslist.addPublication(publication, fromInit);
			}
			if (fromInit) { 
				impresslist.refreshFilter(); 
			}
		})
		.fail(function() {
			API.errorMessage("Could not list Publications.");
		});
}
API.listPersonPublications = function(fromInit) {
	if (typeof fromInit == 'undefined') { fromInit = true; }

	var url = "api.php?endpoint=/person-publication/list/";
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			}
			for(var i = 0; i < json.personPublications.length; ++i) { 
				var publication = new PersonPublication(json.personPublications[i]);
				impresslist.addPersonPublication(publication, fromInit);
			}
			if (fromInit) { impresslist.refreshFilter(); }
		})
		.fail(function() {
			API.errorMessage("Could not list Person Publications.");
		});
}
API.listPersonYoutubeChannels = function(fromInit) {
	if (typeof fromInit == 'undefined') { fromInit = true; }

	var url = "api.php?endpoint=/person-youtube-channel/list/";
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			}
			for(var i = 0; i < json.personYoutubeChannels.length; ++i) { 
				var channel = new PersonYoutubeChannel(json.personYoutubeChannels[i]);
				impresslist.addPersonYoutubeChannel(channel, fromInit);
			}
			if (fromInit) { impresslist.refreshFilter(); }
		})
		.fail(function() {
			API.errorMessage("Could not list Person Youtube Channels.");
		});
}
API.listYoutubeChannels = function(fromInit) {
	if (typeof fromInit == 'undefined') { fromInit = true; }

	impresslist.loading.set('youtubeChannels', true); 
	var url = "api.php?endpoint=/youtuber/list/";
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			}
			for(var i = 0; i < json.youtubechannels.length; ++i) { 
				var youtuber = new Youtuber(json.youtubechannels[i]);
				impresslist.addYoutuber(youtuber, fromInit);
			}
			if (fromInit) { 
				impresslist.refreshFilter(); 
				API.listCoverage(fromInit); 
			}
			impresslist.loading.set('youtubeChannels', false); 
		})
		.fail(function() {
			API.errorMessage("Could not list Youtubers.");
		});
}
API.listEmails = function(fromInit) {
	if (typeof fromInit == 'undefined') { fromInit = true; }

	var url = "api.php?endpoint=/email/list/";
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			}
			for(var i = 0; i < json.emails.length; ++i) { 
				var email = new Email(json.emails[i]);
				impresslist.addEmail(email, fromInit);
			}
			if (fromInit) { impresslist.refreshFilter(); }
		})
		.fail(function() {
			API.errorMessage("Could not list Emails.");
		});
}
API.listCoverage = function(fromInit) {
	if (typeof fromInit == 'undefined') { fromInit = true; }

	impresslist.loading.set('coverage', true); 
	var url = "api.php?endpoint=/coverage/";
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			}
			for(var i = 0; i < json.coverage.length; ++i) { 
				var coverage = new Coverage(json.coverage[i]);
				impresslist.addCoverage(coverage, fromInit);
			}
			if (fromInit) { impresslist.refreshFilter(); }
			impresslist.loading.set('coverage', false); 
		})
		.fail(function() {
			API.errorMessage("Could not list Emails.");
		});
}
API.listKeys = function(game, platform, assigned, callbackfunction) {
	var url = "api.php?endpoint=/keys/list/" +
				"&game=" + encodeURIComponent(game) + 
				"&platform=" + encodeURIComponent(platform) +
				"&assigned=" + encodeURIComponent(assigned);
	console.log(url);

	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { API.errorMessage(result); return; }
			
			var json = JSON.parse(result);
			if (!json.success) { API.errorMessage(json.message); return; }
			
			callbackfunction(json);
		})
		.fail(function() {
			API.errorMessage("Could not list keys.");
		});
}
API.addKeys = function(keys, game, platform, expiresOn, callbackfunction) {
	var url = "api.php?endpoint=/keys/add/" +
					"&keys=" + encodeURIComponent(keys) + 
					"&game=" + encodeURIComponent(game) + 
					"&platform=" + encodeURIComponent(platform) + 
					"&expiresOn=" + encodeURIComponent(expiresOn);
	console.log(url);
	
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			}
			API.successMessage("Keys added.");
			console.log(json);

			callbackfunction(json);

			//var simpleMailout = new SimpleMailout(json.mailout);
			//impresslist.addSimpleMailout(simpleMailout, false);
		})
		.fail(function() {
			API.errorMessage("Could not add Keys.");
		});
}

API.listSimpleMailouts = function(fromInit) {
	if (typeof fromInit == 'undefined') { fromInit = true; }
	
	var url = "api.php?endpoint=/mailout/simple/list/";
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			}
			
			for(var i = 0; i < json.mailouts.length; ++i) { 
				var mailout = new SimpleMailout(json.mailouts[i]);
				impresslist.addSimpleMailout(mailout, fromInit);
			}
			
		})
		.fail(function() {
			API.errorMessage("Could not list Mailouts.");
		});
}
API.addSimpleMailout = function() {
	var url = "api.php?endpoint=/mailout/simple/add/";
	console.log(url);
	
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			}
			API.successMessage("Simple Mailout added.");
			console.log(json);

			var simpleMailout = new SimpleMailout(json.mailout);
			impresslist.addSimpleMailout(simpleMailout, false);
		})
		.fail(function() {
			API.errorMessage("Could not add Simple Mailout.");
		});
}
API.saveSimpleMailout = function(obj, name, subject, recipients, markdown, timestamp, callbackfunction) {
	
	var url = "api.php?endpoint=/mailout/simple/save/" + 
					"&id=" + encodeURIComponent(obj.id) + 
					"&name=" + encodeURIComponent(name) + 
					"&subject=" + encodeURIComponent(subject) + 
					//"&recipients=" + encodeURIComponent( JSON.stringify(recipients) ) + 
					"&recipients=look_in_post_data" + 
					"&markdown=" + encodeURIComponent(markdown) + 
					"&timestamp=" + encodeURIComponent(timestamp);
	console.log(url);

	$.ajax( {
		method: 'POST',
		url: url,
		data: { recipients: encodeURIComponent( JSON.stringify(recipients) ) }
	})
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return; 
			}
			API.successMessage("Simple Mailout saved.");
			obj.init(json.mailout);
			obj.update();
			callbackfunction();
		})
		.fail(function() {
			API.errorMessage("Could not save Simple Mailout.");
		});
}
API.sendSimpleMailout = function(obj) {
	var url = "api.php?endpoint=/mailout/simple/send/" + 
					"&id=" + encodeURIComponent(obj.id);
	console.log(url);

	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return; 
			}
			API.successMessage("Simple Mailout sent!");
			obj.init(json.mailout);
			obj.update();
		})
		.fail(function() {
			API.errorMessage("Could not send Simple Mailout.");
		});
}
API.cancelSimpleMailout = function(obj) {
	var url = "api.php?endpoint=/mailout/simple/cancel/" + 
					"&id=" + encodeURIComponent(obj.id);
	console.log(url);

	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return; 
			}
			API.successMessage("Simple Mailout cancelled send!");
			obj.init(json.mailout);
			obj.update();
		})
		.fail(function() {
			API.errorMessage("Could not cancel send of Simple Mailout.");
		});
}
API.removeSimpleMailout = function(simpleMailout) {
	var url = "api.php?endpoint=/mailout/simple/remove/&id=" + encodeURIComponent(simpleMailout.id);
	console.log(url);
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			}
			API.successMessage("Simple Mailout removed.");
			impresslist.removeSimpleMailout(simpleMailout);
		})
		.fail(function() {
			API.errorMessage("Could not remove Simple Mailout.");
		});
}

API.addPerson = function() {
	var firstname = "Blank";
	var surnames = "Surname";
	var email = "blank@blank.com";
	var twitter = "";
	var notes = "";
	var url = "api.php?endpoint=/person/add/&firstname=" + encodeURIComponent(firstname) + "&surnames=" + encodeURIComponent(surnames) + "&email=" + encodeURIComponent(email) + "&twitter=" + encodeURIComponent(twitter) + "&notes=" + encodeURIComponent(notes);
	console.log(url);
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			}
			API.successMessage("Person added.");
			console.log(json);

			var person = new Person(json.person);
			impresslist.addPerson(person, false);
			$(person.openSelector()).click();
		})
		.fail(function() {
			API.errorMessage("Could not add Person.");
		});
}
API.addPersonPublication = function(personObj, publicationId) {
	var url = "api.php?endpoint=/person/add-publication/" +
				"&person=" + encodeURIComponent(personObj.id) + 
				"&publication=" + encodeURIComponent(publicationId);
	console.log(url);
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			}
			API.successMessage("Person publication added.");
			console.log(json);

			impresslist.addPersonPublication(new PersonPublication(json.personPublication), false);			
		})
		.fail(function() {
			API.errorMessage("Could not add Person.");
		});
}
API.addPersonYoutubeChannel = function(personObj, youtubeChannelId) {
	var url = "api.php?endpoint=/person/add-youtube-channel/" +
				"&person=" + encodeURIComponent(personObj.id) + 
				"&youtubeChannel=" + encodeURIComponent(youtubeChannelId);
	console.log(url);
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			}
			API.successMessage("Person Youtube Channel added.");
			console.log(json);

			impresslist.addPersonYoutubeChannel(new PersonYoutubeChannel(json.personYoutubeChannel), false);			
		})
		.fail(function() {
			API.errorMessage("Could not add Person - Youtube Channel.");
		});
}
API.removePersonYoutubeChannel = function(personYoutuberObj) {
	var url = "api.php?endpoint=/person/remove-youtube-channel/" +
				"&personYoutubeChannel=" + encodeURIComponent(personYoutuberObj.id);
	console.log(url);
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			}
			API.successMessage("Person Youtube Channel removed.");
			console.log(json);

			impresslist.removePersonYoutubeChannel(personYoutuberObj);			
		})
		.fail(function() {
			API.errorMessage("Could not removed Person Youtube Channel.");
		});
}
API.savePersonPublication = function(personPublicationObj, email) {
	var url = "api.php?endpoint=/person/save-publication/" +
				"&personPublication=" + encodeURIComponent(personPublicationObj.id) + 
				"&email=" + encodeURIComponent(email);
	console.log(url);
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			}
			API.successMessage("Person publication saved.");
			console.log(json);

			personPublicationObj.init(json.personPublication);
			personPublicationObj.update();
		})
		.fail(function() {
			API.errorMessage("Could not save Person publication.");
		});
}
API.removePersonPublication = function(personPublicationObj) {
	var url = "api.php?endpoint=/person/remove-publication/" +
				"&personPublication=" + encodeURIComponent(personPublicationObj.id);
	console.log(url);
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			}
			API.successMessage("Person publication removed.");
			console.log(json);

			impresslist.removePersonPublication(personPublicationObj);			
		})
		.fail(function() {
			API.errorMessage("Could not removed Person.");
		});
}
API.addPublicationCoverage = function() {
	var url = "api.php?endpoint=/coverage/publication/add/";
	console.log(url);
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			}
			API.successMessage("Coverage added.");
			console.log(json);

			var coverage = new Coverage(json.coverage);
			impresslist.addCoverage(coverage, false);
		})
		.fail(function() {
			API.errorMessage("Could not add Coverage.");
		});
}
API.savePublicationCoverage = function(coverage, publication, person, title, url, timestamp, thanked) {
	var url = "api.php?endpoint=/coverage/publication/save/" +
						"&id=" + encodeURIComponent(coverage.id) + 
						"&publication=" + encodeURIComponent(publication) + 
						"&person=" + encodeURIComponent(person) + 
						"&title=" + encodeURIComponent(title) + 
						"&url=" + encodeURIComponent(url) + 
						"&timestamp=" + encodeURIComponent(timestamp) + 
						"&thanked=" + encodeURIComponent(thanked);
	console.log(url);
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			}
			API.successMessage("Coverage saved.");
			console.log(json);

			coverage.init(json.coverage);
			coverage.update();		
		})
		.fail(function() {
			API.errorMessage("Could not add Coverage.");
		});
}
API.removePublicationCoverage = function(coverage) {
	var url = "api.php?endpoint=/coverage/publication/remove/&id=" + encodeURIComponent(coverage.id);
	console.log(url);
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			}
			API.successMessage("Coverage removed.");
			console.log(json);
			impresslist.removeCoverage(coverage);
		})
		.fail(function() {
			API.errorMessage("Could not remove Coverage.");
		});
}
API.addYoutuberCoverage = function() {
	var url = "api.php?endpoint=/coverage/youtuber/add/";
	console.log(url);
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			}
			API.successMessage("Coverage added.");
			console.log(json);

			var coverage = new Coverage(json.coverage);
			impresslist.addCoverage(coverage, false);
		})
		.fail(function() {
			API.errorMessage("Could not add Coverage.");
		});
}
API.saveYoutuberCoverage = function(coverage, youtuber, person, title, url, timestamp, thanked) {
	var url = "api.php?endpoint=/coverage/youtuber/save/" +
						"&id=" + encodeURIComponent(coverage.id) + 
						"&youtuber=" + encodeURIComponent(youtuber) + 
						"&person=" + encodeURIComponent(person) + 
						"&title=" + encodeURIComponent(title) + 
						"&url=" + encodeURIComponent(url) + 
						"&timestamp=" + encodeURIComponent(timestamp) + 
						"&thanked=" + encodeURIComponent(thanked);
	console.log(url);
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			}
			API.successMessage("Coverage saved.");
			console.log(json);

			coverage.init(json.coverage);
			coverage.update();		
		})
		.fail(function() {
			API.errorMessage("Could not add Coverage.");
		});
}
API.removeYoutuberCoverage = function(coverage) {
	var url = "api.php?endpoint=/coverage/youtuber/remove/&id=" + encodeURIComponent(coverage.id);
	console.log(url);
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			}
			API.successMessage("Coverage removed.");
			console.log(json);
			impresslist.removeCoverage(coverage);
		})
		.fail(function() {
			API.errorMessage("Could not remove Coverage.");
		});
}
API.setPersonPriority = function(person, priority, gameId) {
	var url = "api.php?endpoint=/person/set-priority/" + 
					"&id=" + encodeURIComponent(person.id) + 
					"&priority=" + encodeURIComponent(priority) + 
					"&game=" + encodeURIComponent(gameId);
	console.log(url); 
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				//alert(json.message)
				API.errorMessage(json.message);
				return; 
			}
			API.successMessage("Person priority set.");
			person.init(json.person);
			person.update();
		})
		.fail(function() {
			API.errorMessage("Could not set priority on Person.");
		});
}
API.setPersonAssignment = function(person, user, gameId) {
	var url = "api.php?endpoint=/person/set-assignment/" + 
					"&id=" + encodeURIComponent(person.id) + 
					"&user=" + encodeURIComponent(user);
	console.log(url); 
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				//alert(json.message)
				API.errorMessage(json.message);
				return; 
			}
			API.successMessage("Person user-assignment set.");
			person.init(json.person);
			person.update();
		})
		.fail(function() {
			API.errorMessage("Could not set user-assignment on Person.");
		});
}
API.savePerson = function(person, firstname, surnames, email, twitter, notes, outofdate) {

	var url = "api.php?endpoint=/person/save/" + 
					"&id=" + encodeURIComponent(person.id) + 
					"&firstname=" + encodeURIComponent(firstname) + 
					"&surnames=" + encodeURIComponent(surnames) + 
					"&email=" + encodeURIComponent(email) + 
					"&twitter=" + encodeURIComponent(twitter) + 
					"&notes=" + encodeURIComponent(notes) + 
					"&outofdate=" + encodeURIComponent(outofdate);
	console.log(url);
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				//alert(json.message)
				API.errorMessage(json.message);
				return; 
			}
			API.successMessage("Person saved.");
			person.init(json.person);
			person.update();
		})
		.fail(function() {
			API.errorMessage("Could not save Person.");
		});
}
API.removePerson = function(person) {
	var url = "api.php?endpoint=/person/remove/&id=" + encodeURIComponent(person.id);
	console.log(url);
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			}
			API.successMessage("Person removed.");
			impresslist.removePerson(person);
		})
		.fail(function() {
			API.errorMessage("Could not remove Person.");
		});
}
API.addPublication = function() {
	var name = "Blank";
	var url = "api.php?endpoint=/publication/add/&name=" + encodeURIComponent(name);
	console.log(url);
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			}
			API.successMessage("Publication added.");
			console.log(json);

			var publication = new Publication(json.publication);
			impresslist.addPublication(publication, false);
			$(publication.openSelector()).click();
		})
		.fail(function() {
			API.errorMessage("Could not add Publication.");
		});
}
API.setPublicationPriority = function(publication, priority, gameId) {
	var url = "api.php?endpoint=/publication/set-priority/" + 
					"&id=" + encodeURIComponent(publication.id) + 
					"&priority=" + encodeURIComponent(priority) + 
					"&game=" + encodeURIComponent(gameId);
	console.log(url); 
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				//alert(json.message)
				API.errorMessage(json.message);
				return; 
			}
			API.successMessage("Publication priority set.");
			publication.init(json.publication);
			publication.update();
		})
		.fail(function() {
			API.errorMessage("Could not set priority on Publication.");
		});
}
API.savePublication = function(publication, name, url, rssfeedurl, twitter, notes) {

	var url = "api.php?endpoint=/publication/save/" + 
					"&id=" + encodeURIComponent(publication.id) + 
					"&name=" + encodeURIComponent(name) + 
					"&url=" + encodeURIComponent(url) + 
					"&rssfeedurl=" + encodeURIComponent(rssfeedurl) + 
					"&twitter=" + encodeURIComponent(twitter) + 
					"&notes=" + encodeURIComponent(notes);
	console.log(url);
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return; 
			}
			console.log(json);

			API.successMessage("Publication saved.");
			publication.init(json.publication);
			publication.update();
		})
		.fail(function() {
			API.errorMessage("Could not save Publication.");
		});
}
API.removePublication = function(publication) {
	var url = "api.php?endpoint=/publication/remove/&id=" + encodeURIComponent(publication.id);
	console.log(url);
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			}
			API.successMessage("Publication removed.");
			impresslist.removePublication(publication);
		})
		.fail(function() {
			API.errorMessage("Could not remove Publication.");
		});
}

API.addYoutuber = function() {
	var name = "Blank";
	var url = "api.php?endpoint=/youtuber/add/&channel=youtube";
	console.log(url);
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			}
			API.successMessage("Youtuber added.");
			console.log(json);

			var youtuber = new Youtuber(json.youtubechannel);
			impresslist.addYoutuber(youtuber, false);
			$(youtuber.openSelector()).click();
		})
		.fail(function() {
			API.errorMessage("Could not add Youtuber.");
		});
}
API.setYoutuberPriority = function(youtuber, priority, gameId) {
	var url = "api.php?endpoint=/youtuber/set-priority/" + 
					"&id=" + encodeURIComponent(youtuber.id) + 
					"&priority=" + encodeURIComponent(priority) + 
					"&game=" + encodeURIComponent(gameId);
	console.log(url); 
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return; 
			}
			console.log(json.youtubechannel);
			API.successMessage("Youtuber priority set.");
			youtuber.init(json.youtubechannel);
			youtuber.update();
		})
		.fail(function() {
			API.errorMessage("Could not set priority on Youtuber.");
		});
}
API.saveYoutuber = function(youtuber, channel, email, twitter, notes) {

	var url = "api.php?endpoint=/youtuber/save/" + 
					"&id=" + encodeURIComponent(youtuber.id) + 
					"&channel=" + encodeURIComponent(channel) + 
					"&email=" + encodeURIComponent(email) + 
					"&twitter=" + encodeURIComponent(twitter) + 
					"&notes=" + encodeURIComponent(notes);
	console.log(url);
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return; 
			}
			console.log(json);

			API.successMessage("Youtuber saved.");
			youtuber.init(json.youtubechannel);
			youtuber.update();
		})
		.fail(function() {
			API.errorMessage("Could not save Youtuber.");
		});
}
API.removeYoutuber = function(youtuber) {
	var url = "api.php?endpoint=/youtuber/remove/&id=" + encodeURIComponent(youtuber.id);
	console.log(url);
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			}
			API.successMessage("Youtuber removed.");
			impresslist.removeYoutuber(youtuber);
		})
		.fail(function() {
			API.errorMessage("Could not remove Youtuber.");
		});
}
API.userChangeIMAPSettings = function(user, smtpServer, imapServer, imapPassword) {
	var url = "api.php?endpoint=/user/change-imap-settings/";
	url += "&id=" + encodeURIComponent(user.id);
	url += "&smtpServer=" + encodeURIComponent(smtpServer);
	url += "&imapServer=" + encodeURIComponent(imapServer);
	url += "&imapPassword=" + encodeURIComponent(imapPassword);
	console.log(url);
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			} 
			API.successMessage("IMAP settings changed.");
		})
		.fail(function() {
			API.errorMessage("Could not change IMAP settings.");
		});
}
API.userChangePassword = function(user, currentPassword, newPassword) {
	var url = "api.php?endpoint=/user/change-password/&id=" + encodeURIComponent(user.id) + "&currentPassword=" + encodeURIComponent(currentPassword) + "&newPassword=" + encodeURIComponent(newPassword);
	console.log(url);
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			} 
			API.successMessage("Password changed.");
		})
		.fail(function() {
			API.errorMessage("Could not change Password.");
		});
}
API.listOAuthTwitterAccounts = function(fromInit) {
	if (typeof fromInit == 'undefined') { fromInit = true; }
	
	var url = "api.php?endpoint=/social/account/twitter/list/";
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { API.errorMessage(result); return; }
			var json = JSON.parse(result);
			if (!json.success) { API.errorMessage(json.message); return; }
			
			for(var i = 0; i < json.twitteraccs.length; ++i) { 
				var twacc = new OAuthTwitterAccount(json.twitteraccs[i]);
				impresslist.addOAuthTwitterAccount(twacc, fromInit);
			}

			$('#social-homepage-twitteracc-list-loading').hide();
			if (json.twitteraccs.length == 0) {
				$('#social-homepage-twitteracc-list-none').show();
			}
			
		})
		.fail(function() {
			API.errorMessage("Could not list OAuth Twitter Accounts.");
		});
}
API.addOAuthTwitterAccount = function(request_token, request_token_secret, pin) {
	var url = "api.php?endpoint=/social/account/twitter/add/" + 
				"&request_token=" + encodeURIComponent(request_token) +
				"&request_token_secret=" + encodeURIComponent(request_token_secret) +
				"&pin=" + encodeURIComponent(pin);
	console.log(url);
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { API.errorMessage(result); return; }
			
			var json = JSON.parse(result);
			if (!json.success) { API.errorMessage(json.message); return; }
			API.successMessage("Twitter Account added.");
			console.log(json);

			var oauthtwitter = new OAuthTwitterAccount(json.twitteracc);
			impresslist.addOAuthTwitterAccount(oauthtwitter, false);
		})
		.fail(function() {
			API.errorMessage("Could not add Twitter Account.");
		});
}
API.removeOAuthTwitterAccount = function(acc) {
	var url = "api.php?endpoint=/social/account/twitter/remove/&id=" + encodeURIComponent(acc.id);
	console.log(url);
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { API.errorMessage(result); return; }
			
			var json = JSON.parse(result);
			if (!json.success) { API.errorMessage(json.message); return; }
			API.successMessage("Twitter Account removed.");
			impresslist.removeOAuthTwitterAccount(acc);
			
		})
		.fail(function() {
			API.errorMessage("Could not remove Twitter Account.");
		});
}
API.sqlQuery = function(query) {
	var url = "api.php?endpoint=/admin/sql-query/&query=" + encodeURIComponent(query);
	console.log(url);
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { 
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			} 
			API.successMessage("Query successful.");
			
		})
		.fail(function() {
			API.errorMessage("Could not execute query.");
		});
}

API.successMessage = function(message) {
	$.bootstrapGrowl(message, { type: 'success',  offset: {from: 'top', amount: 70}, align:'center', delay: 2000});
}
API.errorMessage = function(message) {
	$.bootstrapGrowl(message, { type: 'danger',  offset: {from: 'top', amount: 70}, align:'center', delay: 10000});
}


Priority = {};
Priority.name = function(v) {
	if (v == 0) {
		return "N/A";
	} else if (v == 1) {
		return "Low";
	} else if (v == 2) {
		return "Medium";
	} else if (v == "3") {
		return "High";
	}
}

DBO = function(data) { 
	this.init(data);
} 
	DBO.prototype.constructor = DBO;
	DBO.prototype.init = function(data) {
	 	this.fields = data;
	}
	DBO.prototype.initPriorities = function(field) { 
		// Priorities: 
		// 1=3,2=1
		//console.log(this.field('priorities'));
		var txt = this.field(field);
		if (typeof txt == 'undefined' || txt.length == 0) { 
			this['priority_' + impresslist.config.user.game] = 0;
			return; 
		}
		var pris = txt.split(",");
		for(var i = 0; i < pris.length; ++i) {
			var bits = pris[i].split("=");
			var game_id = bits[0];
			var game_priority = bits[1];
			this['priority_' + game_id] = game_priority;
		}
	}
	DBO.prototype.priority = function() {
		var ret = this['priority_' + impresslist.config.user.game];
		if (typeof ret == 'undefined') {
			return 0;
		}
		return ret;
	}

	DBO.prototype.onAdded = function() {

	}
	DBO.prototype.onRemoved = function() {
		
	}
	DBO.prototype.field = function(f) {
		var r = this.fields[f];
		if (r == null) { console.error("field " + f + " was null."); console.error(this.fields); return ""; }
		return r;
	}
	DBO.prototype.twitterCell = function() {
		var str = "N/A";
		if (this.fields['twitter'].length > 0) { 
			str = "<a href='http://twitter.com/" + this.fields['twitter'] + "' target='new'>" + new Number(this.fields['twitter_followers']).toLocaleString() + "</a>";
		}
		return str;
	}


Email = function(data) {
	DBO.call(this, data); 
}
	Email.prototype = Object.create(DBO.prototype)
	Email.prototype.constructor = Email;
	Email.prototype.init = function(data) {
		DBO.prototype.init.call(this, data);
		this.id = parseInt(this.field('id'));
		//this.user_id = this.field('user_id');
		//this.person_id = this.field('person_id');
	}



Coverage = function(data) {
	DBO.call(this, data); 
}
	Coverage.prototype = Object.create(DBO.prototype)
	Coverage.prototype.constructor = Email;
	Coverage.prototype.init = function(data) {
		DBO.prototype.init.call(this, data);
		this.id = parseInt(this.field('id'));
	}
	Coverage.prototype.filter = function(text) {
		var element = $("#coverage [data-coverage-id='" + this.id + "']");
		if (this.search(text)) {
			element.show();
			return true;
		} else {
			element.hide();
			return false;
		}
	}
	Coverage.prototype.search = function(text) {
		var ret = true;

		if (text.length == 0) { return ret; }

		ret = this.field('url').toLowerCase().indexOf(text) != -1;
		if (ret) { return ret; }

		if (this.field('type') == "publication") { 
			// Search all publications too.
			for(var i = 0; i < impresslist.publications.length; ++i) {
				if (impresslist.publications[i].field('id') == this.field('publication')) {
					var pub = impresslist.findPublicationById( this.field('publication') );
					ret = pub.search(text);
					if (ret) { return ret; }
				}
			}
		} else if (this.field('type') == "youtuber") {
			// Search all YOuTubers too.
			for(var i = 0; i < impresslist.youtubers.length; ++i) {
				if (impresslist.youtubers[i].field('id') == this.field('youtuber')) {
					var pub = impresslist.findYoutuberById( this.field('youtuber') );
					ret = pub.search(text);
					if (ret) { return ret; }
				}
			}
		}

		return false;
	}
	Coverage.prototype.createItem = function(fromInit) {
		var url = ""; 
		var iconurl = "images/favicon.png"; 
		//var pubname = "Unknown Publication";

		if ("publication" in this.fields && this.fields['publication'] > 0) { 

			var publication = impresslist.findPublicationById( this.field('publication') );
			
			if (publication != null) {
				url = publication.field('url');;
				iconurl = publication.field('iconurl');;
			//	pubname = publication.name;
			} 
		}
		
		var html;
		var type = this.field('type');
		if (type == "publication") { 
			html = "		<div data-coverage-id='" + this.field('id') + "' data-coverage-type='" + this.field('type') + "' class='media'>	\
								<div class='media-left' style='min-width:74px; width:74px;'> \
									<a href='" + url + "' style='text-align:right;'><img class='media-object fr' style='width:16px;text-align:right;' src='" + iconurl + "' alt='Image'></a> \
								</div> \
								<div class='media-body'> \
									<div class='fr' style='text-align:right;'>\
										<p style='margin-bottom:5px;font-style:italic;'><span data-coverage-id='" + this.id + "' data-person-id='" + this.fields['person'] + "' data-field='person-name'></span> - <span data-coverage-id='" + this.id + "' data-field='utime' >" + impresslist.util.relativetime_contact(this.field('utime')) + "</span></p>\
										<p data-coverage-id='" + this.id + "' data-field='thanked'></p>\
									</div> \
									<h4 data-coverage-id='" + this.id + "' data-field='name' data-publication-id='" + this.fields['publication'] + "' class='media-heading' ></h4> \
									<p><a data-coverage-id='" + this.id + "' data-field='url' href='" + this.field('url') + "' target='new'>" + this.field('title') + "</a><br/>\
								</div> \
								<div class='media-right'> \
									<button id='edit-coverage' class='btn btn-default btn-lg' data-coverage-id='" + this.field('id') + "'  data-toggle='modal' data-target='.coverage_modal' ><span class='glyphicon glyphicon-pencil'></span></button> \
								</div> \
							</div>";
		} else if (type == "youtuber") {
			//var youtuber = impresslist.findYoutuberById( this.field('youtuber') );
			iconurl = this.field('thumbnail');
			//url = youtuber.field('url');;

			html = "		<div data-youtube-coverage-id='" + this.field('id') + "' data-coverage-type='" + this.field('type') + "' class='media'>	\
								<div class='media-left'> \
									<a href='" + url + "'><img data-youtube-coverage-id='" + this.id + "' data-field='thumbnail' class='media-object' width=64 src='" + iconurl + "' alt='Image'></a> \
								</div> \
								<div class='media-body'> \
									<div class='fr' style='text-align:right;'>\
										<p style='margin-bottom:5px;font-style:italic;'><span data-youtube-coverage-id='" + this.id + "' data-field='utime' >" + impresslist.util.relativetime_contact(this.field('utime')) + "</span></p>\
										<p data-youtube-coverage-id='" + this.id + "' data-field='thanked'></p>\
									</div> \
									<h4 data-youtube-coverage-id='" + this.id + "' data-field='name' data-youtuber-id='" + this.fields['youtuber'] + "' class='media-heading' >Unknown Youtuber</h4> \
									<p><a data-youtube-coverage-id='" + this.id + "' data-field='url' href='" + this.field('url') + "' target='new'>" + this.field('title') + "</a><br/>\
								</div> \
								<div class='media-right'> \
									<button id='edit-youtube-coverage' class='btn btn-default btn-lg' data-youtube-coverage-id='" + this.field('id') + "'  data-toggle='modal' data-target='.coverage_modal' ><span class='glyphicon glyphicon-pencil'></span></button> \
								</div> \
							</div>";
		}
		if (fromInit) { 
			$('#coverage').append(html);
		} else {
			$('#coverage').prepend(html);
		}
		this.update();


		if (type == "publication") { 
			var t = this;
			$("#edit-coverage[data-coverage-id='" + this.id + "']").click(function() { t.open(); });
		} else if (type == "youtuber") {
			var t = this;
			$("#edit-youtube-coverage[data-youtube-coverage-id='" + this.id + "']").click(function() { t.open(); });
		}

	}
	Coverage.prototype.removeItem = function() {
		if (this.field('type') == 'publication') { 
			$(".media[data-coverage-id='" + this.field('id') + "']").remove();
		} else if (this.field('type') == 'youtuber') {
			$(".media[data-youtube-coverage-id='" + this.field('id') + "']").remove();
		}		
	}
	Coverage.prototype.getPersonName = function() {
		var p = this.fields['person'];
		if (p > 0) {
			return impresslist.findPersonById(p).fullname();
		} else if (this.fields['youtuber'] > 0) {
			return "";
		}
		return "Unknown";
	}
	Coverage.prototype.open = function() {

		var publicationId = "";
		var publicationName = "";
		if (this.fields['publication'] > 0) {
			publicationId = this.fields['publication'];
			publicationName = impresslist.findPublicationById(publicationId).field('name');
		}

		var youtuberId = "";
		var youtuberName = "";
		if (this.fields['youtuber'] > 0) {
			youtuberId = this.fields['youtuber'];
			youtuberName = impresslist.findYoutuberById(youtuberId).field('name');
		}

		var personId = "";
		var personName = "";
		if (this.fields['person'] > 0) {
			personId = this.fields['person'];
			personName = impresslist.findPersonById(personId).field('name');
		}

		var html = "<div class='modal fade coverage_modal' tabindex='-1' role='dialog'> \
						<div class='modal-dialog'> \
							<div class='modal-content' style='padding:5px;'> \
								<div style='min-height:100px;padding:20px;'>";

			html += "				<h3>Edit Coverage (" + this.field('type') + ")</h3>";
			html += "				<form role='form' class='oa' onsubmit='return false;'>	\
										<div class='row'>\
											<div class='form-group col-md-5'>\
												<label>Time:</label> \
												<div class='input-group date' id='coverage-timepicker'>\
													<input id='coverage-edit-timestamp' type='text' class='form-control' />\
														<span class='input-group-addon'>\
														<span class='glyphicon glyphicon-calendar'></span>\
													</span>\
												</div>\
											</div>\
										</div>";
			if (this.field('type') == 'publication') {
				html += "					<div class='row'>\
												<div class='form-group col-md-2'>\
													<label>Publication:&nbsp; </label> \
													<input id='coverage-edit-publication-id' class='form-control' type='text' value='" + publicationId + "' style='width:100%;'/>\
												</div>\
												<div class='form-group col-md-4'>\
													<label>&nbsp; </label> \
													<input id='coverage-edit-publication-search' data-coverage-id='" + this.id + "' data-input-field='publication' class='form-control' type='text' value='" + publicationName + "' placeholder='Search...' />\
												</div>\
												<div id='coverage-edit-publication-results-container' class='form-group col-md-6' style='display:none;'>\
													<label>Results:</label>\
													<table class='table table-striped' style='margin-bottom:0px;'>\
														<tbody id='coverage-edit-publication-results'> \
														</tbody> \
													</table>\
												</div>\
											</div>";
			} else if (this.field('type') == 'youtuber') {
				html += "					<div class='row'>\
												<div class='form-group col-md-2'>\
													<label>Youtuber:&nbsp; </label> \
													<input id='coverage-edit-youtuber-id' class='form-control' type='text' value='" + youtuberId + "' style='width:100%;'/>\
												</div>\
												<div class='form-group col-md-4'>\
													<label>&nbsp; </label> \
													<input id='coverage-edit-youtuber-search' data-youtube-coverage-id='" + this.id + "' data-input-field='youtuber' class='form-control' type='text' value='" + youtuberName + "' placeholder='Search...' />\
												</div>\
												<div id='coverage-edit-youtuber-results-container' class='form-group col-md-6' style='display:none;'>\
													<label>Results:</label>\
													<table class='table table-striped' style='margin-bottom:0px;'>\
														<tbody id='coverage-edit-youtuber-results'> \
														</tbody> \
													</table>\
												</div>\
											</div>";
			}
			html += "					<div class='row'>\
											<div class='form-group col-md-2'>\
												<label>Person:</label> \
												<input id='coverage-edit-person-id' class='form-control' type='text' value='" + personId + "' style='width:100%;'/>\
											</div>\
											<div class='form-group col-md-4'>\
												<label>&nbsp; </label>\
												<input id='coverage-edit-person-search' data-coverage-id='" + this.id + "' data-input-field='person' class='form-control' type='text' value='" + personName + "' placeholder='Search...' />\
											</div>\
											<div id='coverage-edit-person-results-container' class='form-group col-md-6' style='display:none;'>\
												<label>Results:</label>\
												<table class='table table-striped' style='margin-bottom:0px;'>\
													<tbody id='coverage-edit-person-results'> \
													</tbody> \
												</table>\
											</div>\
										</div>\
										<div class='row'>\
											<div class='form-group col-md-12'>\
												<label>Title:</label> \
												<input id='coverage-edit-title' data-coverage-id='" + this.id + "' data-input-field='title' class='form-control' type='text' value='' />\
											</div>\
										</div>\
										<div class='row'>\
											<div class='form-group col-md-12'>\
												<label>URL:</label> \
												<input id='coverage-edit-url' data-coverage-id='" + this.id + "' data-input-field='url' class='form-control' type='text' value='" + this.field('url') + "' />\
											</div>\
										</div>\
										<div class='row'>\
											<div class='form-group col-md-4'>\
												<label class='checkbox-inline'><input id='coverage-edit-thanked' type='checkbox' " + (((this.field('thanked')==1)?"checked":"")) + ">Thanked?</label>\
											</div>\
										</div>\
										<div class='fl'> \
											<button id='save_coverageId" + this.id + "' type='submit' class='btn btn-primary'>Save</button>";
			html += "						&nbsp;<button id='close_coverageId" + this.id + "' type='submit' class='btn btn-default'>Close</button>";
			html += " 					</div><div class='fr'> \
											<button id='delete_coverageId" + this.id + "' type='submit' class='btn btn-danger'>Remove</button> \
										</div>\
									</form>";

		html += "				</div>\
							</div>\
						</div>\
					</div>";
		$('#modals').html(html);

		$('#coverage-edit-title').attr('value', this.field('title'));

		var coverageItem = this;
		$("#save_coverageId" + this.id).click(function() { coverageItem.save(); });
		$("#close_coverageId" + this.id).click(function() { coverageItem.close(); });
		$("#delete_coverageId" + this.id).click(function() { coverageItem.remove(); });

		var utime = this.field('utime');
		if (utime == 0) {
			utime = Date.now() / 1000;
		}
		console.log("time: " + utime);
		$('#coverage-timepicker').datetimepicker();
		$('#coverage-timepicker').data("DateTimePicker").defaultDate(moment(utime, "X"));
		$('#coverage-timepicker').data("DateTimePicker").format("L h:mma");
		

		// Edit publication binds
		if (this.field('type') == 'publication') { 
			$("#coverage-edit-publication-search").keyup(function() {
				var searchfield = $(this);
				var text = $(this).val().toLowerCase();
				if (text.length == 0) {
					$("#coverage-edit-publication-results").html("");
					$('#coverage-edit-publication-results-container').hide();
					return;
				}
				var html = "";
				for(var i = 0; i < impresslist.publications.length; i++) { 
					var include = impresslist.publications[i].search(text);
					if (include) {
						html += "	<tr class='table-list' data-publication-id='" + impresslist.publications[i].id + "' data-coverage-edit-publication-result='true' >\
										<td>" + impresslist.publications[i].name + "</td> \
									</tr>";
					}
				}
				if (html.length == 0) { 
					html += "	<tr> <td colspan='2'>No Results</td> </tr>";
				}
				$("#coverage-edit-publication-results").html(html);
				$('#coverage-edit-publication-results-container').show();

				$("[data-coverage-edit-publication-result='true']").click(function() {
					var pubId = $(this).attr("data-publication-id");
					$('#coverage-edit-publication-id').val("" + pubId);
					$("#coverage-edit-publication-search").val( $(this).find('td').html() );
					$("#coverage-edit-publication-results").html("");
					$('#coverage-edit-publication-results-container').hide();
				});
			});
		} else if (this.field('type') == 'youtuber') {
			$("#coverage-edit-youtuber-search").keyup(function() {
				var searchfield = $(this);
				var text = $(this).val().toLowerCase();
				if (text.length == 0) {
					$("#coverage-edit-youtuber-results").html("");
					$('#coverage-edit-youtuber-results-container').hide();
					return;
				}
				var html = "";
				for(var i = 0; i < impresslist.youtubers.length; i++) { 
					var include = impresslist.youtubers[i].search(text);
					if (include) {
						html += "	<tr class='table-list' data-youtuber-id='" + impresslist.youtubers[i].id + "' data-coverage-edit-youtuber-result='true' >\
										<td>" + impresslist.youtubers[i].name + "</td> \
									</tr>";
					}
				}
				if (html.length == 0) { 
					html += "	<tr> <td colspan='2'>No Results</td> </tr>";
				}
				$("#coverage-edit-youtuber-results").html(html);
				$('#coverage-edit-youtuber-results-container').show();

				$("[data-coverage-edit-youtuber-result='true']").click(function() {
					var ytId = $(this).attr("data-youtuber-id");
					$('#coverage-edit-youtuber-id').val("" + ytId);
					$("#coverage-edit-youtuber-search").val( $(this).find('td').html() );
					$("#coverage-edit-youtuber-results").html("");
					$('#coverage-edit-youtuber-results-container').hide();
				});
			});
		}

		// Edit perosn binds
		$("#coverage-edit-person-search").keyup(function() {
			var searchfield = $(this);
			var text = $(this).val().toLowerCase();
			if (text.length == 0) {
				$("#coverage-edit-person-results").html("");
				$('#coverage-edit-person-results-container').hide();
				return;
			}
			var html = "";
			for(var i = 0; i < impresslist.people.length; i++) { 
				var include = impresslist.people[i].search(text);
				if (include) {
					html += "	<tr class='table-list' data-person-id='" + impresslist.people[i].id + "' data-coverage-edit-person-result='true' >\
									<td>" + impresslist.people[i].name + "</td> \
								</tr>";
				}
			}
			if (html.length == 0) { 
				html += "	<tr> <td colspan='2'>No Results</td> </tr>";
			}
			$("#coverage-edit-person-results").html(html);
			$('#coverage-edit-person-results-container').show();

			$("[data-coverage-edit-person-result='true']").click(function() {
				var pubId = $(this).attr("data-person-id");
				$('#coverage-edit-person-id').val("" + pubId);
				$("#coverage-edit-person-search").val( $(this).find('td').html() );
				$("#coverage-edit-person-results").html("");
				$('#coverage-edit-person-results-container').hide();
			});
		});

	}
	Coverage.prototype.update = function() {
		var selector = "data-coverage-id";
		var publicationName = "Unknown Publication";
		if (this.fields['publication'] > 0) {
			publicationName = impresslist.findPublicationById(this.fields['publication']).name;
		} 

		if (this.field('type') == 'youtuber') {
			publicationName = "Unknown Youtuber";
			if (this.fields['youtuber'] > 0) {
				publicationName = impresslist.findYoutuberById(this.fields['youtuber']).name;
			}

			selector = "data-youtube-coverage-id";

			$("[" + selector + "='" + this.id + "'][data-field='thumbnail']").attr('src', this.field('thumbnail'));
		}

		$("[" + selector + "='" + this.id + "'][data-field='name']").html(publicationName);
		$("[" + selector + "='" + this.id + "'][data-field='url']").attr('href', this.field('url'));
		$("[" + selector + "='" + this.id + "'][data-field='url']").html(this.field('title'));
		$("[" + selector + "='" + this.id + "'][data-field='utime']").html( impresslist.util.relativetime_contact(this.field('utime')) );
		
		var thanked = this.field('thanked');
		if (thanked == 1) {
			$("[" + selector + "='" + this.id + "'][data-field='thanked']").html("<span style='color:green;font-style:italic;'>Thanked!</span>");	
		} else {
			$("[" + selector + "='" + this.id + "'][data-field='thanked']").html("<span style='color:red;font-style:italic;'>Not thanked...</span>");	
		}

		var selector = "[" + selector + "='" + this.id + "'][data-field='person-name']";
		$(selector).html( this.getPersonName() );
		
	}
	Coverage.prototype.onAdded = function(fromInit) {
		this.createItem(fromInit);
	}
	Coverage.prototype.onRemoved = function() {
		this.removeItem();
		this.close();
	}
	Coverage.prototype.save = function() {
		if (this.field('type') == 'publication') { 

			var title = $('#coverage-edit-title').val();
			var url = $('#coverage-edit-url').val();
			var timestamp = moment($('#coverage-edit-timestamp').val(), "L h:mma").format("X");
			var publication = $('#coverage-edit-publication-id').val();
			var person = $('#coverage-edit-person-id').val();
			var thanked = $('#coverage-edit-thanked').is(':checked');

			//console.log("----");
			//console.log(this);
			//console.log("title: " + title);
			//console.log("url: " + url);
			//console.log("timestamp: " + timestamp);
			//console.log("publication: " + publication);
			//console.log("person: " + person);
			//console.log("thanked: " + thanked);
			API.savePublicationCoverage(this, publication, person, title, url, timestamp, thanked);
		} else if (this.field('type') == 'youtuber') { 
			var title = $('#coverage-edit-title').val();
			var url = $('#coverage-edit-url').val();
			var timestamp = moment($('#coverage-edit-timestamp').val(), "L h:mma").format("X");
			var youtuber = $('#coverage-edit-youtuber-id').val();
			var person = $('#coverage-edit-person-id').val();
			var thanked = $('#coverage-edit-thanked').is(':checked');

			/*console.log("youtube coverage save");
			console.log("----");
			console.log(this);
			console.log("title: " + title);
			console.log("url: " + url);
			console.log("timestamp: " + timestamp);
			console.log("youtuber: " + youtuber);
			console.log("person: " + person);
			console.log("thanked: " + thanked);*/
			API.saveYoutuberCoverage(this, youtuber, person, title, url, timestamp, thanked);
		}

	}
	Coverage.prototype.close = function() {
		$('.coverage_modal').modal('hide');
	}
	Coverage.prototype.remove = function() {
		if (this.field('type') == 'publication') { 
			API.removePublicationCoverage(this);
		} else if (this.field('type') == 'youtuber') {
			API.removeYoutuberCoverage(this);
		}
	}

Game = function(data) {
	DBO.call(this, data); 
}
	Game.prototype = Object.create(DBO.prototype)
	Game.prototype.constructor = Game;
	Game.prototype.init = function(data) {
		DBO.prototype.init.call(this, data);
		this.id = parseInt(this.field('id'));
		this.name = this.field('name');
		
	}



User = function(data) {
	DBO.call(this, data); 
}
	User.prototype = Object.create(DBO.prototype)
	User.prototype.constructor = User;
	User.prototype.init = function(data) {
		DBO.prototype.init.call(this, data);
		this.id = parseInt(this.field('id'));
	}
	User.prototype.fullname = function() {
		return this.field('forename') + " " + this.field('surname');
	}
	User.prototype.openChangePassword = function() {
		var html = "<div class='modal fade password_modal' tabindex='-1' role='dialog'> \
						<div class='modal-dialog'> \
							<div class='modal-content' style='padding:5px;'> \
								<div style='min-height:100px;padding:20px;'> \
									<h3>Change Password</h3> \
									<form role='form' class='oa' onsubmit='return false;'>	\
										<div class='form-group'>\
											<label for='password-current'>Current Password: </label> \
											<input id='user-change-password-current' class='form-control' type='password' name='password-current' value='' />\
										</div>\
										<div class='form-group'>\
											<label for='password-new'>New Password:</label> \
											<input id='user-change-password-new' class='form-control' type='password' name='password-new' value='' />\
										</div>\
										<div class='fl'> \
											<button id='user-change-password-submit' type='submit' class='btn btn-primary'>Save</button> \
											&nbsp;<button id='user-change-password-close' type='submit' class='btn btn-default'>Close</button> \
										</div>\
									</form> \
								</div>\
							</div>\
						</div>\
					</div>";
		$('#modals').html(html);

		var thiz = this;
		$('#user-change-password-submit').click(function() {
			var currentPassword = $('#user-change-password-current').val();
			var newPassword = $('#user-change-password-new').val(); 
			API.userChangePassword(thiz, currentPassword, newPassword);
		});
		$('#user-change-password-close').click(function() {
			$('.password_modal').modal("hide");
		});

	};
	User.prototype.openChangeIMAPSettings = function() {
		var html = "<div class='modal fade imapsettings_modal' tabindex='-1' role='dialog'> \
						<div class='modal-dialog'> \
							<div class='modal-content' style='padding:5px;'> \
								<div style='min-height:100px;padding:20px;'> \
									<h3>IMAP Settings</h3> \
									<form role='form' class='oa' onsubmit='return false;'>	\
										<div class='form-group'>\
											<label for='change-smtp-server'>SMTP Server:</label> \
											<input id='user-change-smtp-server' type='text' class='form-control' value='" + impresslist.config.user.smtpServer + "'   />\
										</div>\
										<div class='form-group'>\
											<label for='change-imap-server'>IMAP Server:</label> \
											<input id='user-change-imap-server' type='text' class='form-control' value='" + impresslist.config.user.imapServer + "'   />\
										</div>\
										<div class='form-group'>\
											<label for='change-imap-password'>IMAP Password:</label> \
											<input id='user-change-imap-password' class='form-control'  type='password' />\
										</div>\
										<div class='fl'> \
											<button id='user-change-imap-settings-submit' type='submit' class='btn btn-primary'>Save</button> \
											&nbsp;<button id='user-change-imap-settings-close' type='submit' class='btn btn-default'>Close</button> \
										</div>\
									</form> \
								</div>\
							</div>\
						</div>\
					</div>";
		$('#modals').html(html);

		var thiz = this;
		$('#user-change-imap-settings-submit').click(function() {
			var smtp_server = $('#user-change-smtp-server').val();
			var imap_server = $('#user-change-imap-server').val();
			var imap_password = $('#user-change-imap-password').val(); 
			API.userChangeIMAPSettings(thiz, smtp_server, imap_server, imap_password);
		});
		$('#user-change-imap-settings-close').click(function() {
			$('.imapsettings_modal').modal("hide");
		});

	};


Youtuber = function(data) {
	DBO.call(this, data); 
}
	Youtuber.prototype = Object.create(DBO.prototype);
	Youtuber.prototype.constructor = Youtuber;
	Youtuber.prototype.init = function(data) {
		DBO.prototype.init.call(this, data);
		this.id = parseInt(this.field('id'));
		this.name = this.field('name');

		this.initPriorities('priorities');
	}

	Youtuber.prototype.createTableRow = function() {
		var html = "	<tr data-youtuber-id='" + this.field('id') + "' data-youtuber-tablerow='true' class='table-list' data-toggle='modal' data-target='.youtuber_modal'> \
							<td data-youtuber-id='" + this.field('id') + "' data-field='name' data-value='" + this.field('name') + "'>" + this.field('name') + "</td> \
							<td data-youtuber-id='" + this.field('id') + "' data-field='priority' data-value='" + this.priority() + "'>" + Priority.name(this.priority()) + "</td> \
							<td data-youtuber-id='" + this.field('id') + "' data-field='subscribers' data-value='" + this.field('subscribers') + "'><a href='http://youtube.com/user/" + this.field('channel') + "' target='new'>" + new Number(this.field('subscribers')).toLocaleString() + "</a></td> \
							<td data-youtuber-id='" + this.field('id') + "' data-field='views' data-value='" + this.field('views') + "'>" + new Number(this.field('views')).toLocaleString() + "</td> \
							<td data-youtuber-id='" + this.field('id') + "' data-field='twitter_followers' data-value='" + this.field('twitter_followers') + "'>" + this.twitterCell() + "</td> \
							<td data-youtuber-id='" + this.field('id') + "' data-field='lastpostedon' data-value='" + this.field('lastpostedon') + "'>" + impresslist.util.relativetime_contact(this.field('lastpostedon')) + "</td> \
						</tr>";
		$('#youtubers').append(html);
		
		var youtuber = this;
		$(this.openSelector()).click(function() {
			youtuber.open();
		});
	};
	Youtuber.prototype.openSelector = function() {
		return "#youtubers [data-youtuber-tablerow='true'][data-youtuber-id='" + this.id + "']";
	};

	Youtuber.prototype.removeTableRow = function() {
		$("#youtubers [data-youtuber-tablerow='true'][data-youtuber-id='" + this.id + "']").remove();
	}
	Youtuber.prototype.onAdded = function() {
		this.createTableRow();
	}
	Youtuber.prototype.onRemoved = function() {
		//$("[data-youtuber-id='" + this.id + "'][data-youtuber-tablerow='true']").remove();
		this.removeTableRow();
		this.close();
	}
	Youtuber.prototype.open = function() {
		var html = "<div class='modal fade youtuber_modal' tabindex='-1' role='dialog'> \
						<div class='modal-dialog'> \
							<div class='modal-content' style='padding:5px;'> \
								<div style='min-height:100px;padding:20px;'>";

			html += " 				<h3 data-youtuber-id='" + this.id + "' data-field='name'>" + this.field('name') + "</h3> ";
			html += "				<form role='form' class='oa' onsubmit='return false;'>	\
										<div class='row'>\
											<div class='form-group col-md-8'>\
												<label for='url'>Channel Name:&nbsp; </label> \
												<input data-youtuber-id='" + this.id + "' data-input-field='channel' class='form-control' type='text' value='" + this.field('channel') + "' />\
											</div>\
											<div class='form-group col-md-4'>\
												<label for='url'>Priority:&nbsp; </label>"
													var priority = this.priority();
													html += "	<select data-youtuber-id='" + this.id + "' data-input-field='priority' class='form-control'>\
																	<option value='3' " + ((priority==3)?"selected='true'":"") + ">High</option>\
																	<option value='2' " + ((priority==2)?"selected='true'":"") + ">Medium</option>\
																	<option value='1' " + ((priority==1)?"selected='true'":"") + ">Low</option>\
																	<option value='0' " + ((priority==0)?"selected='true'":"") + ">N/A</option>\
																</select>";					
			html += "						</div>\
										</div>\
										<div class='form-group'>\
											<label for='email'>Email:&nbsp; </label> \
											<input data-youtuber-id='" + this.id + "' data-input-field='email' class='form-control' type='text' value='" + this.field('email') + "' />\
										</div>\
										<div class='form-group'>\
											<label for='twitter'>Twitter Username:&nbsp; </label> \
											<input data-youtuber-id='" + this.id + "' data-input-field='twitter' class='form-control' type='text' value='" + this.field('twitter') + "' />\
										</div>\
										<div class='form-group'>\
											<label for='email'>Notes:&nbsp; </label> \
											<textarea data-youtuber-id='" + this.id + "' data-input-field='notes' class='form-control' style='height:100px;'>" + this.field('notes') + "</textarea>\
										</div>\
										<div class='fl'> \
											<button id='save_youtuberId" + this.id + "' type='submit' class='btn btn-primary'>Save</button>";
			html += "						&nbsp;<button id='close_youtuberId" + this.id + "' type='submit' class='btn btn-default'>Close</button>";
			html += " 					</div><div class='fr'> \
											<button id='delete_youtuberId" + this.id + "' type='submit' class='btn btn-danger'>Remove</button> \
										</div>\
									</form>";

		html += "				</div>\
							</div>\
						</div>\
					</div>";
		$('#modals').html(html);

		var youtuber = this;
		$("#save_youtuberId" + this.id).click(function() { youtuber.save(); });
		$("#close_youtuberId" + this.id).click(function() { youtuber.close(); });
		$("#delete_youtuberId" + this.id).click(function() { API.removeYoutuber(youtuber); });

		$("[data-youtuber-id='" + this.id + "'][data-input-field='priority']").change(function() {
			youtuber.savePriority();
		});
	}
	Youtuber.prototype.update = function() {
		$("[data-youtuber-id='" + this.id + "'][data-field='name']").html(this.name);
		$("[data-youtuber-id='" + this.id + "'][data-field='channel']").html(this.field('channel'));
		$("[data-youtuber-id='" + this.id + "'][data-field='priority']").html(Priority.name(this.priority()));
		$("[data-youtuber-id='" + this.id + "'][data-field='subscribers']").html("<a href='http://youtube.com/user/" + this.field('channel') + "' target='new'>" + new Number(this.field('subscribers')).toLocaleString() + "</a>");
		$("[data-youtuber-id='" + this.id + "'][data-field='views']").html( new Number(this.field('views')).toLocaleString() );
		$("[data-youtuber-id='" + this.id + "'][data-field='twitter']").html(this.field('twitter'));
		$("[data-youtuber-id='" + this.id + "'][data-field='twitter_followers']").html( this.twitterCell() );			
		$("[data-youtuber-id='" + this.id + "'][data-field='lastpostedon']").html( impresslist.util.relativetime_contact(this.field('lastpostedon')) );			
	};
	
	Youtuber.prototype.close = function() {
		$('.youtuber_modal').modal('hide');
	}
	Youtuber.prototype.filter = function(text) {
		var element = $("#youtubers [data-youtuber-tablerow='true'][data-youtuber-id='" + this.id + "']");
		if (this.search(text) && this.filter_isHighPriority() && this.filter_hasEmail()) { // && this.isContactedByMe() && this.isRecentlyContacted()) {
			element.show();
			return true;
		} else {
			element.hide();
			return false;
		}
	}
	Youtuber.prototype.filter_isHighPriority = function() {
		if ($('#filter-high-priority').is(':checked')) { 
			return (this.priority() == 3);
		}
		return true;
	}
	Youtuber.prototype.filter_hasEmail = function() {
		if ($('#filter-email-attached').is(':checked')) { 
			return (this.field('email').length > 0);
		}
		return true;
	}
	Youtuber.prototype.search = function(text) {
		var ret = true;

		if (text.length == 0) { return ret; }

		ret = this.name.toLowerCase().indexOf(text) != -1;
		if (ret) { return ret; }

		ret = this.fields['notes'].toLowerCase().indexOf(text) != -1;
		if (ret) { return ret; }

		ret = this.fields['description'].toLowerCase().indexOf(text) != -1;
		if (ret) { return ret; }
		
		ret = this.fields['twitter'].toLowerCase().indexOf(text) != -1;
		if (ret) { return ret; }

		return false;
	}
	Youtuber.prototype.save = function() {
		var channel = $("[data-youtuber-id=" + this.id + "][data-input-field='channel']").val();
		var email   = $("[data-youtuber-id=" + this.id + "][data-input-field='email']").val();
		var twitter = $("[data-youtuber-id=" + this.id + "][data-input-field='twitter']").val();
		var notes   = $("[data-youtuber-id=" + this.id + "][data-input-field='notes']").val();
		
		API.saveYoutuber(this, channel, email, twitter, notes);
	};
	Youtuber.prototype.savePriority = function() {
		var priority = $("[data-youtuber-id='" + this.id + "'][data-input-field='priority']").val();
		API.setYoutuberPriority(this, priority, impresslist.config.user.game);
	}



PersonPublication = function(data) {
	DBO.call(this, data); 
}
	PersonPublication.prototype = Object.create(DBO.prototype);
	PersonPublication.prototype.constructor = PersonPublication;
	PersonPublication.prototype.init = function(data) {
		DBO.prototype.init.call(this, data);
		this.id = parseInt(this.fields['id']);
	}
	PersonPublication.prototype.open = function() {
		console.log('per pub open');
		var obj = this;
		var pub = impresslist.findPublicationById(this.fields['publication']);
		var html = "<div data-perpub-id='" + this.id + "' data-perpub-tablerow='true' class='panel panel-default'> \
						<div class='panel-heading oa'> \
							<h3 class='panel-title fl'>" + pub.name + "&nbsp;</h3><span class='fr'>Last Contacted: <a data-perpub-id='" + this.id + "' href=''>" + impresslist.util.relativetime_contact(this.fields['lastcontacted']) + "</a></span> \
						</div> \
						<div class='panel-body'> \
							<div class='row'> \
								<div class='form-group col-sm-8'> \
									<label for='email'>Email:</label> \
									<input data-perpub-id='" + this.id + "' data-input-field='email' type='text' class='form-control' value='" + this.fields['email'] + "' style='width:100%;'/> \
								</div>\
								<div class='fl padx'> \
									<label for='submit'>&nbsp;</label><br/> \
									<button id='save_personPublicationId" + this.id + "' type='submit' class='btn btn-primary' data-perpub-id='" + this.id + "'>Save</button> \
								</div> \
								<div class='fl padx'> \
									<label for='submit'>&nbsp;</label><br/> \
									<button id='delete_personPublicationId" + this.id + "' type='submit' class='btn btn-danger' data-perpub-id='" + this.id + "'>Remove</button> \
								</div> \
							</div> \
						</div> \
					</div>";
		$('#person-publications').append(html);

		$('#save_personPublicationId' + this.id).click(function() { obj.save(); });
		$('#delete_personPublicationId' + this.id).click(function() { API.removePersonPublication(obj); });

		$("[data-perpub-id='" + this.id + "'] a").click(function(e) {
			e.preventDefault();
			$("#person_tabs [data-tab='person_messages']").click();
		});
	}
	PersonPublication.prototype.save = function() {
		var email = $("[data-perpub-id=" + this.id + "][data-input-field='email']").val();
		API.savePersonPublication(this, email);
	}
	PersonPublication.prototype.update = function() {

	}
	PersonPublication.prototype.onAdded = function(fromInit) {
		if (!fromInit) {
			this.open();
		}
	};
	PersonPublication.prototype.onRemoved = function() {
		$("[data-perpub-id='" + this.id + "'][data-perpub-tablerow='true']").remove();
	}


PersonYoutubeChannel = function(data) {
	DBO.call(this, data);
}
	PersonYoutubeChannel.prototype = Object.create(DBO.prototype);
	PersonYoutubeChannel.prototype.constructor = PersonYoutubeChannel;
	PersonYoutubeChannel.prototype.init = function(data) {
		DBO.prototype.init.call(this, data);
		this.id = parseInt(this.fields['id']);
	}
	PersonYoutubeChannel.prototype.open = function() {
		console.log('per yt open');
		var obj = this;
		var yt = impresslist.findYoutuberById(this.fields['youtuber']);
		var html = "<div data-peryt-id='" + this.id + "' data-peryt-tablerow='true' class='panel panel-default'> \
						<div class='panel-heading oa'> \
							<h3 class='panel-title fl'>" + yt.name + "&nbsp;</h3> \
						</div> \
						<div class='panel-body'> \
							<div class='row'> \
								<div class='fr padx'> \
									<button id='delete_personYoutubeChannelId" + this.id + "' type='submit' class='btn btn-danger' data-peryt-id='" + this.id + "'>Remove</button> \
								</div> \
							</div> \
						</div> \
					</div>";
		$('#person-youtubechannels').append(html);

		$('#delete_personYoutubeChannelId' + this.id).click(function() { API.removePersonYoutubeChannel(obj); });

		$("[data-peryt-id='" + this.id + "'] a").click(function(e) {
			e.preventDefault();
			$("#person_tabs [data-tab='person_messages']").click();
		});
	}
	
	PersonYoutubeChannel.prototype.update = function() {

	}
	PersonYoutubeChannel.prototype.onAdded = function(fromInit) {
		if (!fromInit) {
			this.open();
		}
	};
	PersonYoutubeChannel.prototype.onRemoved = function() {
		$("[data-peryt-id='" + this.id + "'][data-peryt-tablerow='true']").remove();
	}



SimpleMailout = function(data) {
	DBO.call(this, data);
}
	SimpleMailout.prototype = Object.create(DBO.prototype);
	SimpleMailout.prototype.constructor = SimpleMailout;
	SimpleMailout.prototype.init = function(data) {
		DBO.prototype.init.call(this, data);
		this.id = parseInt(this.field('id'));
		
		// Todo: Calculate and cache this server-side or something.
		this.numRecipients = 0;
		this.numOpens = 0;
		this.recipientsData = JSON.parse(data.recipients);
		for(var i = 0; i < this.recipientsData.length; i++) {
			if (this.recipientsData[i].read) {
				this.numOpens++;
			}
			this.numRecipients++; 
		}
	}
	SimpleMailout.prototype.createTableRow = function() {
		var html = "	<tr data-simplemailout-id='" + this.field('id') + "' data-simplemailout-tablerow='true' class='table-list' > \
							<td data-simplemailout-id='" + this.field('id') + "' data-field='name' data-value='" + this.field('name') + "'>" + this.field('name') + "</td> \
							<td data-simplemailout-id='" + this.field('id') + "' data-field='numRecipients' data-value='" + this.numRecipients + "'>" + this.numRecipients + "</td> \
							<td data-simplemailout-id='" + this.field('id') + "' data-field='numOpens' data-value='" + this.numOpens + "'>...</td>\
							<td data-simplemailout-id='" + this.field('id') + "' data-field='timestamp' data-value='" + this.field('timestamp') + "'>...</td>";
		html += "		</tr>";
		$('#mailout-list-tbody').append(html);
		$('#mailout-list-footer').hide();
		
		// Events
		var mailout = this;
		$('tr[data-simplemailout-id="' + this.field('id') + '"][data-simplemailout-tablerow="true"]').unbind("click");
		$('tr[data-simplemailout-id="' + this.field('id') + '"][data-simplemailout-tablerow="true"]').click(function() {
			mailout.open();
		});

		this.update();
	}
	SimpleMailout.prototype.removeTableRow = function() {
		$("[data-simplemailout-tablerow='true'][data-simplemailout-id='" + this.field('id') + "']").remove();
	}
	SimpleMailout.prototype.update = function() {
		var sentText = "";
		if (this.field("sent") == 1) {
			sentText = "Sent " + impresslist.util.relativetime_contact(this.field('timestamp'));
		} else if (this.field("ready") == 1 && (Date.now() / 1000) <= this.field('timestamp') ) {
			sentText = "Scheduled " + impresslist.util.relativetime_contact(this.field('timestamp'));
		} else if (this.field("ready") == 1 && (Date.now() / 1000) > this.field('timestamp') ) {
			sentText = "In send queue.";
		} else {
			sentText = "Draft";
		}

		var recipientsText = "" + this.numRecipients;

		var openText = "-";
		if (this.field("sent") == 1) {
			openText = (((1.0*this.numOpens)/(1.0*this.numRecipients))*100) + "%";
		}

		$("[data-simplemailout-id='" + this.id + "'][data-field='name']").html( this.field('name') );
		$("[data-simplemailout-id='" + this.id + "'][data-field='numRecipients']").html( recipientsText );
		$("[data-simplemailout-id='" + this.id + "'][data-field='numOpens']").html( openText );
		$("[data-simplemailout-id='" + this.id + "'][data-field='timestamp']").html( sentText );		
	}
	SimpleMailout.prototype.send = function() {
		var mailout = this;
		this.save(function() {
			API.sendSimpleMailout(mailout);	
			mailout.close();
		});		
	}
	SimpleMailout.prototype.cancelsend = function() {
		API.cancelSimpleMailout(this);
		this.close();
	}
	SimpleMailout.prototype.save = function(callback) {
		if (typeof callback == 'undefined') { callback = function() {}; }
		var name = $('#mailout-name').val();
		var subject = $('#mailout-subject').val();
		var markdown = $('#mailout-content').val();
		
		var recipients = [];
		var peopleSelected = $('input[data-type="person"][data-checkbox="true"]:checked');
		for(var i = 0; i < peopleSelected.length; i++) {
			var obj = {	};
			obj.type = $(peopleSelected[i]).attr('data-mailout-type');
			obj.sent = false;
			obj.read = false;
			if (obj.type == 'person') {
				obj.person_id = $(peopleSelected[i]).attr('data-mailout-typeid');
			} else if (obj.type == "personPublication") {
				obj.personPublication_id = $(peopleSelected[i]).attr('data-mailout-typeid');
			}
			recipients.push(obj);
		}
		console.log(JSON.stringify(recipients));
		// #####
		//  $('#mailout-radio-time-asap');
		var whichtime = $('input[name="mailout-time-radio"]:checked').val();
		if (whichtime == undefined) {
			API.errorMessage("Please select a time for this mailout.");
			return;
		}
		var timestamp = 0;
		if (whichtime == 'later') {
			var date = new Date($('#mailout-timepicker').data("DateTimePicker").date());
			timestamp = Math.floor(date.getTime() / 1000);
		}
		
		API.saveSimpleMailout(this, name, subject, recipients, markdown, timestamp, callback);
	}
	SimpleMailout.prototype.setFormEnabled = function(boo) {
		boo = !boo;
		$('#mailout-name').attr("disabled", boo); 
		$('#mailout-subject').attr("disabled", boo);
		$('#mailout-content').attr("disabled", boo);
		$('#mailout-radio-time-asap').attr("disabled", boo);
		$('#mailout-radio-time-scheduled').attr("disabled", boo);
		$('#mailout-timepicker').attr("disabled", boo);
		$('#mailout-edit-timestamp').attr("disabled", boo);
		$('#mailout-writepage-back').attr("disabled", boo);
		$('#mailout-writepage-save').attr("disabled", boo);
		$('#mailout-writepage-send').attr("disabled", boo);
		$('#mailout-writepage-cancelsend').attr("disabled", boo);
		$('#mailout-writepage-remove').attr("disabled", boo);
	}
	SimpleMailout.prototype.open = function() {
		console.log("mailout open");
		$('#mailout-homepage').hide();
		$('#mailout-writepage').show();
		$('#mailout-writepage-send').hide();
		$('#mailout-writepage-cancelsend').hide();
		$('.mailout-readflags').hide();

		$('#mailout-writepage-save').show();
		$('#mailout-writepage-remove').show();
		if (this.field("ready") == 0) {
			$('#mailout-writepage-send').show();
		} 
		if (this.field("ready") == 1 && this.field("sent") == 0) {
			$('#mailout-writepage-cancelsend').show();
		}
		
		this.setFormEnabled(true);

		// Set data on form.
		$('#mailout-name').val(this.field("name"));
		$('#mailout-subject').val(this.field("subject"));
		$('#mailout-content').val(this.field("markdown"));
		$('#mailout-content').keyup();

		var utime = this.field('timestamp');
		if (utime == 0) {
			utime = Date.now() / 1000;
		}
		$('#mailout-timepicker').datetimepicker();
		$('#mailout-timepicker').data("DateTimePicker").defaultDate(moment(utime, "X"));
		$('#mailout-timepicker').data("DateTimePicker").format("DD/MM/YYYY h:mma");

		// Events
		var mailout = this;
		$('#mailout-writepage-save').unbind("click");
		$('#mailout-writepage-save').click(function() {
			mailout.save();
		});
		$('#mailout-writepage-send').unbind("click");
		$('#mailout-writepage-send').click(function() {
			mailout.send();
		}); 
		$('#mailout-writepage-cancelsend').unbind("click");
		$('#mailout-writepage-cancelsend').click(function() {
			mailout.cancelsend();
		});
		$('#mailout-writepage-back').unbind("click");
		$('#mailout-writepage-back').click(function(){ 
			mailout.close();
		});
		$('#mailout-writepage-remove').unbind("click");
		$('#mailout-writepage-remove').click(function() { 
			mailout.setFormEnabled(false);
			API.removeSimpleMailout(mailout); 
		});
		
		// time picker
		$('#mailout-radio-time-asap').unbind("click");
		$('#mailout-radio-time-scheduled').unbind("click");
		$('#mailout-radio-time-asap').click(function() { $('#mailout-timepicker').hide(); });
		$('#mailout-radio-time-scheduled').click(function() { $('#mailout-timepicker').show(); });
		if (this.field("timestamp") == 0) { 
			$('#mailout-timepicker').hide();
			$('#mailout-radio-time-asap').prop("checked", "true");
		} else {
			$('#mailout-timepicker').show();
			$('#mailout-radio-time-scheduled').prop("checked", "true");
		}

		// Sync recipients.
		impresslist.openSelectMode();
		for(var i = 0; i < this.recipientsData.length; i++) {
			if (this.recipientsData[i].type == 'person') {
				var p = impresslist.findPersonById(this.recipientsData[i].person_id);
				if (p != null) { 
					var box = $('input[data-type="person"][data-mailout-typeid="' + p.id + '"][data-checkbox="true"][data-mailout-type="person"]');
					$(box).prop("checked", true);
				}
			} else if (this.recipientsData[i].type == "personPublication") {
				var p = impresslist.findPersonPublicationById(this.recipientsData[i].personPublication_id);
				if (p != null) { 
					var box = $('input[data-type="person"][data-mailout-typeid="' + p.id + '"][data-checkbox="true"][data-mailout-type="personPublication"]');
					$(box).prop("checked", true);
				}
			}
			$(box).attr("mailout-read", this.recipientsData[i].read);
		}
		impresslist.refreshMailoutRecipients();

		console.log (this.field("sent"));
		if (this.field("sent") == 1) {
			this.setFormEnabled(false);
			$('#mailout-writepage-back').attr("disabled", false);
			$('#mailout-writepage-save').hide();
			$('#mailout-writepage-remove').hide();
			$('#mailout-writepage-send').hide();

			$('.mailout-readflags').show();
		}
	}
	SimpleMailout.prototype.close = function() {
		console.log("mailout close");
		impresslist.closeSelectMode();
		$('#mailout-writepage').hide();
		$('#mailout-homepage').show();
	}
	SimpleMailout.prototype.onAdded = function(fromInit) {
		this.createTableRow();
	}
	SimpleMailout.prototype.onRemoved = function(fromInit) {
		this.setFormEnabled(true);
		this.removeTableRow();
		this.close();
	}


Person = function(data) {
	DBO.call(this, data); 
}
	Person.prototype = Object.create(DBO.prototype);
	Person.prototype.constructor = Person;
	Person.prototype.init = function(data) {
		DBO.prototype.init.call(this, data);

		this.id = parseInt(this.field('id'));
		this.name = this.field('firstname');
		//this.publications = [];

		this.initPriorities('priorities');
		
	}
	Person.prototype.update = function() {
		$("[data-person-id='" + this.id + "'][data-field='name']").html(this.fullname());
		$("[data-person-id='" + this.id + "'][data-field='priority']").html(Priority.name(this.priority()));
		$("[data-person-id='" + this.id + "'][data-field='twitter_followers']").html( this.twitterCell() );		
	},
	Person.prototype.save = function() {
		var firstname = $("[data-person-id=" + this.id + "][data-input-field='firstname']").val();
		var surnames = $("[data-person-id=" + this.id + "][data-input-field='surnames']").val();
		var email = $("[data-person-id=" + this.id + "][data-input-field='email']").val();
		var twitter = $("[data-person-id=" + this.id + "][data-input-field='twitter']").val();
		var notes = $("[data-person-id=" + this.id + "][data-input-field='notes']").val();
		var outofdate = $("[data-person-id=" + this.id + "][data-input-field='outofdate']").is(':checked');
		
		API.savePerson(this, firstname, surnames, email, twitter, notes, outofdate);
	}
	Person.prototype.savePriority = function() {
		var priority = $("[data-person-id='" + this.id + "'][data-input-field='priority']").val();
		API.setPersonPriority(this, priority, impresslist.config.user.game);
	}
	Person.prototype.saveUserAssignment = function() {
		var assignment = $("[data-person-id='" + this.id + "'][data-input-field='assignment']").val();
		API.setPersonAssignment(this, assignment);
	}
	
	Person.prototype.preventOpen = function() {
		$('#modals').html("");
	}
	Person.prototype.open = function() {

		var html = "<div class='modal fade person_modal' tabindex='-1' role='dialog'> \
						<div class='modal-dialog'> \
							<div class='modal-content' style='padding:5px;'>";
				
		html += "				<div style='min-height:100px;padding:20px;'>";
		
		html += "	<div style='min-height:45px;'> \
						<h3 class='fl' data-person-id='" + this.id + "' data-field='name'>" + this.fullname() + "</h3> \
						\
						<div class='fr'>\
						<!-- Single button -->\
						<div class='btn-group'>\
							<button id='person-email-default' type='button' class='btn btn-default'>\
								<span class='glyphicon glyphicon-send glyphicon-align-left' aria-hidden='true'></span> Email \
							</button>\
							<button type='button' class='btn btn-default dropdown-toggle' data-toggle='dropdown' aria-expanded='false'>\
								<span class='caret'></span> \
								<span class='sr-only'>Toggle Dropdown</span> \
							</button>\
							<ul class='dropdown-menu' role='menu' style='position:absolute;z-index:999;'>";

								var emailSubject = ""; //Press List test";
								var emailBody = ""; //"Press List test body. Include default text here or something.";
								var emailBCC = impresslist.config.system.email;
								var emailGmailIndex = impresslist.config.user.gmail; 
								
								var emails = [];
								emails.push( {"type": "Personal", "email": this.field('email')} );
								for(var i = 0; i < impresslist.personPublications.length; ++i) {
									if (impresslist.personPublications[i].field('person') ==  this.id) {
										emails.push( {"type": impresslist.findPublicationById(impresslist.personPublications[i].field('publication')).field('name'), "email": impresslist.personPublications[i].field('email')} );
									}
								}

								for(var i = 0; i < emails.length; ++i) 
								{ 
									var defaultEmail = emails[i]['email'];
									if (defaultEmail.length == 0) { continue; }
									var emailClientLink = impresslist.util.mailtoClient(defaultEmail, emailSubject, emailBody, emailBCC);
									var emailGmailLink = impresslist.util.mailtoGmail(defaultEmail, emailSubject, emailBody, emailBCC, emailGmailIndex); 
								
									html += "<li><a href='" + emailGmailLink + "' target='new'>" + emails[i]['type'] + " (Gmail)</a></li>";
									html += "<li><a href='" + emailClientLink + "'>" + emails[i]['type'] + " (Client)</a></li>";
								}
								
								
		html += "			</ul>\
							</div>\
						</div>\
					</div>";
		html += "	<div id='person_tabs_container' role='tabpanel'>";
		html += '		<ul id="person_tabs" class="nav nav-tabs" role="tablist"> \
							<li role="presentation" class="active"><a role="tab" href="#" data-tab="person_profile" data-toggle="tab">Profile</a></li> \
							<li role="presentation"><a role="tab" href="#" data-tab="person_publications" data-toggle="tab">Publications</a></li> \
							<li role="presentation"><a role="tab" href="#" data-tab="person_youtubeChannels" data-toggle="tab">Youtube Channels</a></li> \
							<li role="presentation"><a role="tab" href="#" data-tab="person_messages" data-toggle="tab">Messages</a></li> \
						</ul> \
						<div class="tab-content">';

				// Personal profile panel.
				html += "	<div role='tabpanel' class='tab-pane active pady' data-tab='person_profile'>\
								<form role='form' class='oa' onsubmit='return false;'>	\
									<div class='row'>\
										<div class='form-group col-md-3'>\
											<label for='name'>First Name:&nbsp; </label> \
											<input data-person-id='" + this.id + "' data-input-field='firstname' class='form-control' type='text' value='" + this.field('firstname') + "' />\
										</div>\
										<div class='form-group col-md-3'>\
											<label for='name'>Surname/s:&nbsp; </label> \
											<input data-person-id='" + this.id + "' data-input-field='surnames' class='form-control' type='text' value='" + this.field('surnames') + "' />\
										</div>";
				html += "				<div class='form-group col-md-3'>\
											<label for='email'>Assignment:</label>";
											var assignment = this.fields['assigned'];
											html += "	<select data-person-id='" + this.id + "' data-input-field='assignment' class='form-control'>";
												for (var u = 0; u < impresslist.users.length; u++) {
													html += "<option value='" + impresslist.users[u].id + "' " + ((assignment==impresslist.users[u].id)?"selected='true'":"") + ">" + impresslist.users[u].fullname() + "</option>";
												}
											html += " 		<option value='0' " + ((assignment==0)?"selected='true'":"") + ">N/A</option>\
														</select>";
				html += "				</div>";
				html += "				<div class='form-group col-md-3'>\
											<label for='email'>Priority:</label>";
											var priority = this.priority();
											html += "	<select data-person-id='" + this.id + "' data-input-field='priority' class='form-control'>\
															<option value='3' " + ((priority==3)?"selected='true'":"") + ">High</option>\
															<option value='2' " + ((priority==2)?"selected='true'":"") + ">Medium</option>\
															<option value='1' " + ((priority==1)?"selected='true'":"") + ">Low</option>\
															<option value='0' " + ((priority==0)?"selected='true'":"") + ">N/A</option>\
														</select>";
				html += "				</div>\
									</div>\
									<div class='form-group'>\
										<label for='email'>Email:&nbsp; </label> \
										<input data-person-id='" + this.id + "' data-input-field='email' class='form-control' type='text' value='" + this.field('email') + "' />\
									</div>\
									<div class='form-group'>\
										<label for='email'>Twitter Username:&nbsp; </label> \
										<input data-person-id='" + this.id + "' data-input-field='twitter' class='form-control' type='text' value='" + this.field('twitter') + "' />\
									</div>\
									<div class='form-group'>\
										<label for='email'>Notes:&nbsp; </label> \
										<textarea data-person-id='" + this.id + "' data-input-field='notes' class='form-control' style='height:100px;'>" + this.field('notes') + "</textarea>\
									</div>\
									<div class='form-group'>\
										<label class='checkbox-inline'><input data-person-id='" + this.id + "' data-input-field='outofdate' type='checkbox' " + (((this.field('outofdate')==1)?"checked":"")) + "><strong>Out of date?</strong></label>\
									</div>\
									<div class='fl'> \
										<button id='save_personId" + this.id + "' type='submit' class='btn btn-primary'>Save</button>";
				html += "				&nbsp;<button id='close_personId" + this.id + "' type='submit' class='btn btn-default'>Close</button>";
				html += " 			</div><div class='fr'> \
										<button id='delete_personId" + this.id + "' type='submit' class='btn btn-danger'>Remove</button> \
									</div>\
								</form>\
							</div>";


				// Publications panel
				html += "	<div role='tabpanel' class='tab-pane pady' data-tab='person_publications'> \
								<div class='form-group'>\
									<label for='add-publication'>Add:&nbsp;</label> \
									<input id='add-publication-search' type='text' class='form-control' placeholder='Search' /> \
								</div>\
								<div id='add-publication-results-container' style='display:none;'>\
									<table class='table table-striped'>\
										<thead>\
											<th>Name</th> \
											<th>URL</th> \
										</thead> \
										<tbody id='add-publication-results'> \
										</tbody> \
									</table>\
								</div> \
								<div id='person-publications'>";

				html += "		</div>\
							</div>";
 				

 				// Youtube Channels panel
				html += "	<div role='tabpanel' class='tab-pane pady' data-tab='person_youtubeChannels'> \
								<div class='form-group'>\
									<label for='add-youtubechannel'>Add:&nbsp;</label> \
									<input id='add-youtubechannel-search' type='text' class='form-control' placeholder='Search' /> \
								</div>\
								<div id='add-youtubechannel-results-container' style='display:none;'>\
									<table class='table table-striped'>\
										<thead>\
											<th>Name</th> \
											<th>Subscribers</th> \
										</thead> \
										<tbody id='add-youtubechannel-results'> \
										</tbody> \
									</table>\
								</div> \
								<div id='person-youtubechannels'>";

				html += "		</div>\
							</div>";
 
 				var messages = [];
 				for(var i = 0; i < impresslist.emails.length; ++i) {
 					if (impresslist.emails[i].field('person_id') == this.id) {
 						messages.push( impresslist.emails[i] );
 					}
 				}
 				var lastContactBy = "N/A";
 				if (messages.length > 0) {
 					lastContactBy = impresslist.findUserById(messages[0].field('user_id')).field('forename');
 				}
				// Messages Panel
				html += "	<div role='tabpanel' class='tab-pane pady' data-tab='person_messages' > \
								<!-- <div class='pady'><b>Last contacted by:</b> " + lastContactBy + "</div> -->\
								<div id='all-messages'> \
									<div class='pady'><b>All Messages</b></div> \
									<table class='table table-striped sortable'> \
										<thead> \
											<th>Subject</th> \
											<th>Date</th> \
											<th>From</th> \
										</thead> \
										<tbody>";
										for(var i = 0; i < messages.length; i++) {
											var dateformat = new Date(messages[i].field('utime') * 1000);
											var dateformatStr = dateformat.toUTCString();

											html += "	<tr data-open-field='email' data-email-id='" + messages[i].id + "' style='cursor:pointer;'> \
															<td>" + messages[i].field('subject') + "</td> \
															<td title='" + dateformatStr + "'>" + impresslist.util.relativetime_contact(messages[i].field('utime')) + "</td> \
															<td>" + impresslist.findUserById(messages[i].field('user_id')).field('forename') + "</td> \
														</tr>";
										}
										if (messages.length == 0) {
											html += "	<tr> \
															<td colspan='3'>No Messages</td> \
														</tr>";
										}
										
				html += "				</tbody> \
									</table> \
								</div> \
								<div id='person-view-email' style='display:none;'> \
									<div class='pady'><b><a id='person-view-all-messages'>All Messages</a> </b> >&nbsp;<span data-view-email-field='subject'></span></div> \
									<table class='table table-striped sortable'> \
										<tbody> \
											<tr> \
												<th>Date:</th>\
												<td data-view-email-field='date'></td>\
											</tr> \
											<tr> \
												<th>From:</th>\
												<td data-view-email-field='from'></td>\
											</tr> \
											<tr> \
												<th>To:</th>\
												<td data-view-email-field='to'></td>\
											</tr> \
											<tr> \
												<td data-view-email-field='content' colspan='2'></td>\
											</tr> \
										</tbody> \
									</table> \
								</div> \
							</div>";



		html += "		</div>";
		html += "	</div>";


		html += "			</div>\
						</div>\
					</div>";
		$('#modals').html(html);


		// Button actions
		var person = this;
		$("#save_personId" + this.id).click(function() { person.save(); });
		$("#close_personId" + this.id).click(function() { person.close(); });
		$("#delete_personId" + this.id).click(function() { API.removePerson(person); });

		// Priority select auto-save
		$("[data-person-id='" + this.id + "'][data-input-field='priority']").change(function() {
			person.savePriority();
		});

		// Assignment select auto-save
		$("[data-person-id='" + this.id + "'][data-input-field='assignment']").change(function() {
			person.saveUserAssignment();
		});

		// Email buttons
		$('#person-email-default').click(function(e) {
			e.preventDefault();
			window.open(emailGmailLink);
		});

		// Add publication binds
		$("#add-publication-search").keyup(function() {
			var searchfield = $(this);
			var text = $(this).val().toLowerCase();
			if (text.length == 0) {
				$("#add-publication-results").html("");
				$('#add-publication-results-container').hide();
				return;
			}
			var html = "";
			for(var i = 0; i < impresslist.publications.length; i++) { 
				var include = impresslist.publications[i].search(text);
				if (include) {
					html += "	<tr class='table-list' data-publication-id='" + impresslist.publications[i].id + "' data-add-publication-result='true' >\
									<td>" + impresslist.publications[i].name + "</td> \
									<td>" + impresslist.publications[i].field('url') + "</td> \
								</tr>";
				}
			}
			if (html.length == 0) { 
				html += "	<tr>\
								<td colspan='2'>No Results</td> \
							</tr>";
			}
			$("#add-publication-results").html(html);
			$('#add-publication-results-container').show();

			$("[data-add-publication-result='true']").click(function() {
				var pubId = $(this).attr("data-publication-id");
				API.addPersonPublication(person, pubId);

				searchfield.val("");
				$("#add-publication-results").html("");
				$('#add-publication-results-container').hide();
			});

		});

		// Init publications for this person.
		for(var i = 0; i < impresslist.personPublications.length; ++i) {
			var perpub = impresslist.personPublications[i];
			if (perpub.field('person') == this.id) {
				perpub.open(); 
			}
		}

		// Add Youtube Channel binds
		$("#add-youtubechannel-search").keyup(function() {
			var searchfield = $(this);
			var text = $(this).val().toLowerCase();
			if (text.length == 0) {
				$("#add-youtubechannel-results").html("");
				$('#add-youtubechannel-results-container').hide();
				return;
			}
			var html = "";
			for(var i = 0; i < impresslist.youtubers.length; i++) { 
				var include = impresslist.youtubers[i].search(text);
				if (include) {
					html += "	<tr class='table-list' data-youtubechannel-id='" + impresslist.youtubers[i].id + "' data-add-youtubechannel-result='true' >\
									<td>" + impresslist.youtubers[i].name + "</td> \
									<td>" + impresslist.youtubers[i].field('subscribers') + "</td> \
								</tr>";
				}
			}
			if (html.length == 0) { 
				html += "	<tr>\
								<td colspan='2'>No Results</td> \
							</tr>";
			}
			$("#add-youtubechannel-results").html(html);
			$('#add-youtubechannel-results-container').show();

			$("[data-add-youtubechannel-result='true']").click(function() {
				var ytId = $(this).attr("data-youtubechannel-id");
				API.addPersonYoutubeChannel(person, ytId);

				searchfield.val("");
				$("#add-youtubechannel-results").html("");
				$('#add-youtubechannel-results-container').hide();
			});

		});

		// Init youtube channels for this person.
		for(var i = 0; i < impresslist.personYoutubeChannels.length; ++i) {
			var peryt = impresslist.personYoutubeChannels[i];
			if (peryt.field('person') == this.id) {
				peryt.open(); 
			}
		}

		// Viewing Emails
		$("#person-view-all-messages").click(function() {
			$('#person-view-email').hide();
			$('#all-messages').show();
		});
		$("[data-open-field='email']").click(function() {
			var emailId = $(this).attr('data-email-id');
			var emailObj = impresslist.findEmailById(emailId);
			var emailDate = new Date(emailObj.field('utime') * 1000).toUTCString();
			var emailSubject = emailObj.field('subject');
			if (emailSubject.length > 60) { emailSubject = emailSubject.substr(0, 60) + "..."; }
			$("[data-view-email-field='date']").html(emailDate);
			$("[data-view-email-field='subject']").html(emailSubject);
			$("[data-view-email-field='from']").html(emailObj.field('from_email'));
			$("[data-view-email-field='to']").html(emailObj.field('to_email'));
			$("[data-view-email-field='content']").html(emailObj.field('contents'));

			$('#all-messages').hide();
			$('#person-view-email').show();
		});

		// Fix tab navigation.
		$('#person_tabs a').click(function (e) {
		  e.preventDefault();
		  
		  $("#person_tabs_container .tab-content [role='tabpanel']").hide();
		  $("#person_tabs_container .tab-content [data-tab='" + $(this).attr('data-tab') + "']").addClass('active').show();
		  $(this).tab('show');
		});
		
		return html;
	}
	Person.prototype.close = function() {
		$('.person_modal').modal('hide');
	},

	Person.prototype.onAdded = function() {
		this.createTableRow();
	}
	Person.prototype.onRemoved = function() {
		this.removeTableRow();
		this.close();
	}
	Person.prototype.fullname = function() {
		return this.field("firstname") + " " + this.field("surnames");
	}
	Person.prototype.createTableRow = function() {
		var lastcontactedbytemp = this.field('lastcontactedby');
		var lastcontactedbystring = "";
		if (lastcontactedbytemp > 0) {
			var lastcontactedbyuser = impresslist.findUserById(parseInt(lastcontactedbytemp));
			lastcontactedbystring = "by <span style='color:" + lastcontactedbyuser.field('color') + "'>" + lastcontactedbyuser.field('forename') + "</span>";
		}

		var html = "	<tr data-person-id='" + this.field('id') + "' data-person-tablerow='true' class='table-list' data-toggle='modal' data-target='.person_modal'> \
							<!-- <td data-person-id='" + this.field('id') + "' data-field='id' data-value='" + this.field('id') + "'>" + this.field('id') + "</td> -->\
							<td data-value='" + this.fullname() + "'> \
								<span data-person-id='" + this.field('id') + "' data-field='name' >" + this.fullname() + "</span> \
								<div data-person-id='" + this.field('id') + "' data-field='email-list'></div> \
							</td> \
							<td data-person-id='" + this.field('id') + "' data-field='priority' data-value='" + this.priority() + "'>" + Priority.name(this.priority()) + "</td> \
							<td data-person-id='" + this.field('id') + "' data-field='twitter_followers' data-value='" + this.field('twitter_followers') + "'>" + this.twitterCell() + "</td> \
							<td data-person-id='" + this.field('id') + "' data-field='last_contacted' data-value='" + this.field('lastcontacted') + "'>" + impresslist.util.relativetime_contact(this.field('lastcontacted')) + " " + lastcontactedbystring + "</td>";
		html += "		</tr>";
		$('#people').append(html);
		
		// Events for this table row.
		var person = this;
		$(this.openSelector()).click(function() {
			person.open();
		});
	},
	Person.prototype.refreshEmails = function() {
		// Extra (hidden) rows for each e-mail address the person has.
		var emails = []
		if (this.field('email').length > 0) {
			emails.push({
				type: "person",
				typeId: this.field("id"),
				typeName: "Personal",
				person: this.field('id'),
				name: this.fullname(),
				email: this.field('email') 
			});
		}
		for(var i = 0; i < impresslist.personPublications.length; ++i) {
			if (impresslist.personPublications[i].field('person') == this.id) {
				if (impresslist.personPublications[i].field('email').length > 0) { 
					emails.push({
						type: "personPublication",
						typeId: impresslist.personPublications[i].field("id"),
						typeName: impresslist.findPublicationById( impresslist.personPublications[i].field('publication')).field("name"),
						personPublication: impresslist.personPublications[i].field('id'),
						name: this.fullname(),
						email: impresslist.personPublications[i].field('email')
					});	
				}
			}
		}
		var extraEmails = "";
		for(var i = 0; i < emails.length; i++) {
			extraEmails += "	<div data-type='person' data-person-id='" + this.field('id') + "' data-person-extra-tablerow='true' style='padding:5px;'>";
			extraEmails += "		<input \
										data-type='person' \
										data-person-id='" + this.field('id') + "' \
										data-checkbox='true' \
										data-person-checkbox='true' \
										data-mailout-name='" + emails[i]['name'] + "' \
										data-mailout-type='" + emails[i]['type'] + "' \
										data-mailout-typeid='" + emails[i]['typeId'] + "' \
										data-mailout-typename='" + emails[i]['typeName'] + "' \
										data-mailout-email='" + emails[i]['email'] + "' \
										type='checkbox' \
										value='1'/>";
			extraEmails += "		&nbsp; " + emails[i]['typeName'] + " - " + emails[i]['email'];
			extraEmails += "	</div>";
		}

		$('div[data-person-id="' + this.field('id') + '"][data-field="email-list"]').html(extraEmails);

		var person = this;
		$("input[data-person-id='" + this.field('id') + "'][data-checkbox='true']").click(function(e) { 
			impresslist.refreshMailoutRecipients(); 
			person.preventOpen();
			e.stopPropagation();
		});
	};
	Person.prototype.openSelector = function() {
		return "#people [data-person-tablerow='true'][data-person-id='" + this.id + "']";
	};
	Person.prototype.removeTableRow = function() {
		$("#people [data-person-tablerow='true'][data-person-id='" + this.id + "']").remove();

	}

	Person.prototype.filter = function(text) {
		var elementExtras = $("#people [data-person-extra-tablerow='true'][data-person-id='" + this.id + "']");
		elementExtras.hide();

		var element = $("#people [data-person-tablerow='true'][data-person-id='" + this.id + "']");
		if (this.search(text) && this.filter_isContactedByMe() && this.filter_isRecentlyContacted() && this.filter_isHighPriority() && this.filter_hasEmail() && this.filter_isAssignedToMe() && this.filter_isOutOfDate()) {
			element.show();
			if (impresslist.selectModeIsOpen) {
				elementExtras.show();
			}
			return true;
		} else {
			element.hide();
			return false;
		}
	}
	Person.prototype.filter_isRecentlyContacted = function() {
		if ($('#filter-recent-contact').is(':checked')) { 
			var contactedRecently = false;
			var len = impresslist.emails.length;
			for(var i = 0; i < len; ++i) {
				if (impresslist.emails[i].field('person_id') == this.id && 
					Number(impresslist.emails[i].field('utime')) >= (Date.now()/1000) - (86400*7)) {
					contactedRecently = true;
				}
			}
			return !contactedRecently;
		}
		return true;
	}
	Person.prototype.filter_isContactedByMe = function() {
		if ($('#filter-personal-contact').is(':checked')) { 
			var len = impresslist.emails.length;
			for(var i = 0; i < len; ++i) {
				if (impresslist.emails[i].field('person_id') == this.id && 
					impresslist.emails[i].field('user_id') == impresslist.config.user.id) {
					//console.log(i + ": " + impresslist.emails[i].field('user_id') + " matches " + impresslist.config.user.id);
					return true;
				}
			}
			return false;
		}
		return true;
	}
	Person.prototype.filter_isHighPriority = function() {
		if ($('#filter-high-priority').is(':checked')) { 
			if (this.priority() == 3) {
				return true;
			}
			var len = impresslist.personPublications.length;
			for(var i = 0; i < len; ++i) {
				if (impresslist.personPublications[i].field('person') == this.id) {
					var pub = impresslist.findPublicationById(impresslist.personPublications[i].field('publication'));
					if (pub.priority() == 3) {
						return true;
					}
				}
			}
			return false;
		}
		return true;
	}
	Person.prototype.filter_isAssignedToMe = function() {
		if ($('#filter-assigned-self').is(':checked')) { 
			//console.log(this.field('assigned'));
			//console.log(impresslist.config.user.id);
			if (this.field('assigned') == impresslist.config.user.id) {
				return true;
			}
			return false;
		}
		return true;
	}
	Person.prototype.filter_isOutOfDate = function() {
		if ($('#filter-show-outofdate').is(':checked')) { 
			if (this.field('outofdate') ==1) {
				return true;
			}
			return false;
		}
		return true;
	}
	Person.prototype.filter_hasEmail = function() {
		if ($('#filter-email-attached').is(':checked')) { 
			if (this.field('email').length > 0) {
				return true;
			}

			var len = impresslist.personPublications.length;
			for(var i = 0; i < len; ++i) {
				if (impresslist.personPublications[i].field('person') == this.id) {
					if (impresslist.personPublications[i].field('email').length > 0) { 
						return true;
					}
				}
			}
			return false;
		}
		return true;
	}
	Person.prototype.search = function(text) {
		var ret = true;

		if (text.length == 0) { return ret; }

		ret = this.fullname().toLowerCase().indexOf(text) != -1;
		if (ret) { return ret; }

		ret = this.fields['notes'].toLowerCase().indexOf(text) != -1;
		if (ret) { return ret; }

		ret = this.fields['email'].toLowerCase().indexOf(text) != -1;
		if (ret) { return ret; }

		// Search all publications too.
		var len = impresslist.personPublications.length;
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


		return false;
	}



Publication = function(data) {
	DBO.call(this, data);
}
	Publication.prototype = Object.create(DBO.prototype); 
	Publication.prototype.constructor = Publication;
	Publication.prototype.init = function(data) {
		DBO.prototype.init.call(this, data);
		this.id = parseInt(this.fields['id']);
		this.name = this.fields['name'];
		this.lastpostedon = 0;
		this.initPriorities('priorities');
	}
	
	Publication.prototype.onAdded = function() {
		this.createTableRow();
	}
	Publication.prototype.onRemoved = function() {
		this.removeTableRow();
		this.close();
	}
	Publication.prototype.open = function() {
		
		var html = "<div class='modal fade publication_modal' tabindex='-1' role='dialog'> \
						<div class='modal-dialog'> \
							<div class='modal-content' style='padding:5px;'> \
								<div style='min-height:100px;padding:20px;'>";

			html += " 				<h3 data-publication-id='" + this.id + "' data-field='name'>" + this.field('name') + "</h3> ";
			html += "				<form role='form' class='oa' onsubmit='return false;'>	\
										<div class='row'>\
											<div class='form-group  col-md-8'>\
												<label for='name'>Name:&nbsp; </label> \
												<input data-publication-id='" + this.id + "' data-input-field='name' class='form-control' type='text' value='" + this.field('name') + "' /> \
											</div>\
											<div class='form-group col-md-4'>\
												<label for='email'>Priority:</label>";
												var priority = this.priority();
												html += "	<select data-publication-id='" + this.id + "' data-input-field='priority' class='form-control'>\
																<option value='3' " + ((priority==3)?"selected='true'":"") + ">High</option>\
																<option value='2' " + ((priority==2)?"selected='true'":"") + ">Medium</option>\
																<option value='1' " + ((priority==1)?"selected='true'":"") + ">Low</option>\
																<option value='0' " + ((priority==0)?"selected='true'":"") + ">N/A</option>\
															</select>";
			html += "						</div>\
										</div>\
										<div class='form-group'>\
											<label for='url'>URL:&nbsp; </label> \
											<input data-publication-id='" + this.id + "' data-input-field='url' class='form-control' type='text' value='" + this.field('url') + "' />\
										</div>\
										<div class='form-group'>\
											<label for='rssfeedurl'>RSS Feed URL:&nbsp; </label> \
											<input data-publication-id='" + this.id + "' data-input-field='rssfeedurl' class='form-control' type='text' value='" + this.field('rssfeedurl') + "' />\
										</div>\
										<div class='form-group'>\
											<label for='email'>Twitter Username:&nbsp; </label> \
											<input data-publication-id='" + this.id + "' data-input-field='twitter' class='form-control' type='text' value='" + this.field('twitter') + "' />\
										</div>\
										<div class='form-group'>\
											<label for='email'>Notes:&nbsp; </label> \
											<textarea data-publication-id='" + this.id + "' data-input-field='notes' class='form-control' style='height:100px;'>" + this.field('notes') + "</textarea>\
										</div>\
										<div class='fl'> \
											<button id='save_publicationId" + this.id + "' type='submit' class='btn btn-primary'>Save</button>";
			html += "						&nbsp;<button id='close_publicationId" + this.id + "' type='submit' class='btn btn-default'>Close</button>";
			html += " 					</div><div class='fr'> \
											<button id='delete_publicationId" + this.id + "' type='submit' class='btn btn-danger'>Remove</button> \
										</div>\
									</form>";

		html += "				</div>\
							</div>\
						</div>\
					</div>";
		$('#modals').html(html);

		var publication = this;
		$("#save_publicationId" + this.id).click(function() { publication.save(); });
		$("#close_publicationId" + this.id).click(function() { publication.close(); });
		$("#delete_publicationId" + this.id).click(function() { API.removePublication(publication); });

		// Priority select auto-save
		$("[data-publication-id='" + this.id + "'][data-input-field='priority']").change(function() {
			publication.savePriority();
		});
	}
	Publication.prototype.save = function() {
		var name = $("[data-publication-id=" + this.id + "][data-input-field='name']").val();
		var url = $("[data-publication-id=" + this.id + "][data-input-field='url']").val();
		var rssfeedurl = $("[data-publication-id=" + this.id + "][data-input-field='rssfeedurl']").val();
		var twitter = $("[data-publication-id=" + this.id + "][data-input-field='twitter']").val();
		var notes = $("[data-publication-id=" + this.id + "][data-input-field='notes']").val();
		
		API.savePublication(this, name, url, rssfeedurl, twitter, notes);
	}
	Publication.prototype.savePriority = function() {
		var priority = $("[data-publication-id='" + this.id + "'][data-input-field='priority']").val();
		API.setPublicationPriority(this, priority, impresslist.config.user.game);
	}

	Publication.prototype.update = function() {
		$("[data-publication-id='" + this.id + "'][data-field='name']").html(this.name);
		$("[data-publication-id='" + this.id + "'][data-field='priority']").html(Priority.name(this.priority()));
		$("[data-publication-id='" + this.id + "'][data-field='url']").html(this.field('url'));
		$("[data-publication-id='" + this.id + "'][data-field='twitter']").html(this.field('twitter'));		
		$("[data-publication-id='" + this.id + "'][data-field='twitter_followers']").html( this.twitterCell() );		
	}

	Publication.prototype.close = function() {
		//$('#modals').html("");
		$('.publication_modal').modal('hide');
	},
	Publication.prototype.createTableRow = function() {
		var html = "	<tr data-publication-tablerow='true' data-publication-id='" + this.id + "' class='table-list' data-toggle='modal' data-target='.publication_modal'> \
							<!-- <td data-publication-id='" + this.field('id') + "' data-field='id' 				data-value='" + this.field('id')+ "' >" + this.field('id') + "</td> --> \
							<td data-publication-id='" + this.field('id') + "' data-field='name' 				data-value='" + this.field('name')+ "' >" + this.icon() + this.field('name') + "</td> \
							<td data-publication-id='" + this.field('id') + "' data-field='priority' 			data-value='" + this.priority() + "'>" + Priority.name(this.priority()) + "</td> \
							<td data-publication-id='" + this.field('id') + "' data-field='url' 				data-value='" + this.field('url')+ "'><a href='" + this.field('url') + "' target='new'>" + this.field('url') + "</a></td> \
							<td data-publication-id='" + this.field('id') + "' data-field='twitter_followers' 	data-value='" + this.field('twitter_followers')+ "'>" + this.twitterCell() + "</td> \
							<td data-publication-id='" + this.field('id') + "' data-field='lastpostedon' 		data-value='" + this.field('lastpostedon') + "'>" + impresslist.util.relativetime_contact(this.field('lastpostedon')) + "</td> \
						</tr>";
		$('#publications').append(html);

		var publication = this;
		$(this.openSelector()).click(function() {
			publication.open();
		});
	}
	Publication.prototype.openSelector = function() {
		return "#publications [data-publication-tablerow='true'][data-publication-id='" + this.id + "']";
	};
	Publication.prototype.removeTableRow = function() {
		$("#publications [data-publication-tablerow='true'][data-publication-id='" + this.id + "']").remove();
	}
	Publication.prototype.icon = function() {
		//if (this.field("iconurl").length > 0) {
		//	return "<img src='" + this.field("iconurl") + "' width=16 height=16/> ";
		//}
		return "";
	}
	/*Publication.prototype.filter = function(text) {
		if (this.search(text)) {
			$('#publication_id' + this.id).show();
			return true;
		} else {
			$('#publication_id' + this.id).hide();
			return false;
		}
	}*/
	Publication.prototype.filter = function(text) {
		var element = $("#publications [data-publication-tablerow='true'][data-publication-id='" + this.id + "']");
		if (this.search(text) && this.filter_isHighPriority()) { // && this.isContactedByMe() && this.isRecentlyContacted()) {
			element.show();
			return true;
		} else {
			element.hide();
			return false;
		}
	}
	Publication.prototype.filter_isHighPriority = function() {
		if ($('#filter-high-priority').is(':checked')) { 
			return (this.priority() == 3);
		}
		return true;
	}
	Publication.prototype.search = function(text) {
		var ret = true;

		if (text.length == 0) { return ret; }

		ret = this.name.toLowerCase().indexOf(text) != -1;
		if (ret) { return ret; }

		ret = this.fields['notes'].toLowerCase().indexOf(text) != -1;
		if (ret) { return ret; }
		
		ret = this.fields['url'].toLowerCase().indexOf(text) != -1;
		if (ret) { return ret; }

		ret = this.fields['twitter'].toLowerCase().indexOf(text) != -1;
		if (ret) { return ret; }

		return false;
	}

OAuthTwitterAccount = function(data) {
	DBO.call(this, data); 
}
	OAuthTwitterAccount.prototype = Object.create(DBO.prototype)
	OAuthTwitterAccount.prototype.constructor = OAuthTwitterAccount;
	OAuthTwitterAccount.prototype.init = function(data) {
		DBO.prototype.init.call(this, data);
		this.id = parseInt(this.field('id'));
	}
	OAuthTwitterAccount.prototype.onAdded = function(fromInit) {
		this.createItem(fromInit);
		$('#social-homepage-twitteracc-list-none').hide();
		if (!fromInit) {
			$('#social-addtwitteraccount-cancel').click();
		}
	}
	OAuthTwitterAccount.prototype.onRemoved = function() {
		this.removeItem();

		if (impresslist.oauthTwitterAccounts.length - 1 == 0) {
			$('#social-homepage-twitteracc-list-none').show();
		}
	}
	OAuthTwitterAccount.prototype.createItem = function(fromInit) {
		var html = "";
		html += "<p id='social-twitteracc-" + this.id + "'>";
		html += "	<img src='images/email-twitter.png' /> <a href='http://twitter.com/" + this.field("twitter_name") + "'>@" + this.field("twitter_name") + "</a>";
		html += "	<button id='social-remove-twitteracc-" + this.id + "' class='btn btn-sm btn-danger fr'>X</button>";
		html += "</p>";
		$('#social-homepage-twitteracc-list').append(html);
		this.update();
	}
	OAuthTwitterAccount.prototype.removeItem = function() {
		$("#social-twitteracc-" + this.id).remove();
	}
	OAuthTwitterAccount.prototype.update = function() {
		var th = this;
		$("#social-remove-twitteracc-" + this.id).click(function(){
			API.removeOAuthTwitterAccount(th);
		});
	}

var SocialUploadID = 0;
SocialUpload = function(data) {
	DBO.call(this, data); 
}
	SocialUpload.prototype = Object.create(DBO.prototype)
	SocialUpload.prototype.constructor = SocialUpload;
	SocialUpload.prototype.init = function(data) {
		DBO.prototype.init.call(this, data);
		this.id = SocialUploadID;
		SocialUploadID++;
	}
	SocialUpload.prototype.onAdded = function(fromInit) {
		this.createItem(fromInit);
		$('#socialUploads-list-loading').hide();
		$('#social-uploads-list-none').hide();
	}
	SocialUpload.prototype.onRemoved = function() {
		this.removeItem();

		if (impresslist.socialUploads.length - 1 == 0) {
			$('#social-uploads-list-none').show();
		}
	}
	SocialUpload.prototype.createItem = function(fromInit) {
		var html = "";
		html += "<div id='social-upload-" + this.id + "' class='socialqueue-item' style='padding:0px; float:left;margin-right:5px;width:149px;'>";
		html += "	<button id='social-remove-upload-" + this.id + "' class='btn btn-sm btn-danger fr' style='border-radius:0;'>X</button>";
		html += "	<a href='images/uploads/" + this.field("name") + "' target='new'><img style='padding:0px;width:100%;' src='images/uploads/" + this.field("name") + "' /></a>";
		
		html += "</div>";
		$('#social-uploads-list').prepend(html);
		this.update();
	}
	SocialUpload.prototype.removeItem = function() {
		$("#social-upload-" + this.id).remove();
	}
	SocialUpload.prototype.update = function() {
		var th = this;
		$("#social-remove-upload-" + this.id).click(function(){
			API.removeSocialUpload(th);
		});
	}


SocialTimelineItem = function(data) {
	DBO.call(this, data); 
}
	SocialTimelineItem.prototype = Object.create(DBO.prototype)
	SocialTimelineItem.prototype.constructor = SocialTimelineItem;

	SocialTimelineItem.displaySort = function() {
		var $wrapper = $('#social-timeline');

		$wrapper.find('.socialqueue-item-container').sort(function(a, b) {
		    return +a.getAttribute('data-timestamp') - +b.getAttribute('data-timestamp');
		})
		.appendTo($wrapper);
	}

	SocialTimelineItem.prototype.init = function(data) {
		DBO.prototype.init.call(this, data);
		this.id = parseInt(data.id);
	}
	SocialTimelineItem.prototype.onAdded = function(fromInit) {
		this.createItem(fromInit);
		$('#social-timeline-loading').hide();
		$('#social-timeline-none').hide();

		SocialTimelineItem.displaySort();
	}
	SocialTimelineItem.prototype.onRemoved = function() {
		this.removeItem();

		if (impresslist.socialUploads.length - 1 == 0) {
			$('#social-uploads-list-none').show();
		}
	}
	SocialTimelineItem.prototype.createItem = function(fromInit) {
		var html = "";

		html += "<div class='socialqueue-item-container' data-timestamp='" + this.field('timestamp') + "' data-social-id='" + this.id + "' >";
		html += "</div>";

		$('#social-timeline').append(html);
		this.updateRow();
		this.update();
	}
	SocialTimelineItem.prototype.removeItem = function() {
		//$("#social-upload-" + this.id).remove();
		$(".socialqueue-item-container[data-social-id='" + this.id + "']").remove();
		this.close();
	}
	SocialTimelineItem.prototype.openShareDialog = function() {
		var html = "<div class='modal fade socialShare_modal' tabindex='-1' role='dialog'> \
						<div class='modal-dialog' style='width:400px;margin-top:20%'> \
							<div class='modal-content' style='width:400px;'> \
								<div style='min-height:100px;padding:20px;'>";

		html += "	<h3>Add Retweets</h3>";
		html += "	<div class='form-group'>\
						<label for='type'>Time Separation:&nbsp; </label> \
						<select id='social-sharemodal-retweet-timesep' class='form-control'> \
							<option value='3600'>1 hr</option>\
							<option value='7200'>2 hrs</option>\
							<option value='21600'>6 hrs</option>\
							<option value='43200'>12 hrs</option>\
							<option value='86400'>24 hrs</option>\
						</select>\
					</div>";

		html += "	<div class='form-group'>\
						<label for='accounts'>Accounts:&nbsp; </label>\
						<select id='social-sharemodal-retweet-accounts' multiple='multiple'>";

							for(var i = 0; i < impresslist.oauthTwitterAccounts.length; i++) 
							{
								var tw = impresslist.oauthTwitterAccounts[i];
								console.log("upid:" + tw.id);
								console.log(this.field('typedata').account);

								if (tw.id == parseInt(this.field('typedata').account)) { 
									continue; 
								}

								var hasRetweetScheduled = false;
								for(var j = 0; j < impresslist.socialTimelineItems.length; j++) {
									var sti = impresslist.socialTimelineItems[j];
									if (sti.field('type') == 'retweet' &&
										parseInt(sti.field('typedata').tweet) == this.id && 
										parseInt(sti.field('typedata').account) == tw.id
										) {
										hasRetweetScheduled = true;
									}
								}

								if (!hasRetweetScheduled) { 

									//var selectedText = (hasRetweetScheduled)?"selected='true'":"";
									var selectedText = "";
									html += "<option value='" + tw.field('id') + "' " + selectedText + ">" + tw.field('twitter_handle') + "</option>";
								}
							}
		html += "		</select>\
					</div>";

		html += "	<div class='oa'> \
						<div class='fl'> \
							<button id='social-add-retweets-" + this.id + "' type='submit' class='btn btn-success' style='margin-right:5px;'>Add</button> \
						</div>\
						<div class='fr'> \
							<button id='social-close-retweets-" + this.id + "' type='submit' class='btn btn-default'>Close</button> \
						</div> \
					</div>";
		
		html += "				</div> \
							</div> \
						</div> \
					</div>";
		$('#modals').html(html);

		var th = this;
		$('#social-sharemodal-retweet-accounts').multiselect({
			enableFiltering: true,
			includeSelectAllOption: true,
			enableCaseInsensitiveFiltering: true
		});
		$("#social-add-retweets-" + this.id).click(function() {
			// ... 
			var timeSeparation = $('#social-sharemodal-retweet-timesep').val();
			var selectedAccountsNodes = $('#social-sharemodal-retweet-accounts option:selected');//.map(function(a, item){return item.value;});
			var selectedAccounts = "";
			for(var i = 0; i < selectedAccountsNodes.length; i++) {
				selectedAccounts += selectedAccountsNodes[i].value;
				if (i < selectedAccountsNodes.length - 1) {
					selectedAccounts += ",";
				}
			}
			console.log(timeSeparation);
			console.log(selectedAccountsNodes);
			console.log(selectedAccounts);

			API.addSocialTimelineItemRetweets(th, selectedAccounts, timeSeparation, function() {
				th.closeShareDialog();
				SocialTimelineItem.displaySort();
			}); 
		});
		$("#social-close-retweets-" + this.id).click(function() { th.closeShareDialog(); });
	}
	SocialTimelineItem.prototype.closeShareDialog = function() {
		$('.socialShare_modal').modal('hide');
	}
	SocialTimelineItem.prototype.open = function() {
		console.log('open');
		var html = "<div class='modal fade social_modal' tabindex='-1' role='dialog'> \
						<div class='modal-dialog'> \
							<div class='modal-content'> \
								<div style='min-height:100px;padding:20px;'>";
					
		html += "	<h3>Scheduled Item</h3>";
		html += "	<div class='row'>\
						<div class='col-sm-6'>\
							<div class='form-group'>\
								<label for='type'>Type:&nbsp; </label> \
								<select id='social-modal-type'  class='form-control'> \
									<option value='blank' " + ((this.field('type')=='blank')?"selected='true'":"") + ">Blank</option>\
									<option value='tweet' " + ((this.field('type')=='tweet')?"selected='true'":"") + ">Tweet</option>\
									<option value='retweet' " + ((this.field('type')=='retweet')?"selected='true'":"") + ">Retweet</option>\
									<!-- <option value='facebookpost'>Facebook Post</option> -->\
								</select>\
							</div>\
						</div>\
						<div class='col-sm-6'>\
							<div class='form-group'>\
								<label for='account'>Account:&nbsp; </label> \
								<select id='social-modal-account'  class='form-control'>";
									for(var i = 0; i < impresslist.oauthTwitterAccounts.length; i++) {
										var tw = impresslist.oauthTwitterAccounts[i];
										html += "<option value='" + tw.field("id") + "' " + ((this.field('typedata').account==tw.id)?"selected='true'":"") + " >@" + tw.field("twitter_handle") + "</option>";
									}
		html += "				</select>\
							</div>\
						</div>\
					</div>";

		html += "	<div class='form-group'> \
						<label for='timepicker'>Date &amp; Time:</label>\
						<div class='input-group date' id='social-modal-timepicker'>\
							<input id='social-modal-edit-timestamp' type='text' class='form-control' />\
							<span class='input-group-addon'>\
								<span class='glyphicon glyphicon-calendar'></span>\
							</span>\
						</div>\
					</div>";



			// Blank
			html += "	<div class='social-modal-blank' style='display:none;'>";
			html += "		<p>Please select a type...</p>";
			html += "	</div>"
			// Tweet
			html += "	<div class='social-modal-tweet' style='display:none;'>";
			html += "		<div class='form-group'>\
								<label for='message'>Message: (<span id='social-modal-tweet-charsleft'>140</span> characters remaining)&nbsp; </label> \
								<textarea id='social-modal-tweet-message' data-socialitem-id='" + this.id + "' class='form-control' style='height:100px;'>" + ((this.field('typedata').message)?this.field('typedata').message:"") + "</textarea>\
							</div>";
			html += "		<div class='form-group'>\
								<label for='attachments'>Attachments:&nbsp; </label>\
								<select id='social-modal-tweet-attachments' multiple='multiple'>";
									var attachments = this.field('typedata').attachments;
								
									for(var i = 0; i < impresslist.socialUploads.length; ++i) {
										var up = impresslist.socialUploads[i];
										var hasAttachment = false;
										if (attachments) {
											for(var j = 0; j < attachments.length; j++) {
												if (up.field('name') == attachments[j].file) {
													hasAttachment = true;
												}
											}
										}
										
										var selectedText = (hasAttachment)?"selected='true'":"";
										html += "<option value='" + up.field('name') + "' " + selectedText + ">" + up.field('name') + "</option>";
									}
								//}
			html += "			</select>\
							</div> \
							<div class='social-modal-tweet-attachment-preview-container' style='margin-bottom:15px;'></div>\
						</div>";

			// Retweet
			html += "	<div class='social-modal-retweet' style='display:none;'> \
							<div class='form-group'>\
								<label for='account'>Tweet:&nbsp; </label> \
								<select id='social-modal-retweet-select'  class='form-control'>";
									for(var i = 0; i < impresslist.socialTimelineItems.length; i++) {
										var it = impresslist.socialTimelineItems[i];
										if (it.field('type') == 'tweet') { 
											html += "<option value='" + it.field("id") + "' " + ((this.field('typedata').tweet==it.id)?"selected='true'":"") + " >@" + impresslist.findOAuthTwitterAccountById(parseInt(it.field('typedata').account)).field('twitter_handle') + " " + it.field('typedata').message + "</option>";
										}
									}
			html += "			</select>\
							</div> \
						</div>";

		html += "	<div class='form-group'> \
						<label class='checkbox-inline'><input id='social-modal-edit-ready' type='checkbox' " + (((this.field('ready')==1)?"checked":"")) + "><b>Ready to send?</b></label>\
					</div>";

		// Save, close, remove.
		html += "		<div class='oa'> \
							<div class='fl'> \
								<button id='save_socialitemId" + this.id + "' type='submit' class='btn btn-primary' style='margin-right:5px;'>Save</button> \
								<button id='close_socialitemId" + this.id + "' type='submit' class='btn btn-default'>Close</button> \
				 				</div>\
							<div class='fr'> \
								<button id='delete_socialitemId" + this.id + "' type='submit' class='btn btn-danger'>Remove</button> \
							</div> \
						</div>";
	


		html += "				</div> \
							</div> \
						</div> \
					</div>";
		$('#modals').html(html);

		// time
		var utime = this.field('timestamp');
		if (utime == 0) {
			//utime = Date.now() / 1000;
		}
		$('#social-modal-timepicker').datetimepicker();
		$('#social-modal-timepicker').data("DateTimePicker").defaultDate(moment(utime, "X"));
		$('#social-modal-timepicker').data("DateTimePicker").format("MMMM Do YYYY HH:mm zz");
		
		var th = this;
		var onAttachmentsChanged = function() {
			var selectedOptions = $('#social-modal-tweet-attachments option:selected');
			var selectedOptionValues = $('#social-modal-tweet-attachments option:selected').map(function(a, item){return item.value;});
        	var html = "";    
        	for(var i = 0; i < selectedOptionValues.length; i++) {
        		html += "<img src='images/uploads/" + selectedOptionValues[i] + "' style='width:100px;height:100px;margin-right:5px;'/>";
        	}
            $('.social-modal-tweet-attachment-preview-container').html(html);
            th.testAttachments = selectedOptionValues.length;
            th.updateTwitterCharactersLeft();
		}
		$('#social-modal-tweet-attachments').multiselect({
			enableFiltering: true,
			//includeSelectAllOption: true,
			enableCaseInsensitiveFiltering: true,
			onSelectAll: onAttachmentsChanged,
			onChange: onAttachmentsChanged
		});

		var onTypeChanged = function(ty) {
			$('.social-modal-blank').hide();
			$('.social-modal-tweet').hide();
			$('.social-modal-retweet').hide();
			if (ty == 'tweet') {
				onAttachmentsChanged();
				$('.social-modal-tweet').show();
			} else if (ty == 'retweet') {
				$('.social-modal-retweet').show();
			}
		}
		onTypeChanged(this.field('type'));

		// update twitlen
		th.updateTwitterCharactersLeft();
		$('#social-modal-tweet-message').on("change keyup paste", function() {
			th.updateTwitterCharactersLeft();	
		});

		
		$('#social-modal-type').change(function() {
			var vl = $(this).val();
			onTypeChanged(vl);
		});

		// ok/close/delete
		$("#save_socialitemId" + this.id).click(function() { th.save(); });
		$("#close_socialitemId" + this.id).click(function() { th.close(); });
		$("#delete_socialitemId" + this.id).click(function() { API.removeSocialTimelineItem(th); });

		this.setFormEnabled(true);
		if (//utime != 0 && Date.now() / 1000 > utime && 
			this.field('sent') == 1) {
			this.setFormEnabled(false);
			$('#save_socialitemId'+this.id).hide();
			$('#delete_socialitemId'+this.id).hide();
		}
		
	}
	SocialTimelineItem.prototype.updateRow = function() {
		var html = "";
		var acc = impresslist.findOAuthTwitterAccountById(parseInt(this.field('typedata').account));
		var thedate = moment(this.field('timestamp'), "X").format("MMMM Do YYYY HH:mm zz");
		var reldate = impresslist.util.relativetime(this.field('timestamp'));
		if (this.field("type") == "tweet") {
			html += "	<div class='socialqueue-item " + (this.field('ready')==1?"ready":"") + "'> \
							<div class='oa'> \
								<p class='fl'><b>Tweet: <a href='http://twitter.com/" + acc.field('twitter_handle') + "'>@" + acc.field('twitter_handle') + "</a></b></p>";
			html += "			<p class='fr text-muted' style='margin-left:10px;'> <a data-socialitem-id='" + this.id + "' data-toggle='modal' data-target='.social_modal'    style='cursor:pointer' title='Edit'><i class='glyphicon glyphicon-pencil'></i></a></p>";
			if (this.field('ready')==1) { 
				html += "		<p class='fr text-muted' style='margin-left:10px;'> <a class='social-timeline-opensharedialog' data-socialitem-id='" + this.id + "' data-toggle='modal' data-target='.socialShare_modal' style='cursor:pointer' title='Add Shares'><i class='glyphicon glyphicon-plus'></i></a></p>";
			}
			html += "			<p class='fr text-muted'><i class='glyphicon glyphicon-time'></i> <span data-social-id='" + this.id + "' data-toggle='tooltip' data-placement='left' title='" + reldate + "'>" + thedate + "</span></p> \
							</div> \
							<div> \
								<img class='icon' src='images/email-twitter.png'/> \
								<p style='min-height:40px;'>" + this.field('typedata').message + "</p>";
					
								var attachments = this.field('typedata').attachments;
								if (attachments.length > 0) { 
									html += "<p class='text-muted'>Attachment/s: " + attachments.length + " <i class='glyphicon glyphicon-picture'></i></p>";
									for(var i = 0; i < attachments.length; i++) { 
										html += "<p class='socialqueue-imageattachment-container' data-social-id='" + this.id + "'  data-attachment-id='" + i + "'><img class='socialqueue-imageattachment' data-social-id='" + this.id + "' data-attachment-id='" + i + "' src='images/uploads/" + attachments[i].file + "'/></p>";
									}
								}

			html += "		</div> \
						</div>";
		} else if (this.field("type") == "retweet") {
			html += "	<div class='socialqueue-item indented thinnest " + (this.field('ready')==1?"ready":"") + "'> \
							<div class='oa'> \
								<img class='icon' src='images/email-twitter.png'/> \
								<p class='fl'><b>Retweet by <a href='http://twitter.com/" + acc.field('twitter_handle') + "'>@" + acc.field('twitter_handle') + "</a></b></p> \
								<p class='fr text-muted' style='margin-left:10px;'> <a data-socialitem-id='" + this.id + "' data-toggle='modal' data-target='.social_modal' style='cursor:pointer'><i class='glyphicon glyphicon-pencil'></i></a></p> \
								<p class='fr text-muted'><i class='glyphicon glyphicon-time'></i> <span data-social-id='" + this.id + "' data-toggle='tooltip' data-placement='left' title='" + reldate + "'>" + thedate + "</span> </p> \
							</div> \
						</div>";
		} else if (this.field("type") == "blank") {
			html += "	<div class='socialqueue-item thinnest " + (this.field('ready')==1?"ready":"") + "'> \
							<div class='oa'> \
								<p class='fl'><b>Blank </b></p> \
								<p class='fr text-muted' style='margin-left:10px;'> <a data-socialitem-id='" + this.id + "' data-toggle='modal' data-target='.social_modal' style='cursor:pointer'><i class='glyphicon glyphicon-pencil'></i></a></p> \
							</div> \
						</div>";
		}
		$('.socialqueue-item-container[data-social-id=' + this.id + ']').html(html);

		$('.socialqueue-item-container[data-social-id=' + this.id + ']').attr("data-timestamp", this.field('timestamp'));
		

		/*$("[data-custom-toggle='social-add-shares'").unbind("click");
		$("[data-custom-toggle='social-add-shares'").click(function() {
			//alert('lol');
		});*/
	}
	SocialTimelineItem.prototype.update = function() {
		var th = this;
		$('.socialqueue-imageattachment').click(function(){ 
			var attid = $(this).attr('data-attachment-id');
			console.log(attid);

			var containerSelector = '.socialqueue-imageattachment-container[data-social-id=' + th.id + '][data-attachment-id='+attid+']';
			var imageSelector = '.socialqueue-imageattachment[data-social-id=' + th.id + '][data-attachment-id='+attid+']';

			var h = $(containerSelector).css('height');
			if (h == '100px') { 
				$(containerSelector).css('height', '100%'); 
				$(imageSelector).css('margin-top', '0px'); 
			} else {
				$(containerSelector).css('height', '100px'); 
				$(imageSelector).css('margin-top', '-25%'); 
			}
		});

		$('[data-social-id=' + this.id + '][data-toggle="tooltip"]').tooltip(); 
		
		$(this.openSelector()).click(function(){
			th.open();
		});
		$(this.openShareSelector()).click(function(){
			th.openShareDialog();
		});
	}
	SocialTimelineItem.prototype.openSelector = function() {
		return "#social-timeline a[data-socialitem-id='" + this.id + "']";
	}
	SocialTimelineItem.prototype.openShareSelector = function() {
		return ".social-timeline-opensharedialog[data-socialitem-id='" + this.id + "']";
	}

	SocialTimelineItem.prototype.close = function() {
		$('.social_modal').modal('hide');
	}
	SocialTimelineItem.prototype.save = function() {
		var type = $("#social-modal-type").val();
		console.log('type: ' + type);

		var date = new Date($('#social-modal-timepicker').data("DateTimePicker").date());
		var timestamp = Math.floor(date.getTime() / 1000);
		var ready = $('#social-modal-edit-ready').is(":checked");

		if (type == 'tweet') {
			var account = $("#social-modal-account").val();
			var typedata = { 
				message: $('#social-modal-tweet-message').val(),
				account: account,
				attachments: []
			};

			var selectedOptionValues = $('#social-modal-tweet-attachments option:selected').map(function(a, item){return item.value;});
			for(var i = 0; i < selectedOptionValues.length; i++) {
				typedata.attachments.push({
					"type": "image",
					"file": selectedOptionValues[i]
				});
			}
			
			API.saveSocialTimelineItem(this, type, typedata, timestamp, ready);
		}
		else if (type == 'retweet') {
			var account = $("#social-modal-account").val();
			var tw = $('#social-modal-retweet-select').val();
			var typedata = {
				tweet: 1,
				account: account
			};
			API.saveSocialTimelineItem(this, type, typedata, timestamp, ready);
		} 
		else if (type == 'blank') {
			var typedata = {};
			API.saveSocialTimelineItem(this, type, typedata, timestamp, ready);
		}
		//var email   = $("[data-youtuber-id=" + this.id + "][data-input-field='email']").val();
		//var twitter = $("[data-youtuber-id=" + this.id + "][data-input-field='twitter']").val();
		//var notes   = $("[data-youtuber-id=" + this.id + "][data-input-field='notes']").val();
		
		
	}

	SocialTimelineItem.prototype.updateTwitterCharactersLeft = function() {
		var msg = $('#social-modal-tweet-message').val();
		var remaining = 140;
		remaining -= twttr.txt.getTweetLength(msg, {
			short_url_length: impresslist.config.system.twitter.short_url_length,
			short_url_length_https: impresslist.config.system.twitter.short_url_length_https
      	});

		remaining -= Math.min(1,this.testAttachments) * impresslist.config.system.twitter.characters_reserved_per_media;
		$('#social-modal-tweet-charsleft').html( remaining );
	}

	SocialTimelineItem.prototype.setFormEnabled = function(boo) {
		boo = !boo;
		$('#social-modal-type').attr("disabled", boo); 
		$('#social-modal-account').attr("disabled", boo);
		$('#social-modal-edit-timestamp').attr("disabled", boo);
		$('#social-modal-tweet-message').attr("disabled", boo);
		$('#social-modal-tweet-attachments').attr("disabled", boo);
		//$('#social-modal-tweet-attachments').attr("disabled", boo);
		$('#social-modal-retweet-select').attr("disabled", boo);
		$('#social-modal-edit-ready').attr("disabled", boo);
		$('#save_socialitemId'+this.id).attr("disabled", boo);
		$('#delete_socialitemId'+this.id).attr("disabled", boo);
		
	}
	


var impresslist = {
	config: {
		system: {
			email: "",
			backups: {
				lastmanual: 0
			}
		},
		user: {
			id: 0,
			game: 0,
			gmail: 0,
			imapServer: '',
			smtpServer: ''
		}
	},
	people: [],
	publications: [],
	personPublications: [],
	personYoutubeChannels: [],
	youtubers: [],
	emails: [],
	users: [],
	games: [],
	coverage: [],
	simpleMailouts: [],
	oauthTwitterAccounts: [],
	socialUploads: [],
	socialTimelineItems: [],
	loading: {
		people: false,
		publications: false,
		personPublications: false,
		personYoutubeChannels: false,
		youtubeChannels: false,
		emails: false,
		users: false,
		games: false,
		coverage: false,
		socialUploads: false,
		socialTimelineItems: false,
		set: function(type, b) {
			impresslist.loading[type] = b;

			$('#' + type + '-loading-' + b).show();
			$('#' + type + '-loading-' + (!b)).hide();
		},
		isLoading: function(t) {
			return impresslist.loading[t];
		}
	},
	init: function() {
		//API.listJobs();
		API.listPeople();
		API.listPublications();
		API.listPersonPublications();
		API.listPersonYoutubeChannels();
		API.listYoutubeChannels();
		API.listEmails();
		API.listSimpleMailouts();
		API.listOAuthTwitterAccounts();
		API.listSocialTimeline();
		API.listSocialUploads();
		

		// Navigation links
		var thiz = this;
		$('#nav-add-person').click(API.addPerson);
		$('#nav-add-publication').click(API.addPublication);
		$('#nav-add-youtuber').click(API.addYoutuber);
		$('#nav-add-coverage-publication').click(API.addPublicationCoverage);
		$('#nav-add-coverage-youtuber').click(API.addYoutuberCoverage);
		$('#nav-add-simplemailout').click(API.addSimpleMailout);
		$('#nav-user-changepassword').click(function() { thiz.findUserById(thiz.config.user.id).openChangePassword(); });
		$('#nav-user-changeimapsettings').click(function() { thiz.findUserById(thiz.config.user.id).openChangeIMAPSettings(); });
		$('#nav-home').click(this.changePage);
		$('#nav-coverage').click(this.changePage);
		$('#nav-keys').click(this.changePage);
		$('#nav-social').click(this.changePage);
		$('#nav-mailout').click(this.changePage);
		$('#nav-mailout-addrecipients').click(this.changePage);
		$('#nav-admin').click(this.changePage);
		$('#nav-importtool').click(this.changePage);
		$('#nav-help').click(this.changePage);
		$('#nav-feedback').attr("href", impresslist.util.mailtoClient("ashley@forceofhab.it", "impresslist feedback", ""));
		$('#sql-query-submit').click(function() { API.sqlQuery( $('#sql-query-text').val() ); });

		// Dynamic bits.
		$('#current-user-name').html(this.findUserById(this.config.user.id).fullname());
		$('#current-project-name').html(this.findGameById(this.config.user.game).field('name'));
		$("[data-last-backup='true']").html(new Date(this.config.system.backups.lastmanual*1000).toUTCString());

		// Set up search / filter
		$('#filter').keyup(this.refreshFilter);
		$('#filter-recent-contact').change(this.refreshFilter);
		$('#filter-personal-contact').change(this.refreshFilter);
		$('#filter-high-priority').change(this.refreshFilter);
		$('#filter-email-attached').change(this.refreshFilter);
		$('#filter-assigned-self').change(this.refreshFilter);
		$('#filter-show-outofdate').change(this.refreshFilter);

		// Import tool
		$('#importtool-fieldselect').change(function() {
			var val = $(this).val();
			if (val == '---') { return; }
			var html = "<div class='tag' data-tag='" + val + "'>" + val + " &nbsp; <a href='javascript:;' data-remove-tag='" + val + "'>x</a></div>";
			$('#importtool-order').append(html);
			$('#importtool-fieldselect').val('---');

			$('a[data-remove-tag="'+ val +'"]').click(function(){
				$('.tag[data-tag="'+ val +'"]').remove();
			});
		});
		$('#importtool-submit').click(function() {

			var importtool_disableForm = function(boo) {
				$('#importtool-type-csv').attr('disabled', boo);
				$('#importtool-type-tsv').attr('disabled', boo);
				$('#importtool-maintext').attr("disabled", boo);
				$('#importtool-fieldselect').attr("disabled", boo);
				//$('#importtool-selectorder').attr("disabled", boo);
				$('#importtool-submit').attr("disabled", boo);
				$('.bootstrap-tagsinput').attr('disabled',boo);
			}
			importtool_disableForm(true);
			
			var importString = $('#importtool-maintext').val();

			var importType = $('input[name="importtool-type"]:checked').val();
			if (importType == undefined) { 
				importtool_disableForm(false); 
				API.errorMessage("Invalid import type. Please select CSV or TSV."); 
				return; 
			}
			console.log(importType);

			var importOrder = []
			var importTags = $('#importtool-order .tag');
			for(var i = 0; i < importTags.length; i++) {
				importOrder.push( $(importTags[i]).attr('data-tag') );
			}
			console.log(importOrder);

			var url = "api.php?endpoint=/import/";
			url += "&data=" + encodeURIComponent(importString);
			url += "&type=" + encodeURIComponent(importType);
			url += "&order=" + encodeURIComponent(importOrder.join());
			$.ajax( url )
				.done(function(result) {
					importtool_disableForm(false);
					if (result.substr(0, 1) != '{') { 
						API.errorMessage(result);
						return;
					}
					var json = JSON.parse(result);
					if (!json.success) {
						API.errorMessage(json.message);
						return;
					}
					alert(result);
					
				})
				.fail(function() {
					API.errorMessage("Could not import list.");
					importtool_disableForm(false);
				});

		});

		// Mailout tool.
		$('#mailout-content').keyup(function(){

			var steam_keys_md = "**Steam Keys:**\n\n";
			steam_keys_md += "* XXXXX-XXXXX-XXXXX *(Sent by Nick on 23rd March 2015)*\n";
			steam_keys_md += "* XXXXX-XXXXX-XXXXX *(Sent by Ashley on 3rd March 2015)*\n\n";

			var md_content = $(this).val();
			md_content = md_content.replace("{{first_name}}", "(First Name)");
			md_content = md_content.replace("{{steam_key}}", "XXXXX-XXXXX-XXXXX");
			md_content = md_content.replace("{{steam_keys}}", steam_keys_md);
			var html_content = markdown.toHTML( md_content ); 
			$('#mailout-preview').html(html_content);
		});

		// Select people en-masse
		var selectAllCheckedForType = function(type, checked) {
			$('input[data-'+type+'-checkbox="true"]').each(function(){
				var visible = $(this).is(":visible");
				if (visible) {
					var typeId = $(this).attr('data-'+type+'-id');
					if (typeId != undefined) {
						if (checked) { 
							$('input[data-'+type+'-checkbox="true"][data-'+type+'-id="' + typeId + '"]').prop("checked", true);
						} else {
							$('input[data-'+type+'-checkbox="true"][data-'+type+'-id="' + typeId + '"]').prop("checked", false);
						}
					}
				}
			})
		}
		$('#nav-select-edit').click(function(){
			impresslist.toggleSelectMode();
		});
		$('#nav-select-all').click(function(){
			var type = $(this).attr('data-type');
			selectAllCheckedForType(type, true);
			impresslist.refreshMailoutRecipients();
		});
		$('#nav-deselect-all').click(function(){
			var type = $(this).attr('data-type');
			selectAllCheckedForType(type, false);
			impresslist.refreshMailoutRecipients();
		});

		// Keys functions
		$('#nav-keys').click(function() {
			API.listKeys(impresslist.config.user.game, 'steam', true, function(data) {
				$('#steam_keys_assigned_count').html(data.count);
			});

			API.listKeys(impresslist.config.user.game, 'steam', false, function(data) {
				$('#steam_keys_available_count').html(data.count);
			});

			// sort out timepicker
			$('#keys-timepicker').datetimepicker();
			$('#keys-timepicker').data("DateTimePicker").defaultDate(moment(0, "X"));
			$('#keys-timepicker').data("DateTimePicker").format("DD/MM/YYYY h:mma");

			$('#keys-radio-expiry-never').unbind("click");
			$('#keys-radio-expiry-time').unbind("click");
			$('#keys-radio-expiry-never').click(function() { $('#keys-timepicker').hide(); });
			$('#keys-radio-expiry-time').click(function() { $('#keys-timepicker').show(); });
			$('#keys-timepicker').hide();
			$('#keys-radio-expiry-never').prop("checked", "true");

		});
		$('#keys-add-submit').click(function(){
			var keys_setFormEnabled = function(boo) {
				boo = !boo;
				$('#keys-add-platform').attr("disabled", boo); 
				$('#keys-add-textfield').attr("disabled", boo); 
				$('#keys-radio-expiry-never').attr("disabled", boo); 
				$('#keys-radio-expiry-time').attr("disabled", boo); 
				$('#keys-timepicker').attr("disabled", boo); 
				$('#keys-add-submit').attr("disabled", boo); 
			}
			keys_setFormEnabled(false);

			var submitList = $('#keys-add-textfield').val();
			var submitGame = impresslist.config.user.game;
			var submitPlatform = $('#keys-add-platform').val();
			if (submitPlatform == '---') {
				API.errorMessage("Please select an platform for these keys.");
				keys_setFormEnabled(true);
				return;
			}

			var whichtime = $('input[name="keys-expiry-radio"]:checked').val();
			if (whichtime == undefined) {
				API.errorMessage("Please select an expiry for these keys.");
				keys_setFormEnabled(true);
				return;
			}
			var expiresOn = 0;
			if (whichtime == 'time') {
				var date = new Date($('#keys-timepicker').data("DateTimePicker").date());
				expiresOn = Math.floor(date.getTime() / 1000);
			}
			API.addKeys(submitList, submitGame, submitPlatform, expiresOn, function(d) {
				keys_setFormEnabled(true);
				$('#keys-add-platform').val('---');
				$('#keys-add-textfield').val('');
				$('#nav-keys').click();
			});	
				
		});

		// Social Scheduler 
		// Timeline 
		$('#social-timeline-add').click(function() {
			API.addSocialTimelineItem();
		});
		

		// Social Account Management
		$('#social-addtwitteraccount-link').click(function(){
			$('#social-addtwitteraccount-buttonpage').hide();
			$('#social-addtwitteraccount-verifypage').show();
		});
		$('#social-addtwitteraccount-cancel').click(function(){
			$('#social-addtwitteraccount-verifypage').hide();
			$('#social-addtwitteraccount-buttonpage').show();
		});
		$('#social-addtwitteraccount-submit').click(function(){
			var token 		= $('#social-addtwitteraccount-requesttoken').val();
			var tokenSecret = $('#social-addtwitteraccount-requesttokensecret').val();
			var pin 		= $('#social-addtwitteraccount-pin').val();
			API.addOAuthTwitterAccount(token, tokenSecret, pin);
		});

		// Chat functionality
		this.chat.init();

		// TODO functionality
		this.jobs.init();
		
		this.refreshFilter();
	},
	selectModeIsOpen: false,
	openSelectMode: function() {
		if (!this.selectModeIsOpen) { 
			this.toggleSelectMode();
		}
	},
	closeSelectMode: function() {
		if (this.selectModeIsOpen) { 
			this.toggleSelectMode();
		}
	},
	toggleSelectMode: function() {
		this.selectModeIsOpen = !this.selectModeIsOpen;

		// Refresh emails for all people here.
		for(var i = 0; i < this.people.length; i++) {
			this.people[i].refreshEmails();
		}
		this.refreshMailoutRecipients();

		var type = 'person'; //$(this).attr('data-type');
		$('.checkbox-column[data-type="' + type+ '"]').each(function(){
			$(this).toggle();
		});
		this.refreshFilter();

		
	},


	changePage: function() {
		var page = $(this).attr('data-nav-page');
		$("[data-type-page='true']").hide();
		$("[data-type-page='true'][data-page='" + page + "']").show();
	},
	
	refreshFilter: function() {
		impresslist.applyFilter($('#filter').val().toLowerCase());
	},
	applyFilter: function(text) {
		var countPeopleVisible = 0;
		for(var i = 0; i < impresslist.people.length; i++) { 
			if (impresslist.people[i].filter(text)) { countPeopleVisible++; }
		}
		
		var countPublicationsVisible = 0;
		for(var i = 0; i < impresslist.publications.length; i++) { 
			if (impresslist.publications[i].filter(text)) { countPublicationsVisible++; }
		}

		var countYoutubeChannelsVisible = 0;
		for(var i = 0; i < impresslist.youtubers.length; i++) { 
			if (impresslist.youtubers[i].filter(text)) { countYoutubeChannelsVisible++; }
		}

		var countCoverageVisible = 0;
		for(var i = 0; i < impresslist.coverage.length; i++) { 
			if (impresslist.coverage[i].filter(text)) { countCoverageVisible++; }
		}

		if (countPeopleVisible == 0) { $('#people-footer').show(); } else { $('#people-footer').hide(); }
		if (countPublicationsVisible == 0) { $('#publications-footer').show(); } else { $('#publications-footer').hide(); }
		if (countYoutubeChannelsVisible == 0) { $('#youtubers-footer').show(); } else { $('#youtubers-footer').hide(); }
		if (countCoverageVisible == 0) { $('#coverage-footer').show(); } else { $('#coverage-footer').hide(); }

		if (impresslist.people.length > 0 && countPeopleVisible == 0) { $('#people-container').hide(); } else { $('#people-container').show(); }
		if (impresslist.publications.length > 0 && countPublicationsVisible == 0) { $('#publications-container').hide(); } else { $('#publications-container').show(); }
		if (impresslist.youtubers.length > 0 && countYoutubeChannelsVisible == 0) { $('#youtubers-container').hide(); } else { $('#youtubers-container').show(); }
		if (impresslist.coverage.length > 0 && countCoverageVisible == 0) { $('#coverage-container').hide(); } else { $('#coverage-container').show(); }

		$('#people-count').html("(" + countPeopleVisible + ")");
		$('#publication-count').html("(" + countPublicationsVisible + ")");
		$('#youtuber-count').html("(" + countYoutubeChannelsVisible + ")");

		if (text.length > 0) { $('#chat-container').hide(); } else { $('#chat-container').show(); }

	},
	refreshMailoutRecipients: function() {
		var peopleSelected = $('input[data-type="person"][data-checkbox="true"]:checked');

		if (peopleSelected.length == 0) {
			$('#mailout-recipients-none').show();
			$('#mailout-recipients').hide();
			return;
		}

		var html = "";
		for(var i = 0; i < peopleSelected.length; i++) {
			
			var readBool = $(peopleSelected[i]).attr('mailout-read');
			html += "<tr>"
			html += "	<td>" + $(peopleSelected[i]).attr('data-mailout-name') + "</td>";
			html += "	<td>" + $(peopleSelected[i]).attr('data-mailout-typename') + "</td>";
			html += "	<td class='mailout-readflags mailout-read-" + readBool + "'>" + readBool + "</td>";
			// " + $(peopleSelected[i]).attr('data-mailout-email') + "
			html += "</tr>";
		}

		$('#mailout-recipients-tbody').html(html);

		$('#mailout-recipients').show();
		$('#mailout-recipients-none').hide();
	},
	
	addPerson: function(obj, fromInit) {
		this.people.push(obj);
		obj.onAdded();
		if (!fromInit) { impresslist.refreshFilter(); }
	},
	addPublication: function(obj, fromInit) {
		this.publications.push(obj);
		obj.onAdded();
		if (!fromInit) { impresslist.refreshFilter(); }
	},
	addPersonPublication: function(obj, fromInit) {
		this.personPublications.push(obj);
		obj.onAdded(fromInit);
		if (!fromInit) { impresslist.refreshFilter(); }
	},
	addPersonYoutubeChannel: function(obj, fromInit) {
		this.personYoutubeChannels.push(obj);
		obj.onAdded(fromInit);
		if (!fromInit) { impresslist.refreshFilter(); }
	},
	addEmail: function(obj, fromInit) {
		this.emails.push(obj);
		obj.onAdded();
		if (!fromInit) { impresslist.refreshFilter(); }
	},
	addUser: function(obj, fromInit) {
		this.users.push(obj);
		obj.onAdded();
		if (!fromInit) { impresslist.refreshFilter(); }
	},
	addYoutuber: function(obj, fromInit) {
		this.youtubers.push(obj);
		obj.onAdded();
		if (!fromInit) { impresslist.refreshFilter(); }
	},
	addGame: function(obj, fromInit) {
		this.games.push(obj);
		obj.onAdded();
	},
	addCoverage: function(obj, fromInit) {
		this.coverage.push(obj);
		obj.onAdded(fromInit);
	},
	
	addOAuthTwitterAccount: function(obj, fromInit) {
		this.oauthTwitterAccounts.push(obj);
		obj.onAdded(fromInit);
	},
	removeOAuthTwitterAccount: function(obj) {
		for(var i = 0, len = this.oauthTwitterAccounts.length; i < len; ++i) {
			if (this.oauthTwitterAccounts[i].id == obj.id) {
				console.log('oauth twitter removed: ' + obj.id);
				obj.onRemoved();
				this.oauthTwitterAccounts.splice(i, 1);
				break;
			}
		}
	},
	addSocialUpload: function(obj, fromInit) {
		this.socialUploads.push(obj);
		obj.onAdded(fromInit);
	},
	removeSocialUpload: function(obj) {
		for(var i = 0, len = this.socialUploads.length; i < len; ++i) {
			if (this.socialUploads[i].id == obj.id) {
				console.log('social upload removed: ' + obj.id);
				obj.onRemoved();
				this.socialUploads.splice(i, 1);
				break;
			}
		}
	},
	addSocialTimelineItem: function(obj, fromInit) {
		this.socialTimelineItems.push(obj);
		obj.onAdded(fromInit);
	},
	removeSocialTimelineItem: function(obj) {
		for(var i = 0, len = this.socialTimelineItems.length; i < len; ++i) {
			if (this.socialTimelineItems[i].id == obj.id) {
				console.log('social timeline item removed: ' + obj.id);
				obj.onRemoved();
				this.socialTimelineItems.splice(i, 1);
				break;
			}
		}
	},

	addSimpleMailout: function(obj, fromInit) {
		this.simpleMailouts.push(obj);
		obj.onAdded(fromInit);
	},
	removeSimpleMailout: function(obj) {
		for(var i = 0, len = this.simpleMailouts.length; i < len; ++i) {
			if (this.simpleMailouts[i].id == obj.id) {
				console.log('simple mailout removed: ' + obj.id);
				obj.onRemoved();
				this.simpleMailouts.splice(i, 1);
				break;
			}
		}
	},
	removePerson: function(obj) {
		for(var i = 0, len = this.people.length; i < len; ++i) {
			if (this.people[i].id == obj.id) {
				console.log('person removed: ' + obj.id);
				obj.onRemoved();
				this.people.splice(i, 1);
				impresslist.refreshFilter();
				break;
			}
		}
	},
	removePublication: function(obj) {
		for(var i = 0, len = this.publications.length; i < len; ++i) {
			if (this.publications[i].id == obj.id) {
				console.log('publication removed: ' + obj.id);
				obj.onRemoved();
				this.publications.splice(i, 1);
				impresslist.refreshFilter();
				break;
			}
		}
	}, 
	removeYoutuber: function(obj) {
		for(var i = 0, len = this.youtubers.length; i < len; ++i) {
			if (this.youtubers[i].id == obj.id) {
				console.log('youtuber removed: ' + obj.id);
				obj.onRemoved();
				this.youtubers.splice(i, 1);
				impresslist.refreshFilter();
				break;
			}
		}
	}, 
	removePersonPublication: function(obj) {
		for(var i = 0, len = this.personPublications.length; i < len; ++i) {
			if (this.personPublications[i].id == obj.id) {
				console.log('person publication removed: ' + obj.id);
				obj.onRemoved();
				this.personPublications.splice(i, 1);
				impresslist.refreshFilter();
				break;
			}
		}
	},
	removePersonYoutubeChannel: function(obj) {
		for(var i = 0, len = this.personYoutubeChannels.length; i < len; ++i) {
			if (this.personYoutubeChannels[i].id == obj.id) {
				console.log('person youtube channel removed: ' + obj.id);
				obj.onRemoved();
				this.personYoutubeChannels.splice(i, 1);
				impresslist.refreshFilter();
				break;
			}
		}
	},
	removeCoverage: function(obj) {
		for(var i = 0, len = this.coverage.length; i < len; ++i) {
			if (this.coverage[i].id == obj.id) {
				console.log('coverage removed: ' + obj.id);
				obj.onRemoved();
				this.coverage.splice(i, 1);
				impresslist.refreshFilter();
				break;
			}
		}
	},
	findPersonById: function(id) {
		/*for(var i = 0; i < this.people.length; ++i) {
			if (this.people[i].id == id) {
				return this.people[i];
			}
		}*/
		var r = this.binarySearchById(this.people, id);
		if (r != null) { return r; }

		console.log("impresslist: could not findPersonById: " + id);
		return null;
	},
	findPersonPublicationById: function(id) {
		/*for(var i = 0, len = this.personPublications.length; i < len; ++i) {
			if (this.personPublications[i].id == id) {
				return this.personPublications[i];
			}
		}*/
		var r = this.binarySearchById(this.personPublications, id);
		if (r != null) { return r; }

		console.log("impresslist: could not findPersonPublicationById: " + id);
		return null;
	},
	findPublicationById: function(id) {
		/*for(var i = 0; i < this.publications.length; ++i) {
			if (this.publications[i].id == id) {
				return this.publications[i];
			}
		}*/
		var r = this.binarySearchById(this.publications, id);
		if (r != null) { return r; }

		console.log("impresslist: could not findPublicationById: " + id);
		return null;
	},
	findYoutuberById: function(id) {
		/*for(var i = 0; i < this.youtubers.length; ++i) {
			if (this.youtubers[i].id == id) {
				return this.youtubers[i];
			}
		}*/
		var r = this.binarySearchById(this.youtubers, id);
		if (r != null) { return r; }

		console.log("impresslist: could not findYoutuberById: " + id);
		return null;
	},
	findUserById: function(id) {
		/*for(var i = 0; i < this.users.length; ++i) {
			if (this.users[i].id == id) {
				return this.users[i];
			}
		}*/
		var r = this.binarySearchById(this.users, id);
		if (r != null) { return r; }

		console.log("impresslist: could not findUserById: " + id);
		return null;
	},
	findEmailById: function(id) {
		/*for(var i = 0; i < this.emails.length; ++i) {
			if (this.emails[i].id == id) {
				return this.emails[i];
			}
		}*/
		var r = this.binarySearchById(this.emails, id);
		if (r != null) { return r; }

		console.log("impresslist: could not findEmailById: " + id);
		return null;
	},
	findGameById: function(id) {
		for(var i = 0; i < this.games.length; ++i) {
			if (this.games[i].id == id) {
				return this.games[i];
			}
		}
		console.log("impresslist: could not findGameById: " + id);
		return null;
	},
	findOAuthTwitterAccountById: function(id) {
		var r = this.binarySearchById(this.oauthTwitterAccounts, id);
		if (r != null) { return r; }

		console.log("impresslist: could not findOAuthTwitterAccountById: " + id);
		return null;
	},
	// http://oli.me.uk/2014/12/17/revisiting-searching-javascript-arrays-with-a-binary-search/
	binarySearchById: function(list, id) {
	    var min = 0;
	    var max = list.length - 1;
	    var guess;

	    while (min <= max) {
	        guess = Math.floor((min + max) / 2);

	        if (list[guess].id == id) {
	            return list[guess];
	        }
	        else {
	            if (list[guess].id < id) {
	                min = guess + 1;
	            }
	            else {
	                max = guess - 1;
	            }
	        }
	    }

	    return null;
	},
	backup: function() {

	}

};

impresslist.jobs = {
	selectors: {
		text: "#jobs-textarea",
		button: "#jobs-save-all"
	},
	init: function(fromInit) {
		this.update(fromInit);

		var thiz = this;
		$(this.selectors.button).click(function() {
			var message = $(thiz.selectors.text).val();
			if (message.trim().length > 0) { 
				thiz.save(message);
			}
		});
	},
	update: function(fromInit) {
		var thiz = this;
		var url = "api.php?endpoint=/job/list/";
		$.ajax( url )
			.done(function(result) {
				if (result.substr(0, 1) != '{') { 
					API.errorMessage(result);
					return;
				}
				var json = JSON.parse(result);
				if (!json.success) {
					API.errorMessage(json.message);
					return;
				}
				thiz.populate(json.jobs);
				$(thiz.selectors.button).removeAttr('disabled');
				
			})
			.fail(function() {
				API.errorMessage("Could not list Jobs.");
			});
	},
	save: function(message) {
		var thiz = this;
		var url = "api.php?endpoint=/job/save-all/&jobs=" + encodeURIComponent(message);
		$.ajax( url )
			.done(function(result) {
				if (result.substr(0, 1) != '{') { 
					API.errorMessage(result);
					return;
				}
				var json = JSON.parse(result);
				if (!json.success) { API.errorMessage(json.message); return; }
				
				thiz.populate(json.jobs);
				API.successMessage("Jobs saved");
			})
			.fail(function() {
				API.errorMessage("Could not save Jobs.");
			});
	},
	populate: function(jobs) {
		var jobsString = "";
		for(var i = 0; i < jobs.length; ++i) {
			jobsString += jobs[i];
			if (i < jobs.length - 1) {
				jobsString += "\n";
			}
		}
		console.log(jobsString);
		$(this.selectors.text).html(jobsString);
	} 
};


impresslist.chat = {
	online_users: new Array(),
	current_filesize: 0,
	latest_message_time: (new Date().getTime() / 1000) - (60*60*12), // last 12 hours of chat!
	selectors: {
		users: "#chat .online-users",
		messages: "#chat .messages",
		text: "#chat .message",
		button: "#chat .submit-message"
	},
	init: function() {
		this.updateOnlineUsers();
		setInterval(this.updateOnlineUsers, 30 * 1000);

		this.update(true);

		var thiz = this;
		$(this.selectors.text).keyup(function(e) {
			var code = e.which;
			if(code==13) { 
				$(thiz.selectors.button).click();
			}
		});
		$(this.selectors.button).click(function() {
			var message = $(thiz.selectors.text).val();
			$(thiz.selectors.text).val("");
			if (message.trim().length > 0) { 
				thiz.send(message);
			}
		});
	},
	updateOnlineUsers: function(fromInit) {
		var thiz = impresslist.chat;
		var url = "api.php?endpoint=/chat/online-users/";
		console.log(url);
		$.ajax( url )
			.done(function(result) {
				if (result.substr(0, 1) != '{') {
					API.errorMessage(result);
					return;
				}
				var json = JSON.parse(result);
				if (!json.success) {
					if (json.logout) { window.location = "/"; return; }
					API.errorMessage(json.message);
					return;
				}
				//API.successMessage(result);
				$(thiz.selectors.users).html("");
				for(var i = 0; i < json.data.users.length; ++i) {
					var u = impresslist.findUserById(json.data.users[i]);
					$(thiz.selectors.users).append(" <span style='color:" + u.field('color') + "'>" + u.fullname() + "</span>");
				}
			});
	},


	update: function(fromInit) { 
		var thiz = this;
		$.ajax(  {
			type: "GET",
			url: "api.php?endpoint=/chat/lines/&time=" + this.latest_message_time + "&size=" + this.current_filesize,
			dataType: "text",
				cache: false,
				success: function(result) {
					console.log(result);

					if (result.substr(0, 1) != '{') { 
						API.errorMessage(result);
						return;
					}
					var json = JSON.parse(result);
					if (!json.success) {
						if (json.logout) { window.location = "/"; return; }
						API.errorMessage(json.message);
						return;
					}
					//console.log(result);
					if (fromInit) { $(thiz.selectors.messages).html(""); }
					for (var i = 0; i < json.data.lines.length; i++) {
						
						var user = impresslist.findUserById(json.data.lines[i].user);
						var date = new Date(json.data.lines[i].time*1000);
						var text = "";
						text += (date.getUTCDate()) + "/";
						text += impresslist.util.zeropad(date.getUTCMonth()+1, 2) + "/";
						text += (date.getUTCFullYear()) + " - ";
						text += (date.getUTCHours()) + ":";
						text += impresslist.util.zeropad(date.getUTCMinutes(), 2) + "";
						text += " - <span style='color:" + user.field('color') + "'>";
						text += user.field('surname');
						text += "</span>: ";
						text += json.data.lines[i].message;
						text += "<br/>"

						$(thiz.selectors.messages).append(text);
						$(thiz.selectors.messages).scrollTop($(thiz.selectors.messages)[0].scrollHeight);
					}

					thiz.latest_message_time = json.data.meta.time;
					thiz.current_filesize = json.data.meta.size;

					setTimeout(function() { 
						thiz.update(false); 
					}, 1000);
				}
			});
	},
	send: function(line) { 
		$.ajax({
			type: "POST",
			url: "api.php?endpoint=/chat/send/",
			data: { 'message': line },
			dataType: "text",
			cache: false,
			success:  function(result) {
				console.log(result);
				if (result.substr(0, 1) != '{') { 
					API.errorMessage(result);
					return;
				}
				var json = JSON.parse(result);
				if (!json.success) {
					API.errorMessage(json.message);
					return;
				} 
			}
		});
			
	}
}




impresslist.util = {
	relativetime_contact: function(previous) {
	    //console.log(previous);
	    if (Number(previous) == 0 || Number(previous) == 3600) { return "Never"; }
	    return this.relativetime(previous);
	},
	relativetime: function(previous) {
		return this.relativetime2(parseInt(Date.now()/1000), Number(previous));
	},
	relativetime2: function(current, previous) {

	    var msPerMinute = 60;// * 1000;
	    var msPerHour = msPerMinute * 60;
	    var msPerDay = msPerHour * 24;
	    var msPerMonth = msPerDay * 30;
	    var msPerYear = msPerDay * 365;

	    var elapsed = current - previous; 
	    //console.log("current: " + current);
	    //console.log("prev: " + previous);

	    if (elapsed < 0) {
	    	var elapsed = Math.abs(elapsed);
	    	if (elapsed < msPerMinute) {
				var num = Math.round(elapsed/1000);
				if (num == 0) {
					return "now";
				}
		        return num + ' seconds from now';
		    } else if (elapsed < msPerHour) {
		        return Math.round(elapsed/msPerMinute) + ' minutes from now';   
		    } else if (elapsed < msPerDay ) {
		        return Math.round(elapsed/msPerHour ) + ' hours from now';   
		    } else if (elapsed < msPerMonth) {
		        return '~' + Math.round(elapsed/msPerDay) + ' days from now';   
		    } else if (elapsed < msPerYear) {
		        return '~' + Math.round(elapsed/msPerMonth) + ' months from now';   
		    }
	    	return '~' + Math.round(elapsed/msPerYear ) + ' years from now';   
	    }
		else if (elapsed < msPerMinute) {
			var num = Math.round(elapsed/1000);
			if (num == 0) {
				return "now";
			}
	        return num + ' seconds ago';
	    } else if (elapsed < msPerHour) {
	        return Math.round(elapsed/msPerMinute) + ' minutes ago';   
	    } else if (elapsed < msPerDay ) {
	        return Math.round(elapsed/msPerHour ) + ' hours ago';   
	    } else if (elapsed < msPerMonth) {
	        return '~' + Math.round(elapsed/msPerDay) + ' days ago';   
	    } else if (elapsed < msPerYear) {
	        return '~' + Math.round(elapsed/msPerMonth) + ' months ago';   
	    }

	    else {
	        return '~' + Math.round(elapsed/msPerYear ) + ' years ago';   
	    }
	},
	
	zeropad: function(num, digits) {
	    var newstr = ("" + num);
	    while (newstr.length < digits) {
	        newstr = "0" + newstr;
	    }
	    return newstr;
	},

	mailtoClient: function(defaultEmail, emailSubject, emailBody, emailBCC) {
		emailBCC = typeof emailBCC !== 'undefined' ? emailBCC : "";
		
  	 	var str = "mailto:" + defaultEmail + "?subject=" + emailSubject + "&body=" + emailBody;
		if (emailBCC.length > 0) { str += "&bcc=" + emailBCC; }
		return str;
	},
	mailtoGmail: function(defaultEmail, emailSubject, emailBody, emailBCC, emailGmailIndex) {
		emailBCC 		= typeof emailBCC !== 'undefined' ? emailBCC : "";
		emailGmailIndex = typeof emailGmailIndex !== 'undefined' ? emailGmailIndex : "0";

		var str = "https://mail.google.com/mail/u/" + emailGmailIndex + "/?view=cm&fs=1&to=" + defaultEmail + "&su=" + emailSubject + "&body=" + emailBody;
		if (emailBCC.length > 0) { str += "&bcc=" + emailBCC; }
		return str;
	}
}
