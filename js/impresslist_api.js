API = function() {

}
API.prototype = {

}
API.root = "";

API.search = function(q, successCallback, failCallback) {
	var url = API.root + "api.php?endpoint=/search/&q=" + encodeURIComponent(q);
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') {
				API.errorMessage(result);
				if (failCallback) { failCallback(); }
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				if (failCallback) { failCallback(); }
				return;
			}

			if (successCallback) { successCallback(json); }
		})
		.fail(function() {
			API.errorMessage("Could not perform Full Search.");
			if (failCallback) { failCallback(); }
		});

}

API.listSocialTimeline = function(fromInit) {
	if (typeof fromInit == 'undefined') { fromInit = true; }

	impresslist.loading.set('socialTimelineItem', true);
	var url = API.root + "api.php?endpoint=/social/timeline/";
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
			if (json.timeline.length == 0) { // this is the worst place to put this code but it will be rewritten anyway..
				$('#social-timeline-none').show();
				$('#social-timeline-loading').hide();
			}
			impresslist.loading.set('socialTimelineItem', false);
			if (fromInit) {
				impresslist.loading.onCategoryItemLoaded('social');
			}
		})
		.fail(function() {
			API.errorMessage("Could not pull Social Timeline.");
		});
}
API.addSocialTimelineItem = function() {
	var url = API.root + "api.php?endpoint=/social/timeline/item/add/";
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
	var url = API.root + "api.php?endpoint=/social/timeline/item/add-retweets/" +
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
	var url = API.root + "api.php?endpoint=/social/timeline/item/save/" +
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
	var url = API.root + "api.php?endpoint=/social/timeline/item/remove/&id=" + encodeURIComponent(obj.id);
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
	var url = API.root + "api.php?endpoint=/social/uploads/list/";
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
			if (fromInit) {
				impresslist.loading.onCategoryItemLoaded('social');
			}
		})
		.fail(function() {
			API.errorMessage("Could not list Social Uploads.");
		});
}
API.addSocialUpload = function(d, successCallback) {
	var url = API.root + "api.php?endpoint=/social/uploads/add/";
	console.log(url);

	$.ajax({
		url: API.root + "api.php?endpoint=/social/uploads/add/",
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
	var url = API.root + "api.php?endpoint=/social/uploads/remove/&name=" + encodeURIComponent(acc.field("name"));
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

API.listPeople = function(fromInit) {//
	if (typeof fromInit == 'undefined') { fromInit = true; }

	var url = API.root + "api.php?endpoint=/person/list/";
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
			if (fromInit) {
				impresslist.loading.onCategoryItemLoaded('home');
				//impresslist.refreshFilter();
			}
		})
		.fail(function() {
			API.errorMessage("Could not list People.");
		});
}
API.listPublications = function(fromInit) {
	if (typeof fromInit == 'undefined') { fromInit = true; }

	var url = API.root + "api.php?endpoint=/publication/list/";
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
				impresslist.loading.onCategoryItemLoaded('home');
				//impresslist.refreshFilter();
			}
		})
		.fail(function() {
			API.errorMessage("Could not list Publications.");
		});
}
API.listPersonPublications = function(fromInit) {
	if (typeof fromInit == 'undefined') { fromInit = true; }

	var url = API.root + "api.php?endpoint=/person-publication/list/";
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
			if (fromInit) {
				impresslist.loading.onCategoryItemLoaded('home');
				//impresslist.refreshFilter();
			}
		})
		.fail(function() {
			API.errorMessage("Could not list Person Publications.");
		});
}
API.listPersonYoutubeChannels = function(fromInit) {
	if (typeof fromInit == 'undefined') { fromInit = true; }

	var url = API.root + "api.php?endpoint=/person-youtube-channel/list/";
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
			if (fromInit) {
				impresslist.loading.onCategoryItemLoaded('home');
				//impresslist.refreshFilter();
			}
		})
		.fail(function() {
			API.errorMessage("Could not list Person Youtube Channels.");
		});
}
API.listPersonTwitchChannels = function(fromInit) {
	if (typeof fromInit == 'undefined') { fromInit = true; }

	var url = API.root + "api.php?endpoint=/person-twitchchannel/list/";
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
			for(var i = 0; i < json.personTwitchChannels.length; ++i) {
				var channel = new PersonTwitchChannel(json.personTwitchChannels[i]);
				impresslist.addPersonTwitchChannel(channel, fromInit);
			}
			if (fromInit) {
				impresslist.loading.onCategoryItemLoaded('home');
				//impresslist.refreshFilter();
			}
		})
		.fail(function() {
			API.errorMessage("Could not list Person Youtube Channels.");
		});
}
API.listYoutubeChannels = function(fromInit) {
	if (typeof fromInit == 'undefined') { fromInit = true; }

	impresslist.loading.set('youtubeChannels', true);
	var url = API.root + "api.php?endpoint=/youtuber/list/";
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
			impresslist.loading.set('youtubeChannels', false);
			if (fromInit) {
				impresslist.loading.onCategoryItemLoaded('home');
			}
		})
		.fail(function() {
			API.errorMessage("Could not list Youtubers.");
		});
}
API.listTwitchChannels = function(fromInit) {
	if (typeof fromInit == 'undefined') { fromInit = true; }

	impresslist.loading.set('twitchChannels', true);
	var url = API.root + "api.php?endpoint=/twitchchannel/list/";
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
			for(var i = 0; i < json.twitchchannels.length; ++i) {
				var channel = new TwitchChannel(json.twitchchannels[i]);
				impresslist.addTwitchChannel(channel, fromInit);
			}
			if (fromInit) {
				impresslist.loading.onCategoryItemLoaded('home');
				//impresslist.refreshFilter();
			}
			impresslist.loading.set('twitchChannels', false);
		})
		.fail(function() {
			API.errorMessage("Could not list Twitch Channels.");
		});
}
API.listEmails = function(fromInit) {
	if (typeof fromInit == 'undefined') { fromInit = true; }

	var url = API.root + "api.php?endpoint=/email/list/";
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
			if (fromInit) {
				impresslist.loading.onCategoryItemLoaded('home');
				//impresslist.refreshFilter();
			}
		})
		.fail(function() {
			API.errorMessage("Could not list Emails.");
		});
}
API.listCoverage = function(fromInit, successCallback) {
	if (typeof fromInit == 'undefined') { fromInit = true; }

	impresslist.loading.set('coverage', true);
	var url = API.root + "api.php?endpoint=/coverage/";
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
			if (successCallback) successCallback(json);
			if (fromInit) {
				impresslist.loading.onCategoryItemLoaded('project');
				//impresslist.refreshFilter();
			}
			impresslist.loading.set('coverage', false);
		})
		.fail(function() {
			API.errorMessage("Could not list Coverage.");
		});
}
API.listWatchedGames = function(fromInit) {
	if (typeof fromInit == 'undefined') { fromInit = true; }

	impresslist.loading.set('watchedgames', true);
	var url = API.root + "api.php?endpoint=/watchedgame/list/";
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
			for(var i = 0; i < json.watchedgames.length; ++i) {
				var watchedgame = new WatchedGame(json.watchedgames[i]);
				impresslist.addWatchedGame(watchedgame, fromInit);
			}
			if (fromInit) {
				impresslist.loading.onCategoryItemLoaded('project');
				//impresslist.refreshFilter();
			}
			impresslist.loading.set('watchedgames', false);
		})
		.fail(function() {
			API.errorMessage("Could not list Watched Games.");
		});
}
API.saveWatchedGame = function(watchedgame, name, keywords, successCallback, failCallback) {
	var url = API.root + "api.php?endpoint=/watchedgame/save/" +
					"&id=" + encodeURIComponent(watchedgame.id) +
					"&name=" + encodeURIComponent(name) +
					"&keywords=" + encodeURIComponent(keywords);
	console.log(url);

	$.ajax( {
		method: 'GET',
		url: url
	})
		.done(function(result) {
			if (result.substr(0, 1) != '{') {
				API.errorMessage(result);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				if (failCallback) failCallback();
				return;
			}
			API.successMessage("Watched Game saved.");
			obj.init(json.watchedgame);
			obj.update();
			if (successCallback) successCallback();
		})
		.fail(function() {
			API.errorMessage("Could not save Watched Game.");
			if (failCallback) failCallback();
		});
}
API.removeWatchedGame = function(watchedgame, successCallback, failCallback) {
	var url = API.root + "api.php?endpoint=/watchedgame/remove/&id=" + encodeURIComponent(watchedgame.id);
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
				if (failCallback) failCallback();
				return;
			}
			API.successMessage("Watched Game removed.");
			impresslist.removeWatchedGame(person);
			if (successCallback) successCallback();
		})
		.fail(function() {
			API.errorMessage("Could not remove Watched Game.");
			if (failCallback) failCallback();
		});
}

API.assignedKeys = function(type, typeId, successCallback, errorCallback) {
	var url = API.root + "api.php?endpoint=/keys/assigned/" +
				"&type=" + encodeURIComponent(type) +
				"&type_id=" + encodeURIComponent(typeId);
	console.log(url);

	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') {
				API.errorMessage(result);
				if (errorCallback) errorCallback();
				return;
			}

			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				if (errorCallback) errorCallback();
				return;
			}

			if (successCallback) successCallback(json);
		})
		.fail(function() {
			API.errorMessage("Could not list assigned keys.");
			if (errorCallback) errorCallback();
		});
}

API.listKeys = function(game, platform, subplatform, assigned, callbackfunction) {
	var url = API.root + "api.php?endpoint=/keys/list/" +
				"&game=" + encodeURIComponent(game) +
				"&platform=" + encodeURIComponent(platform) +
				"&subplatform=" + encodeURIComponent(subplatform) +
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
API.addKeys = function(keys, game, platform, subplatform, expiresOn, callbackfunction, failCallback) {
	var url = API.root + "api.php?endpoint=/keys/add/" +
					"&keys=" + encodeURIComponent(keys) +
					"&game=" + encodeURIComponent(game) +
					"&platform=" + encodeURIComponent(platform) +
					"&subplatform=" + encodeURIComponent(subplatform) +
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
				failCallback();
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
			failCallback();
			API.errorMessage("Could not add Keys.");
		});
}
API.popKeys = function(game, platform, subplatform, amount, successCallback) {
	var url = API.root + "api.php?endpoint=/keys/pop/" +
					"&game=" + encodeURIComponent(game) +
					"&platform=" + encodeURIComponent(platform) +
					"&subplatform=" + encodeURIComponent(subplatform) +
					"&amount=" + encodeURIComponent(amount);
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
			API.successMessage("Keys popped.");
			console.log(json);

			successCallback(json);
		})
		.fail(function() {
			API.errorMessage("Could not pop Keys.");
		});
}

API.listSimpleMailouts = function(fromInit) {
	if (typeof fromInit == 'undefined') { fromInit = true; }

	var url = API.root + "api.php?endpoint=/mailout/simple/list/";
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

			if (fromInit) {
				impresslist.loading.onCategoryItemLoaded('mailout');
			}

		})
		.fail(function() {
			API.errorMessage("Could not list Mailouts.");
		});
}
API.addSimpleMailout = function() {
	var url = API.root + "api.php?endpoint=/mailout/simple/add/";
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
API.duplicateSimpleMailout = function(obj, successCallback, errorCallback) {
	var url = API.root + "api.php?endpoint=/mailout/simple/duplicate/" +
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
				if (errorCallback) errorCallback();
				return;
			}
			API.successMessage("Simple Mailout duplicated.");
			console.log(json);

			var simpleMailout = new SimpleMailout(json.mailout);
			impresslist.addSimpleMailout(simpleMailout, false);
			if (successCallback) successCallback(simpleMailout);
		})
		.fail(function() {
			API.errorMessage("Could not duplicate Simple Mailout.");
			if (errorCallback) errorCallback();
		});
}
API.saveSimpleMailout = function(obj, name, subject, recipients, markdown, timestamp, successCallback, errorCallback) {

	var url = API.root + "api.php?endpoint=/mailout/simple/save/" +
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
				if (errorCallback) errorCallback(json);
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				if (errorCallback) errorCallback(json);
				return;
			}
			API.successMessage("Simple Mailout saved.");
			obj.init(json.mailout);
			obj.update();
			if (successCallback) successCallback(json);
		})
		.fail(function() {
			API.errorMessage("Could not save Simple Mailout.");
		});
}
API.sendSimpleMailout = function(obj) {
	var url = API.root + "api.php?endpoint=/mailout/simple/send/" +
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
	var url = API.root + "api.php?endpoint=/mailout/simple/cancel/" +
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
	var url = API.root + "api.php?endpoint=/mailout/simple/remove/&id=" + encodeURIComponent(simpleMailout.id);
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
	var url = API.root + "api.php?endpoint=/person/add/&firstname=" + encodeURIComponent(firstname) + "&surnames=" + encodeURIComponent(surnames) + "&email=" + encodeURIComponent(email) + "&twitter=" + encodeURIComponent(twitter) + "&notes=" + encodeURIComponent(notes);
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
	var url = API.root + "api.php?endpoint=/person/add-publication/" +
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
	var url = API.root + "api.php?endpoint=/person/add-youtube-channel/" +
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
	var url = API.root + "api.php?endpoint=/person/remove-youtube-channel/" +
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
API.addPersonTwitchChannel = function(personObj, twitchChannelId) {
	var url = API.root + "api.php?endpoint=/person/add-twitchchannel/" +
				"&person=" + encodeURIComponent(personObj.id) +
				"&twitchchannel=" + encodeURIComponent(twitchChannelId);
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
			API.successMessage("Person Twitch Channel added.");
			console.log(json);

			impresslist.addPersonTwitchChannel(new PersonTwitchChannel(json.personTwitchChannel), false);
		})
		.fail(function() {
			API.errorMessage("Could not add Person - Twitch Channel.");
		});
}
API.removePersonTwitchChannel = function(personTwitchObj) {
	var url = API.root + "api.php?endpoint=/person/remove-twitchchannel/" +
				"&personTwitchChannel=" + encodeURIComponent(personTwitchObj.id);
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
			API.successMessage("Person Twitch Channel removed.");
			console.log(json);

			impresslist.removePersonTwitchChannel(personTwitchObj);
		})
		.fail(function() {
			API.errorMessage("Could not removed Person Twitch Channel.");
		});
}
API.savePersonPublication = function(personPublicationObj, email) {
	var url = API.root + "api.php?endpoint=/person/save-publication/" +
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
	var url = API.root + "api.php?endpoint=/person/remove-publication/" +
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
	var url = API.root + "api.php?endpoint=/coverage/publication/add/";
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
	var url = API.root + "api.php?endpoint=/coverage/publication/save/" +
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
	var url = API.root + "api.php?endpoint=/coverage/publication/remove/&id=" + encodeURIComponent(coverage.id);
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
	var url = API.root + "api.php?endpoint=/coverage/youtuber/add/";
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
	var url = API.root + "api.php?endpoint=/coverage/youtuber/save/" +
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
	var url = API.root + "api.php?endpoint=/coverage/youtuber/remove/&id=" + encodeURIComponent(coverage.id);
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
API.addTwitchChannelCoverage = function() {
	var url = API.root + "api.php?endpoint=/coverage/twitchchannel/add/";
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
API.saveTwitchChannelCoverage = function(coverage, twitchchannel, person, title, url, timestamp, thanked) {
	var url = API.root + "api.php?endpoint=/coverage/twitchchannel/save/" +
						"&id=" + encodeURIComponent(coverage.id) +
						"&twitchchannel=" + encodeURIComponent(twitchchannel) +
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
API.removeTwitchChannelCoverage = function(coverage) {
	var url = API.root + "api.php?endpoint=/coverage/twitchchannel/remove/&id=" + encodeURIComponent(coverage.id);
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
	var url = API.root + "api.php?endpoint=/person/set-priority/" +
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
	var url = API.root + "api.php?endpoint=/person/set-assignment/" +
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
API.savePerson = function(person, firstname, surnames, email, twitter, notes, country, language, tags, outofdate) {

	var url = API.root + "api.php?endpoint=/person/save/" +
					"&id=" + encodeURIComponent(person.id) +
					"&firstname=" + encodeURIComponent(firstname) +
					"&surnames=" + encodeURIComponent(surnames) +
					"&email=" + encodeURIComponent(email) +
					"&twitter=" + encodeURIComponent(twitter) +
					"&notes=" + encodeURIComponent(notes) +
					"&country=" + encodeURIComponent(country) +
					"&language=" + encodeURIComponent(language) +
					"&tags=" + encodeURIComponent(tags) +
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
	var url = API.root + "api.php?endpoint=/person/remove/&id=" + encodeURIComponent(person.id);
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
	var url = API.root + "api.php?endpoint=/publication/add/&name=" + encodeURIComponent(name);
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
	var url = API.root + "api.php?endpoint=/publication/set-priority/" +
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
API.savePublication = function(publication, name, url, email, rssfeedurl, twitter, notes, country, tags) {

	var url = API.root + "api.php?endpoint=/publication/save/" +
					"&id=" + encodeURIComponent(publication.id) +
					"&name=" + encodeURIComponent(name) +
					"&url=" + encodeURIComponent(url) +
					"&email=" + encodeURIComponent(email) +
					"&rssfeedurl=" + encodeURIComponent(rssfeedurl) +
					"&twitter=" + encodeURIComponent(twitter) +
					"&notes=" + encodeURIComponent(notes) +
					"&country=" + encodeURIComponent(country) +
					"&tags=" + encodeURIComponent(tags);
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
	var url = API.root + "api.php?endpoint=/publication/remove/&id=" + encodeURIComponent(publication.id);
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

API.searchYouTube = function(search, order, successCallback, failCallback) {
	var url = API.root + "api.php?endpoint=/youtuber/search-youtube/&search=" + encodeURIComponent(search) + "&order=" + encodeURIComponent(order);
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
				if (failCallback) failCallback();
				return;
			}

			if (successCallback) successCallback(json.results);

		})
		.fail(function() {
			API.errorMessage("Could not Search YouTube.");
			if (failCallback) failCallback();
		});
}
API.addYoutuber = function(openImmediately, successCallback, failCallback) {
	openImmediately = (typeof openImmediately == 'undefined')?true:openImmediately;
	var name = "Blank";
	var url = API.root + "api.php?endpoint=/youtuber/add/&channel=youtube";
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
				if (failCallback) failCallback();
				return;
			}
			API.successMessage("Youtuber added.");
			console.log(json);

			var youtuber = new Youtuber(json.youtubechannel);
			impresslist.addYoutuber(youtuber, false);
			if (openImmediately) {
				$(youtuber.openSelector()).click();
			}
			if (successCallback) {
				successCallback(youtuber);
			}
		})
		.fail(function() {
			API.errorMessage("Could not add Youtuber.");
			if (failCallback) failCallback();
		});
}
API.setYoutuberPriority = function(youtuber, priority, gameId) {
	var url = API.root + "api.php?endpoint=/youtuber/set-priority/" +
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
API.saveYoutuber = function(youtuber, channel, nameOverride, email, twitter, notes, country, tags) {

	var url = API.root + "api.php?endpoint=/youtuber/save/" +
					"&id=" + encodeURIComponent(youtuber.id) +
					"&channel=" + encodeURIComponent(channel) +
					"&name=" + encodeURIComponent(nameOverride) +
					"&email=" + encodeURIComponent(email) +
					"&twitter=" + encodeURIComponent(twitter) +
					"&notes=" + encodeURIComponent(notes) +
					"&country=" + encodeURIComponent(country) +
					"&tags=" + encodeURIComponent(tags);
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
	var url = API.root + "api.php?endpoint=/youtuber/remove/&id=" + encodeURIComponent(youtuber.id);
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
API.addTwitchChannel = function(openImmediately, successCallback, failCallback) {
	openImmediately = (typeof openImmediately == 'undefined')?true:openImmediately;
	var name = "Blank";
	var url = API.root + "api.php?endpoint=/twitchchannel/add/&channel=twitch";
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
				if (failCallback) failCallback();
				return;
			}
			API.successMessage("Twitch Channel added.");
			console.log(json);

			var twitchchannel = new TwitchChannel(json.twitchchannel);
			impresslist.addTwitchChannel(twitchchannel, false);
			if (openImmediately) {
				$(twitchchannel.openSelector()).click();
			}
			if (successCallback) {
				successCallback(twitchchannel);
			}
		})
		.fail(function() {
			API.errorMessage("Could not add Twitch Channel.");
			if (failCallback) failCallback();
		});
}
API.setTwitchChannelPriority = function(twitchchannel, priority, gameId) {
	var url = API.root + "api.php?endpoint=/twitchchannel/set-priority/" +
					"&id=" + encodeURIComponent(twitchchannel.id) +
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
			console.log(json.twitchchannel);
			API.successMessage("Twitch Channel priority set.");
			twitchchannel.init(json.twitchchannel);
			twitchchannel.update();
		})
		.fail(function() {
			API.errorMessage("Could not set priority on Twitch Channel.");
		});
}
API.saveTwitchChannel = function(twitchchannel, channel, email, twitter, notes, tags) {

	var url = API.root + "api.php?endpoint=/twitchchannel/save/" +
					"&id=" + encodeURIComponent(twitchchannel.id) +
					"&channel=" + encodeURIComponent(channel) +
					"&email=" + encodeURIComponent(email) +
					"&twitter=" + encodeURIComponent(twitter) +
					"&notes=" + encodeURIComponent(notes) +
					"&tags=" + encodeURIComponent(tags);
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

			API.successMessage("Twitch Channel saved.");
			twitchchannel.init(json.twitchchannel);
			twitchchannel.update();
		})
		.fail(function() {
			API.errorMessage("Could not save Twitch Channel.");
		});
}
API.removeTwitchChannel = function(twitchchannel) {
	var url = API.root + "api.php?endpoint=/twitchchannel/remove/&id=" + encodeURIComponent(twitchchannel.id);
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
			API.successMessage("Twitch Channel removed.");
			impresslist.removeTwitchChannel(twitchchannel);
		})
		.fail(function() {
			API.errorMessage("Could not remove Twitch Channel.");
		});
}
API.userChangeIMAPSettings = function(user, smtpServer, imapServer, imapPassword) {
	var url = API.root + "api.php?endpoint=/user/change-imap-settings/";
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
API.userChangeProject = function(user, newProject, onSuccess, onFail) {
	var url = API.root + "api.php?endpoint=/user/change-project/&id=" + encodeURIComponent(user.id) + "&newProject=" + encodeURIComponent(newProject);
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
				if (onFail) onFail();
				return;
			}
			API.successMessage("Project changed.");
			if (onSuccess) onSuccess();
		})
		.fail(function() {
			API.errorMessage("Could not change Project.");
			if (onFail) onFail();
		});
}
API.userChangePassword = function(user, currentPassword, newPassword) {
	var url = API.root + "api.php?endpoint=/user/change-password/&id=" + encodeURIComponent(user.id) + "&currentPassword=" + encodeURIComponent(currentPassword) + "&newPassword=" + encodeURIComponent(newPassword);
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
API.userChangeAudience = function(user, audience, successFunc) {
	var url = API.root + "api.php?endpoint=/user/change-audience/&id=" + encodeURIComponent(user.id) + "&audience=" + encodeURIComponent(audience);
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
			API.successMessage("Audience changed.");
			if (successFunc) successFunc();
		})
		.fail(function() {
			API.errorMessage("Could not change Audience.");
		});
}
API.queryOAuthFacebookPages = function(callbackFunction) {
	var url = API.root + "api.php?endpoint=/social/account/facebook-page/query/";
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { API.errorMessage(result); return; }
			var json = JSON.parse(result);
			if (!json.success) { API.errorMessage(json.message); return; }

			callbackFunction(json.facebookpages);
		})
		.fail(function() {
			API.errorMessage("Could not query Facebook Pages.");
		});
}
API.listOAuthFacebookPages = function(fromInit) {
	if (typeof fromInit == 'undefined') { fromInit = true; }

	var url = API.root + "api.php?endpoint=/social/account/facebook-page/list/";
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { API.errorMessage(result); return; }
			var json = JSON.parse(result);
			if (!json.success) { API.errorMessage(json.message); return; }

			for(var i = 0; i < json.facebookpages.length; ++i) {
				var acc = new OAuthFacebookPage(json.facebookpages[i]);
				impresslist.addOAuthFacebookPage(acc, false);
			}
			if (fromInit) {
				impresslist.loading.onCategoryItemLoaded('social');
			}
		})
		.fail(function() {
			API.errorMessage("Could not list Facebook Pages.");
		});
}
API.addOAuthFacebookPage = function(page_id, page_name, page_accessToken, page_image, callbackFunction) {
	var url = API.root + "api.php?endpoint=/social/account/facebook-page/add/" +
				"&page_id=" + encodeURIComponent(page_id) +
				"&page_name=" + encodeURIComponent(page_name) +
				"&page_accessToken=" + encodeURIComponent(page_accessToken) +
				"&page_image=" + encodeURIComponent(page_image);
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { API.errorMessage(result); return; }
			var json = JSON.parse(result);
			if (!json.success) { API.errorMessage(json.message); return; }

			//for(var i = 0; i < json.facebookpages.length; ++i) {

			//}

			if (typeof json.updated == 'undefined') {
				API.successMessage("Facebook Page added.");
				console.log(json);

				var fbpage = new OAuthFacebookPage(json.facebookpage);
				impresslist.addOAuthFacebookPage(fbpage, false);
			} else {
				API.successMessage("Facebook Page updated.");
				console.log(json);

				var fbpage = impresslist.findOAuthFacebookPageById(json.facebookpage.id);
				fbpage.init(json.facebookpage);
				$('#social-homepage-twitteracc-list-none').hide();
			}


			if (typeof callbackFunction != 'undefined') { callbackFunction(json); }
		})
		.fail(function() {
			API.errorMessage("Could not add Facebook Page.");
		});
}
API.removeOAuthFacebookPage = function(acc) {
	var url = API.root + "api.php?endpoint=/social/account/facebook-page/remove/&id=" + encodeURIComponent(acc.id);
	console.log(url);
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { API.errorMessage(result); return; }

			var json = JSON.parse(result);
			if (!json.success) { API.errorMessage(json.message); return; }
			API.successMessage("Faceobok Page removed.");
			impresslist.removeOAuthFacebookPage(acc);

		})
		.fail(function() {
			API.errorMessage("Could not remove Facebook Account.");
		});
}
API.listOAuthFacebookAccounts = function(fromInit) {
	if (typeof fromInit == 'undefined') { fromInit = true; }

	var url = API.root + "api.php?endpoint=/social/account/facebook/list/";
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { API.errorMessage(result); return; }
			var json = JSON.parse(result);
			if (!json.success) { API.errorMessage(json.message); return; }

			for(var i = 0; i < json.facebookaccs.length; ++i) {
				var twacc = new OAuthFacebookAccount(json.facebookaccs[i]);
				impresslist.addOAuthFacebookAccount(twacc, fromInit);
			}

			$('#social-homepage-facebookacc-list-loading').hide();
			if (json.facebookaccs.length == 0) {
				$('#social-homepage-facebookacc-list-none').show();
			}
			if (fromInit) {
				impresslist.loading.onCategoryItemLoaded('social');
			}

		})
		.fail(function() {
			API.errorMessage("Could not list OAuth Facebook Accounts.");
		});
}
API.removeOAuthFacebookAccount = function(acc) {
	var url = API.root + "api.php?endpoint=/social/account/facebook/remove/&id=" + encodeURIComponent(acc.id);
	console.log(url);
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { API.errorMessage(result); return; }

			var json = JSON.parse(result);
			if (!json.success) { API.errorMessage(json.message); return; }
			API.successMessage("Faceobok Account removed.");
			impresslist.removeOAuthFacebookAccount(acc);

		})
		.fail(function() {
			API.errorMessage("Could not remove Facebook Account.");
		});
}
API.listOAuthTwitterAccounts = function(fromInit) {
	if (typeof fromInit == 'undefined') { fromInit = true; }

	var url = API.root + "api.php?endpoint=/social/account/twitter/list/";
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
			if (fromInit) {
				impresslist.loading.onCategoryItemLoaded('social');
			}
		})
		.fail(function() {
			API.errorMessage("Could not list OAuth Twitter Accounts.");
		});
}
API.addOAuthTwitterAccount = function(request_token, request_token_secret, pin) {
	var url = API.root + "api.php?endpoint=/social/account/twitter/add/" +
				"&request_token=" + encodeURIComponent(request_token) +
				"&request_token_secret=" + encodeURIComponent(request_token_secret) +
				"&pin=" + encodeURIComponent(pin);
	console.log(url);
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { API.errorMessage(result); return; }

			var json = JSON.parse(result);
			if (!json.success) { API.errorMessage(json.message); return; }


			if (typeof json.updated == 'undefined') {
				API.successMessage("Twitter Account added.");
				console.log(json);

				var oauthtwitter = new OAuthTwitterAccount(json.twitteracc);
				impresslist.addOAuthTwitterAccount(oauthtwitter, false);
			} else {
				API.successMessage("Twitter Account updated.");
				console.log(json);

				var oauthtwitter = impresslist.findOAuthTwitterAccountById(json.twitteracc.id);
				oauthtwitter.init(json.twitteracc);
				$('#social-homepage-twitteracc-list-none').hide();
			}
		})
		.fail(function() {
			API.errorMessage("Could not add Twitter Account.");
		});
}
API.removeOAuthTwitterAccount = function(acc) {
	var url = API.root + "api.php?endpoint=/social/account/twitter/remove/&id=" + encodeURIComponent(acc.id);
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
API.getOAuthTwitterAccountInactiveFollowings = function(handle, years, successCallback, errorCallback) {

	var url = API.root + "api.php?endpoint=/social/account/twitter/tools/inactive-followings/&handle=" + encodeURIComponent(handle) + "&years=" + years;
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { API.errorMessage(result); return; }
			var json = JSON.parse(result);
			if (!json.success) { API.errorMessage(json.message); return; }

			if (successCallback) {
				successCallback(json);
			}
		})
		.fail(function() {
			API.errorMessage("Could not get OAuth Twitter Accounts Inactive Followings.");
			if (errorCallback) {
				errorCallback();
			}
		});
}
API.getOAuthTwitterAccountUnrequitedFollowings = function(handle, successCallback, errorCallback) {
	var url = API.root + "api.php?endpoint=/social/account/twitter/tools/unrequited-followings/&handle=" + encodeURIComponent(handle);
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { API.errorMessage(result); return; }
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			}

			if (successCallback) {
				successCallback(json);
			}
		})
		.fail(function() {
			API.errorMessage("Could not get OAuth Twitter Accounts Unrequited Followings.");
			if (errorCallback) {
				errorCallback();
			}
		});
}
API.doOAuthTwitterAccountUnfollow = function(fromId, handle, successCallback, errorCallback) {
	var url = API.root + "api.php?endpoint=/social/account/twitter/tools/unfollow/&id=" + encodeURIComponent(fromId) + "&handle=" + encodeURIComponent(handle);
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { API.errorMessage(result); return; }
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				return;
			}
			API.successMessage("Unfollow successful.");
			if (successCallback) {
				successCallback(json);
			}
		})
		.fail(function() {
			API.errorMessage("Could not unfollow Twitter Account.");
			if (errorCallback) {
				errorCallback();
			}
		});
}
API.sqlQuery = function(query) {
	var url = API.root + "api.php?endpoint=/admin/sql-query/&query=" + encodeURIComponent(query);
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
API.addUser = function() {
	var url = API.root + "api.php?endpoint=/admin/user/add/";
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
			API.successMessage("User added.");
			console.log(json);

			var user = new User(json.user);
			impresslist.addUser(user, false);
		})
		.fail(function() {
			API.errorMessage("Could not add User.");
		});
}
API.saveUser = function(user, forename, surname, email, color, admin, successCallback) {
	var url = API.root + "api.php?endpoint=/admin/user/save/";
	url += "&id=" + encodeURIComponent(user.id);
	url += "&forename=" + encodeURIComponent(forename);
	url += "&surname=" + encodeURIComponent(surname);
	url += "&email=" + encodeURIComponent(email);
	url += "&color=" + encodeURIComponent(color);
	url += "&admin=" + encodeURIComponent(admin);
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
			API.successMessage("User saved.");
			console.log(json);

			console.log(json);
			user.init(json.user);
			user.update();

			if (typeof successCallback != 'undefined') {
				successCallback();
			}
		})
		.fail(function() {
			API.errorMessage("Could not save User.");
		});
}
API.removeUser = function(user) {
	var url = API.root + "api.php?endpoint=/admin/user/remove/";
	url += "&id=" + encodeURIComponent(user.id);
	console.log(url);

	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { API.errorMessage(result); return; }

			var json = JSON.parse(result);
			if (!json.success) { API.errorMessage(json.message); return; }
			API.successMessage("User removed.");
			impresslist.removeUser(user);

		})
		.fail(function() {
			API.errorMessage("Could not remove User.");
		});
}
API.saveUserPassword = function(user, password1, password2, successCallback) {
	var url = API.root + "api.php?endpoint=/admin/user/change-password/";
	url += "&id=" + encodeURIComponent(user.id);
	url += "&password1=" + encodeURIComponent(password1);
	url += "&password2=" + encodeURIComponent(password2);
	console.log(url);

	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { API.errorMessage(result); return; }

			var json = JSON.parse(result);
			if (!json.success) { API.errorMessage(json.message); return; }
			API.successMessage("User password changed.");

			if (typeof successCallback != 'undefined') {
				successCallback();
			}

		})
		.fail(function() {
			API.errorMessage("Could not change User password.");
		});
}
API.addCompany = function() {
	var url = API.root + "api.php?endpoint=/superadmin/company/add/";
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
			API.successMessage("Company added.");
			console.log(json);

			var company = new Company(json.company);
			impresslist.addCompany(company, false);
		})
		.fail(function() {
			API.errorMessage("Could not add Company.");
		});
}
API.saveCompany = function(companyObj,
	name,
	keywords,
	email,
	twitter,
	facebook,
	discord_enabled,
	discord_webhookId,
	discord_webhookToken,
	successCallback,
	failCallback)
{
	var url = API.root + "api.php?endpoint=/superadmin/company/save/";
	url += "&id=" + encodeURIComponent(companyObj.id);
	url += "&name=" + encodeURIComponent(name);
	url += "&keywords=" + encodeURIComponent(keywords);
	url += "&email=" + encodeURIComponent(email);
	url += "&twitter=" + encodeURIComponent(twitter);
	url += "&facebook=" + encodeURIComponent(facebook);
	url += "&discord_enabled=" + encodeURIComponent(discord_enabled);
	url += "&discord_webhookId=" + encodeURIComponent(discord_webhookId);
	url += "&discord_webhookToken=" + encodeURIComponent(discord_webhookToken);

	API._companyRequest(companyObj, url, "Company saved!", "Could not save Company.", successCallback, failCallback);
}

API.addCompanyGame = function(companyObj, successCallback, failCallback) {
	var url = API.root + "api.php?endpoint=/superadmin/company/game/add/&company=" + encodeURIComponent(companyObj.id);
	API._companyRequest(companyObj, url, "Added game - you must now edit!", "Could not add Game.", successCallback, failCallback);
}
API.removeCompanyGame = function(companyObj, gameId, successCallback, failCallback) {
	var url = API.root + "api.php?endpoint=/superadmin/company/game/remove/&company=" + encodeURIComponent(companyObj.id) + "&game=" + encodeURIComponent(gameId);
	API._companyRequest(companyObj, url, "Removed game successfully!", "Could not remove Game.", successCallback, failCallback);
}
API.saveCompanyGame = function(companyObj, gameId, name, keywords, twitchId, successCallback, failCallback) {
	var url = API.root + "api.php?endpoint=/superadmin/company/game/save/";
	url += "&company=" + encodeURIComponent(companyObj.id);
	url += "&game=" + encodeURIComponent(gameId);
	url += "&name=" + encodeURIComponent(name);
	url += "&keywords=" + encodeURIComponent(keywords);
	url += "&twitchId=" + encodeURIComponent(twitchId);

	API._companyRequest(companyObj, url, "Game saved!", "Could not save Game.", successCallback, failCallback);
}
API.testDiscordWebhook = function(companyId) {
	$.ajax( API.root + "api.php?endpoint=/superadmin/company/webhook/discord/test/&company=" + encodeURIComponent(companyId) );
}
API._companyRequest = function(companyObj, url, successMessage, failMessage, successCallback, failCallback) {
	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') { API.errorMessage(result); return; }

			var json = JSON.parse(result);
			if (!json.success) { API.errorMessage(json.message); return; }

			API.successMessage(successMessage);
			companyObj.init(json.company);
			companyObj.update();
			if (successCallback) successCallback();
		})
		.fail(function() {
			API.errorMessage(failMessage);
			if (failCallback) failCallback();
		});
}

API.request = function(endpoint, data, successCallback, failCallback) {
	var url = API.root + "api.php?endpoint=" + encodeURIComponent(endpoint);
	for(var field in data) {
		url += "&" + field + "=" + encodeURIComponent(data[field]);
	}
	console.log(url);

	$.ajax( url )
		.done(function(result) {
			if (result.substr(0, 1) != '{') {
				API.errorMessage(result);
				failCallback();
				return;
			}
			var json = JSON.parse(result);
			if (!json.success) {
				API.errorMessage(json.message);
				failCallback();
				return;
			}
			successCallback(json);
		})
		.fail(function() {
			failCallback();
		});
}

API.successMessage = function(message) {
	$.bootstrapGrowl(message, { type: 'success',  offset: {from: 'top', amount: 70}, align:'center', delay: 2000});
}
API.infoMessage = function(message) {
	$.bootstrapGrowl(message, { type: 'info',  offset: {from: 'top', amount: 70}, align:'center', delay: 5000});
}
API.errorMessage = function(message) {
	$.bootstrapGrowl(message, { type: 'danger',  offset: {from: 'top', amount: 70}, align:'center', delay: 10000});
}
