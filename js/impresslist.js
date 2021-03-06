
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
	 	this.rowSelector = null;
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
	DBO.prototype.countrySelectHtml = function(type){
		var html = "<label for='country'>Country:</label>";
		var country = this.field('country');
		html += "	<select data-" + type + "-id='" + this.id + "' data-input-field='country' class='form-control'>";
		for(var countryCode in impresslist.config.misc.countries) {

			html += "	<option value='" + countryCode + "' " + ((countryCode==country)?"selected='true'":"") + ">" + impresslist.config.misc.countries[countryCode] + "</option>";
		}
		html += "	</select>";
		return html;
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
	DBO.prototype.filterTags = function(tagsArray) {
		if (tagsArray.length == 0) {
			this.show();
			return true;
		}

		var tags = this.field('tags');

		// doesnn't have tags field.
		if (typeof tags == 'undefined') {
			this.show();
			return true;
		}

		// has tags field and has no tags
		if (typeof tags == 'string' && tags.length == 0) {
			this.hide();
			return false;
		}

		var containsAll = true;
		for(var i = 0; i < tagsArray.length; i++) {
			if (tags.indexOf(tagsArray[i]) == -1) {
				containsAll = false;
				break;
			}
		}
		if (containsAll) {
			this.show();
			return true;
		} else {
			this.hide();
			return false;
		}
	}
	DBO.prototype.show = function() {
		if (this.rowSelector) $(this.rowSelector).show();
	}
	DBO.prototype.hide = function() {
		if (this.rowSelector) $(this.rowSelector).hide();
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


WatchedGame = function(data) {
	DBO.call(this, data);
}
	WatchedGame.prototype = Object.create(DBO.prototype)
	WatchedGame.prototype.constructor = WatchedGame;
	WatchedGame.prototype.init = function(data) {
		DBO.prototype.init.call(this, data);
		this.id = parseInt(this.field('id'));
		this.coverage = this.field('coverage');
		this.rowSelector = "#watchedgames [data-watchedgame-id='" + this.id + "']";
	}
	WatchedGame.prototype.filter = function(text) {
		if (this.search(text)) {
			this.show();
			return true;
		} else {
			this.hide();
			return false;
		}
	}
	WatchedGame.prototype.search = function(text) {
		return true;
	}
	WatchedGame.prototype.createItem = function(fromInit) {
		console.log(this);

		var url = "";
		var iconurl = "images/favicon.png";
		var html = "";
		html += "<br/> \
				<div data-watchedgame-id='" + this.field('id') + "' class='media'>	\
					<div class='oa'>\
						<div class='fl'>\
							<h4 data-watchedgame-id='" + this.id + "' data-field='name'>" + this.field('name') + "</h4>\
							<!-- <p data-watchedgame-id='" + this.id + "' data-field='keywords' style='font-style:italic'>" + this.field('keywords') + "</p><br/> -->\
						</div>\
						<div class='fr'>\
							<button id='edit-watchedgame' class='fr btn btn-default btn-sm' data-watchedgame-id='" + this.field('id') + "'  data-toggle='modal' data-target='.watchedgame_modal' ><span class='glyphicon glyphicon-pencil'></span></button> \
						</div>\
					</div>\
					<h5>Coverage:</h5>\
					<div data-watchedgame-id='" + this.id + "' data-field='items'>\
					</div>\
				</div>\
				";
		if (fromInit) {
			$('#watchedgames').append(html);
		} else {
			$('#watchedgames').prepend(html);
		}
		this.update();

		for (var i = 0; i < this.coverage.length; i++) {
			var c = new Coverage(this.coverage[i])
			c.createItem(fromInit, "[data-watchedgame-id='" + this.id + "'][data-field='items']");
		}
		if (this.coverage.length == 0) {
			var err = "<div class='alert alert-info' role='alert'> \
							<span class='glyphicon glyphicon-thumbs-down' aria-hidden='true'></span> \
							<span class='sr-only'>Error:</span> \
							Dang! There is currently no coverage for this Watched Game.\
						</div>";
			$("[data-watchedgame-id='" + this.id + "'][data-field='items']").append(err);
		}

		var t = this;
		$("#edit-watchedgame[data-watchedgame-id='" + this.id + "']").click(function() { t.open(); });
	}
	WatchedGame.prototype.update = function() {
		var selector = "data-watchedgame-id";

		$("[" + selector + "='" + this.id + "'][data-field='name']").html(this.field('name'));
		//$("[" + selector + "='" + this.id + "'][data-field='url']").attr('href', this.field('url'));
		//$("[" + selector + "='" + this.id + "'][data-field='url']").html(this.field('title'));
		//$("[" + selector + "='" + this.id + "'][data-field='utime']").html( impresslist.util.relativetime_contact(this.field('utime')) );
	}
	WatchedGame.prototype.open = function() {

		var html = "<div class='modal fade watchedgame_modal' tabindex='-1' role='dialog'> \
						<div class='modal-dialog'> \
							<div class='modal-content' style='padding:5px;'> \
								<div style='min-height:100px;padding:20px;'>";

			html += "				<h3>Edit Watched Game</h3>";
			html += "				<form role='form' class='oa' onsubmit='return false;'>	\
										<div class='row'>\
											<div class='form-group col-md-5'>\
												<label>Name:</label> \
												<input id='watchedgame-edit-name' class='form-control' type='text' value='" + this.field('name') + "' style='width:100%;'/>\
											</div>\
										</div> \
										<div class='row'>\
											<div class='form-group col-md-5'>\
												<label>Keywords/Search Terms:</label> \
												<input id='watchedgame-edit-keywords' class='form-control' type='text' value='" + this.field('keywords') + "' style='width:100%;'/>\
											</div>\
										</div> \
										<div class='fl'> \
											<button id='save_watchedGameId" + this.id + "' type='submit' class='btn btn-primary'>Save</button>";
			html += "						&nbsp;<button id='close_watchedGameId" + this.id + "' type='submit' class='btn btn-default'>Close</button>";
			html += " 					</div><div class='fr'> \
											<button id='delete_watchedGameId" + this.id + "' type='submit' class='btn btn-danger'>Remove</button> \
										</div>\
									</form>";

		html += "				</div>\
							</div>\
						</div>\
					</div>";
		$('#modals').html(html);

		//$('#coverage-edit-title').attr('value', this.field('title'));


		var watchedGame = this;
		$("#save_watchedGameId" + this.id).click(function() { watchedGame.save(); });
		$("#close_watchedGameId" + this.id).click(function() { watchedGame.close(); });
		$("#delete_watchedGameId" + this.id).click(function() { watchedGame.remove(); });
	}
	WatchedGame.prototype.onAdded = function(fromInit) {
		this.createItem(fromInit);
	}
	WatchedGame.prototype.onRemoved = function() {
		this.removeItem();
		this.close();
	}
	WatchedGame.prototype.save = function() {
		var name = $('#watchedgame-edit-name').val();
		var keywords = $('#watchedgame-edit-keywords').val();
		API.saveWatchedGame(this, name, keywords);
	}
	WatchedGame.prototype.close = function() {
		$('.watchedgame_modal').modal('hide');
	}
	WatchedGame.prototype.remove = function() {
		API.removeWatchedGame(this);
	}


Coverage = function(data) {
	DBO.call(this, data);
}
	Coverage.prototype = Object.create(DBO.prototype)
	Coverage.prototype.constructor = Coverage;
	Coverage.prototype.init = function(data) {
		DBO.prototype.init.call(this, data);
		this.id = parseInt(this.field('id'));
		this.rowSelector = "#coverage [data-coverage-id='" + this.id + "']";
	}
	Coverage.prototype.filter = function(text) {
		if (this.search(text)) {
			this.show();
			return true;
		} else {
			this.hide();
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
		} else if (this.field('type') == "twitchchannel") {
			// Search all YOuTubers too.
			for(var i = 0; i < impresslist.twitchchannels.length; ++i) {
				if (impresslist.twitchchannels[i].field('id') == this.field('twitchchannel')) {
					var pub = impresslist.findTwitchChannelById( this.field('twitchchannel') );
					ret = pub.search(text);
					if (ret) { return ret; }
				}
			}
		}

		return false;
	}
	Coverage.prototype.createItem = function(fromInit, parent) {
		parent = parent || "#coverage";

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
			html = "		<div class='coverage-row' data-coverage-id='" + this.field('id') + "' data-coverage-type='" + this.field('type') + "'>	\
								<div class='media-left' style='min-width:50px; width:50px;'> \
									<img class='media-object fl' style='width:50px;text-align:right;' src='/images/icon-web.png' alt='Image'></a> \
								</div> \
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
		}
		else if (type == "youtuber") {
			//var youtuber = impresslist.findYoutuberById( this.field('youtuber') );
			iconurl = this.field('thumbnail');
			//url = youtuber.field('url');;

			html = "		<div class='coverage-row' data-youtube-coverage-id='" + this.field('id') + "' data-coverage-type='" + this.field('type') + "'>	\
								<div class='media-left' style='min-width:50px; width:50px;'> \
									<img class='media-object fl' style='width:50px;text-align:right;' src='/images/icon-youtube.png' alt='Image'></a> \
								</div> \
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
		else if (type == "twitchchannel") {
			//var youtuber = impresslist.findYoutuberById( this.field('youtuber') );
			iconurl = this.field('thumbnail');
			// console.log(iconurl);
			// iconurl = iconurl.replace("\%{width}", "300");
			// iconurl = iconurl.replace("\%{height}", "300");
			// console.log(iconurl);
			//url = youtuber.field('url');;

			html = "		<div data-twitchchannel-coverage-id='" + this.field('id') + "' data-coverage-type='" + this.field('type') + "' class='media'>	\
								<div class='media-left' style='min-width:50px; width:50px;'> \
									<img class='media-object fl' style='width:50px;text-align:right;' src='/images/icon-twitch.png' alt='Image'></a> \
								</div> \
								<div class='media-left'> \
									<a href='" + url + "'><img data-twitchchannel-coverage-id='" + this.id + "' data-field='thumbnail' class='media-object' width=64 src='" + encodeURIComponent(iconurl) + "' alt='Image'></a> \
								</div> \
								<div class='media-body'> \
									<div class='fr' style='text-align:right;'>\
										<p style='margin-bottom:5px;font-style:italic;'><span data-twitchchannel-coverage-id='" + this.id + "' data-field='utime' >" + impresslist.util.relativetime_contact(this.field('utime')) + "</span></p>\
										<p data-twitchchannel-coverage-id='" + this.id + "' data-field='thanked'></p>\
									</div> \
									<h4 data-twitchchannel-coverage-id='" + this.id + "' data-field='name' data-youtuber-id='" + this.fields['twitchchannel'] + "' class='media-heading' >Unknown Twitch Channel</h4> \
									<p><a data-twitchchannel-coverage-id='" + this.id + "' data-field='url' href='" + this.field('url') + "' target='new'>" + this.field('title') + "</a><br/>\
								</div> \
								<div class='media-right'> \
									<button id='edit-twitchchannel-coverage' class='btn btn-default btn-lg' data-twitchchannel-coverage-id='" + this.field('id') + "'  data-toggle='modal' data-target='.coverage_modal' ><span class='glyphicon glyphicon-pencil'></span></button> \
								</div> \
							</div>";
		}
		if (fromInit) {
			$(parent).append(html);
		} else {
			$(parent).prepend(html);
		}
		this.update();


		if (type == "publication") {
			var t = this;
			$("#edit-coverage[data-coverage-id='" + this.id + "']").click(function() { t.open(); });
		}
		else if (type == "youtuber") {
			var t = this;
			$("#edit-youtube-coverage[data-youtube-coverage-id='" + this.id + "']").click(function() { t.open(); });
		}
		else if (type == "twitchchannel") {
			var t = this;
			$("#edit-twitchchannel-coverage[data-twitchchannel-coverage-id='" + this.id + "']").click(function() { t.open(); });
		}

	}
	Coverage.prototype.removeItem = function() {
		if (this.field('type') == 'publication') {
			$(".media[data-coverage-id='" + this.field('id') + "']").remove();
		}
		else if (this.field('type') == 'youtuber') {
			$(".media[data-youtube-coverage-id='" + this.field('id') + "']").remove();
		}
		else if (this.field('type') == 'twitchchannel') {
			$(".media[data-twitchchannel-coverage-id='" + this.field('id') + "']").remove();
		}
	}
	Coverage.prototype.getPersonName = function() {
		var p = this.fields['person'];
		if (p > 0) {
			return impresslist.findPersonById(p).fullname();
		} else if (this.fields['youtuber'] > 0) {
			return "";
		} else if (this.fields['twitchchannel'] > 0) {
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

		var twitchchannelId = "";
		var twitchchannelName = "";
		if (this.fields['twitchchannel'] > 0) {
			twitchchannelId = this.fields['twitchchannel'];
			twitchchannelName = impresslist.findTwitchChannelById(twitchchannelId).field('name');
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
			else if (this.field('type') == 'twitchchannel') {
				html += "					<div class='row'>\
												<div class='form-group col-md-2'>\
													<label>Twitchchannel:&nbsp; </label> \
													<input id='coverage-edit-twitchchannel-id' class='form-control' type='text' value='" + twitchchannelId + "' style='width:100%;'/>\
												</div>\
												<div class='form-group col-md-4'>\
													<label>&nbsp; </label> \
													<input id='coverage-edit-twitchchannel-search' data-twitchchannel-coverage-id='" + this.id + "' data-input-field='twitchchannel' class='form-control' type='text' value='" + twitchchannelName + "' placeholder='Search...' />\
												</div>\
												<div id='coverage-edit-twitchchannel-results-container' class='form-group col-md-6' style='display:none;'>\
													<label>Results:</label>\
													<table class='table table-striped' style='margin-bottom:0px;'>\
														<tbody id='coverage-edit-twitchchannel-results'> \
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
		} else if (this.field('type') == 'twitchchannel') {
			$("#coverage-edit-twitchchannel-search").keyup(function() {
				var searchfield = $(this);
				var text = $(this).val().toLowerCase();
				if (text.length == 0) {
					$("#coverage-edit-twitchchannel-results").html("");
					$('#coverage-edit-twitchchannel-results-container').hide();
					return;
				}
				var html = "";
				for(var i = 0; i < impresslist.twitchchannels.length; i++) {
					var include = impresslist.twitchchannels[i].search(text);
					if (include) {
						html += "	<tr class='table-list' data-twitchchannel-id='" + impresslist.twitchchannels[i].id + "' data-coverage-edit-twitchchannel-result='true' >\
										<td>" + impresslist.twitchchannels[i].name + "</td> \
									</tr>";
					}
				}
				if (html.length == 0) {
					html += "	<tr> <td colspan='2'>No Results</td> </tr>";
				}
				$("#coverage-edit-twitchchannel-results").html(html);
				$('#coverage-edit-twitchchannel-results-container').show();

				$("[data-coverage-edit-twitchchannel-result='true']").click(function() {
					var twId = $(this).attr("data-twitchchannel-id");
					$('#coverage-edit-twitchchannel-id').val("" + twId);
					$("#coverage-edit-twitchchannel-search").val( $(this).find('td').html() );
					$("#coverage-edit-twitchchannel-results").html("");
					$('#coverage-edit-twitchchannel-results-container').hide();
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
									<td>" + impresslist.people[i].fullname() + "</td> \
								</tr>";
				}// fullname
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
			var pub = impresslist.findPublicationById(this.fields['publication']);
			if (!pub) {
				publicationName = "Unknown";
			} else {
				publicationName = pub.name;
			}
		}

		if (this.field('type') == 'youtuber') {
			publicationName = "Unknown Youtuber";
			if (this.fields['youtuber'] > 0) {
				youtuberObj = impresslist.findYoutuberById(this.fields['youtuber']);
				if (youtuberObj != null) {
					publicationName = youtuberObj.name;
				}
			}

			selector = "data-youtube-coverage-id";

			$("[" + selector + "='" + this.id + "'][data-field='thumbnail']").attr('src', this.field('thumbnail'));
		}
		if (this.field('type') == 'twitchchannel') {
			publicationName = "Unknown Twitch Channel";
			if (this.fields['twitchchannel'] > 0) {
				youtuberObj = impresslist.findTwitchChannelById(this.fields['twitchchannel']);
				if (youtuberObj != null) {
					publicationName = youtuberObj.name;
				}
			}

			selector = "data-twitchchannel-coverage-id";

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
		else if (this.field('type') == 'twitchchannel') {
			var title = $('#coverage-edit-title').val();
			var url = $('#coverage-edit-url').val();
			var timestamp = moment($('#coverage-edit-timestamp').val(), "L h:mma").format("X");
			var twitchchannel = $('#coverage-edit-twitchchannel-id').val();
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
			API.saveTwitchChannelCoverage(this, twitchchannel, person, title, url, timestamp, thanked);
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
		} else if (this.field('type') == 'twitchchannel') {
			API.removeTwitchChannelCoverage(this);
		}
	}

Audience = function(data) {
	DBO.call(this, data);
}
	Audience.prototype = Object.create(DBO.prototype);
	Audience.prototype.constructor = Audience;
	Audience.prototype.init = function(data) {
		DBO.prototype.init.call(this, data);
		this.id = parseInt(this.field('id'));
		this.name = this.field('name');
	}
	Audience.prototype.onAdded = function() {
		var html = "<div data-audience-container data-audience-id='" + this.id + "' style='padding:5px;'> \
						<p><b><span data-field='name'>" + this.name + "</span></b></p> \
						<p>Automations:</p>\
						<p>No automations set.</p>\
						<button class='btn btn-primary'>Add Automation</button>\
					</div><hr/>";
		$('#admin-audiences-list').append(html);
	}


Game = function(data) {
	DBO.call(this, data);
}
	Game.prototype = Object.create(DBO.prototype);
	Game.prototype.constructor = Game;
	Game.prototype.init = function(data) {
		DBO.prototype.init.call(this, data);
		this.id = parseInt(this.field('id'));
		this.name = this.field('name');
	}
	Game.prototype.iconUrl = function() {
		if (this.fields['iconurl'] && this.fields['iconurl'].length > 0) {
			return this.fields['iconurl'];
		}
		return 'images/favicon.png';
	}
	Game.prototype.onAdded = function() {
		var html = "<div data-project-container data-project-id='" + this.id+ "' style='padding:5px;'> \
						<img src='" + this.iconUrl() + "' style='width:32px;height:32px' /> \
						<span data-field='name'>" + this.name + "</span> \
					</div>";
		$('#admin-projects-list').append(html);
	}

Company = function(data) {
	DBO.call(this, data);
}
	Company.prototype = Object.create(DBO.prototype)
	Company.prototype.constructor = Company;
	Company.prototype.init = function(data) {
		DBO.prototype.init.call(this, data);
		this.id = parseInt(this.field('id'));
		this.name = this.field('name');
	}
	Company.prototype.update = function(data) {
		$('#superadmin-companies-list tr[data-company-id=' + this.id + '] td[data-field="id"]').html(this.id);
		$('#superadmin-companies-list tr[data-company-id=' + this.id + '] td[data-field="name"]').html(this.field('name'));
		$('#superadmin-companies-list tr[data-company-id=' + this.id + '] td[data-field="games"]').html(this.buildGamesStr());
		$('#superadmin-companies-list tr[data-company-id=' + this.id + '] td[data-field="discord"]').html(this.buildDiscordStr());

	}
	Company.prototype.buildDiscordStr = function() {
		return ((this.field('discord_enabled') == "1")?"Discord":"N/A");
	}
	Company.prototype.buildGamesStr = function() {
		var games = this.field("games");
		var gamesStr = "None";
  		if (games.length > 0) { gamesStr = ""; }
  		for(var i = 0; i < games.length; i++) {
  			gamesStr += " • " + games[i].name + "<br/>";
  		}
  		return gamesStr;
	}

	Company.prototype.onAdded = function() {
		var html = "<tr data-company-container data-company-id='" + this.id + "'>\
						<td data-field='name'>" + this.name + "</td>\
						<td data-field='games'>" + this.buildGamesStr() + "</td>\
						<td data-field='discord'>" + this.buildDiscordStr() + "</td>\
						<td><button id='company-edit-open-" + this.id + "' class='btn btn-primary btn-sm fr'>Edit</button></td>\
					</tr>";
		$('#superadmin-companies-list').append(html);

		var thiz = this;
		$('#company-edit-open-' + this.id).click(function() { thiz.openEditModal(); });
	}
	Company.prototype.clearEditModal = function() {
		$('.modal-backdrop').remove();
		$('.editcompany_modal').remove();
	}
	Company.prototype.openEditModal = function() {
		var html = "<div class='modal fade editcompany_modal' tabindex='-1' role='dialog'> \
						<div class='modal-dialog'> \
							<div class='modal-content' style='padding:5px;'> \
								<div style='min-height:200px;padding:20px;'> \
									<h3 data-company-id='" + this.id + "' data-field='name'>" + this.field("name") + "</h3> \
									<form role='form' style='overflow:hidden' onsubmit='return false;'>	\
										<div class='row'>\
											<div class='form-group col-md-6'>\
												<label for='name'>Name:&nbsp; </label> \
												<input data-company-id='" + this.id + "' data-input-field='name' class='form-control' type='text' value='" + this.field('name') + "' />\
											</div>\
											<div class='form-group col-md-6'>\
												<label for='name'>Keywords:&nbsp; </label> \
												<input data-company-id='" + this.id + "' data-input-field='keywords' class='form-control' type='text' value='" + this.field('keywords') + "' />\
											</div>\
										</div>\
										<div class='row'>\
											<div class='form-group col-md-12'>\
												<label for='email'>Email:&nbsp; </label> \
												<input data-company-id='" + this.id + "' data-input-field='email' class='form-control' type='text' value='" + this.field('email') + "' />\
											</div>\
										</div>\
										<div class='row'>\
											<div class='form-group col-md-6'>\
												<label for='twitter'>Twitter Username:&nbsp; </label> \
												<input data-company-id='" + this.id + "' data-input-field='twitter' class='form-control' type='text' value='" + this.field('twitter') + "' />\
											</div>\
											<div class='form-group col-md-6'>\
												<label for='facebook'>Facebook:&nbsp; </label> \
												<input data-company-id='" + this.id + "' data-input-field='facebook' class='form-control' type='text' value='" + this.field('facebook') + "' />\
											</div>\
										</div>\
										<div class='row'>\
											<div class='form-group col-md-2'>\
												<label class='checkbox-inline'>\
												<input data-company-id='" + this.id + "' data-input-field='discord_enabled' type='checkbox' " + (((this.field('discord_enabled')==1)?"checked":"")) + "/>\
												<strong>Discord</strong></label>\
											</div>\
											<div class='form-group col-md-4'>\
												<label for='discord_webhookId'>Webhook Id:&nbsp; </label> \
												<input data-company-id='" + this.id + "' data-input-field='discord_webhookId' class='form-control' type='text' value='" + this.field('discord_webhookId') + "' />\
											</div>\
											<div class='form-group col-md-6'>\
												<label for='discord_webhookToken'>Webhook Token:&nbsp; </label> \
												<input data-company-id='" + this.id + "' data-input-field='discord_webhookToken' class='form-control' type='text' value='" + this.field('discord_webhookToken') + "' />\
											</div>\
										</div>\
										<div> \
											<button id='company-edit-submit' type='submit' class='btn btn-primary'>Save</button> \
											<button id='company-edit-submit' type='submit' class='btn btn-warning' onclick='API.testDiscordWebhook(" + this.id + ")'>Test Webhook</button> \
											<button id='company-edit-close' type='submit' class='btn btn-default'>Close</button> \
										</div>\
										<hr/>\
										<div class='row'>\
											<div class='form-group col-md-12'>\
												<h4>Games</h4>\
												<table class='table'>\
													<thead>\
														<tr>\
															<th>Name</th>\
															<th width=120>Keywords</th>\
															<th width=120>Blackwords</th>\
															<th width=50>Twitch ID</th>\
															<th></th>\
														</tr>\
													</thead>\
													<tbody id='company-edit-games-tbody'>\
													</tbody>\
												</table>\
											</div>\
										</div>\
										<div class='row'>\
											<div class='form-group col-md-12'>\
												<h4>Coverage Stats</h4>\
												<div id='company-game-stats-container'>\
													<p><i>Click a stats button to generate...</p></i>\
												</div>\
											</div>\
										</div>\
									</form> \
								</div>\
							</div>\
						</div>\
					</div>";
		$('#modals').html(html);

		$('.modal-dialog').css('width', '800px');

		var thiz = this;
		var games = this.field("games");
		for(var i = 0; i < games.length; i++) {
			var gameHtml = "";
			gameHtml += "<tr>\
							<td><input data-game-id='" + games[i].id + "' data-field='name' class='form-control' type='text' value='" + games[i].name + "'/></td>\
							<td><input data-game-id='" + games[i].id + "' data-field='keywords' class='form-control' type='text' value='" + games[i].keywords + "'/></td>\
							<td><input data-game-id='" + games[i].id + "' data-field='blackwords' class='form-control' type='text' value='" + games[i].blackwords + "'/></td>\
							<td><input data-game-id='" + games[i].id + "' data-field='twitchId' class='form-control' type='text' value='" + games[i].twitchId + "'/></td>\
							<td>\
								<!-- <button id='refresh-company-game-twitch-" + games[i].id + "' data-game-id='" + games[i].id + "' class='btn btn-warning btn-sm'>Refresh Twitch</button> -->\
								<button id='save-company-game-" + games[i].id + "' data-game-id='" + games[i].id + "' class='btn btn-primary btn-sm'><span class='glyphicon glyphicon-floppy-disk' aria-hidden='true'></span></button>\
								<button id='generatestats-company-game-" + games[i].id + "' data-game-id='" + games[i].id + "' class='btn btn-warning btn-sm'><span class='glyphicon glyphicon-signal' aria-hidden='true'></span></button>\
								<button id='remove-company-game-" + games[i].id + "' data-game-id='" + games[i].id + "' class='btn btn-danger btn-sm'><span class='glyphicon glyphicon-trash' aria-hidden='true'></span></button>\
							</td>\
						 </tr>";
			$('#company-edit-games-tbody').append(gameHtml);

			$("#save-company-game-" + games[i].id).click(function() {
				var gameId = $(this).attr('data-game-id');
				var gameName = $("[data-game-id='" + gameId + "'][data-field='name']").val();
				var gameKeywords = $("[data-game-id='" + gameId + "'][data-field='keywords']").val();
				var gameBlackwords = $("[data-game-id='" + gameId + "'][data-field='blackwords']").val();
				var gameTwitchId = $("[data-game-id='" + gameId + "'][data-field='twitchId']").val();

				API.saveCompanyGame(thiz, gameId, gameName, gameKeywords, gameBlackwords, gameTwitchId);
			});
			$("#remove-company-game-" + games[i].id).click(function() {
				var gameId = $(this).attr('data-game-id');
				API.removeCompanyGame(thiz, gameId, function() {
					thiz.clearEditModal();
					thiz.openEditModal();
				});
			});

			$("#generatestats-company-game-" + games[i].id).click(function() {
				var gameId = $(this).attr('data-game-id');
				API.request("/superadmin/company/game/coverage-stats/", {game: gameId}, function(data) {
					$('#company-game-stats-container').html("<b>" + data.game.name + "</b><br/>" + impresslist.util.templates.StatsTable(data.stats));
				}, function(){});

			});


		}
		$('#company-edit-games-tbody').append("<button id='superadmin-add-company-game' data-company-id='" + this.id + "' class='btn btn-success' style='margin-top:10px;'>Add Game</button>");
		$('#superadmin-add-company-game').click(function(){
			API.addCompanyGame(thiz, function() {
				thiz.clearEditModal();
				thiz.openEditModal();
			});
		});

		$('.editcompany_modal').modal("show");


		$('#company-edit-submit').click(function() {
			var name = $("[data-company-id='" + thiz.id + "'][data-input-field='name']").val();
			var keywords = $("[data-company-id='" + thiz.id + "'][data-input-field='keywords']").val();
			var email = $("[data-company-id='" + thiz.id + "'][data-input-field='email']").val();
			var twitter = $("[data-company-id='" + thiz.id + "'][data-input-field='twitter']").val();
			var facebook = $("[data-company-id='" + thiz.id + "'][data-input-field='facebook']").val();
			var discord_enabled = $("[data-company-id='" + thiz.id + "'][data-input-field='discord_enabled']").prop("checked");
			var discord_webhookId = $("[data-company-id='" + thiz.id + "'][data-input-field='discord_webhookId']").val();
			var discord_webhookToken = $("[data-company-id='" + thiz.id + "'][data-input-field='discord_webhookToken']").val();

			//console.log(name, keywords, email, twitter, facebook, discord_enabled, discord_webhookId, discord_webhookToken);

			API.saveCompany(thiz, name, keywords, email, twitter, facebook, discord_enabled, discord_webhookId, discord_webhookToken,
				function(data) { $('.editcompany_modal').modal("hide"); },
				function(err) {}
			);
		});
		$('#company-edit-close').click(function() {
			$('.editcompany_modal').modal("hide");
		});
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
	User.prototype.openChangeProject = function() {
		var curProj = impresslist.config.user.game;
		var html = "<div class='modal fade changeproject_modal' tabindex='-1' role='dialog'> \
						<div class='modal-dialog'> \
							<div class='modal-content' style='padding:5px;'> \
								<div style='min-height:100px;padding:20px;'> \
									<h3>Change Project</h3> \
									<form role='form' class='oa' onsubmit='return false;'>	\
										<div class='form-group'>\
											<select id='user-change-project-new' data-project-id='" + this.id + "' data-input-field='id' class='form-control'>";
											for(var i = 0; i < impresslist.games.length; i++) {
												html += "<option value='" + impresslist.games[i].id + "' " + ((impresslist.games[i].id==curProj)?"selected='true'":"") + ">" + impresslist.games[i].name + "</option>\n";
											}
		html +=	"							</select>\
										</div>\
										<div class='fl'> \
											<button id='user-change-project-submit' type='submit' class='btn btn-primary'>Save</button> \
											&nbsp;<button id='user-change-project-close' type='submit' class='btn btn-default'>Close</button> \
										</div>\
									</form> \
								</div>\
							</div>\
						</div>\
					</div>";
		$('#modals').html(html);

		var thiz = this;
		$('#user-change-project-submit').click(function() {
			var newProject = $('#user-change-project-new').val();
			console.log(newProject);
			API.userChangeProject(thiz, newProject, function(){
				window.location.href = "/";
			});

			$('.changeproject_modal').modal("hide");
		});
		$('#user-change-project-close').click(function() {
			$('.changeproject_modal').modal("hide");
		});
	}
	User.prototype.openChangeAudience = function() {
		var curAudience = impresslist.config.user.audience;
		var html = "<div class='modal fade changeaudience_modal' tabindex='-1' role='dialog'> \
						<div class='modal-dialog'> \
							<div class='modal-content' style='padding:5px;'> \
								<div style='min-height:100px;padding:20px;'> \
									<h3>Change Audience</h3> \
									<form role='form' class='oa' onsubmit='return false;'>	\
										<div class='form-group'>\
											<select id='user-change-audience-new' data-audience-id='" + this.id + "' data-input-field='id' class='form-control'>";
											for(var i = 0; i < impresslist.audiences.length; i++) {
												html += "<option value='" + impresslist.audiences[i].id + "' " + ((impresslist.audiences[i].id==curAudience)?"selected='true'":"") + ">" + impresslist.audiences[i].name + "</option>\n";
											}
		html +=	"							</select>\
										</div>\
										<div class='fl'> \
											<button id='user-change-audience-submit' type='submit' class='btn btn-primary'>Save</button> \
											&nbsp;<button id='user-change-audience-close' type='submit' class='btn btn-default'>Close</button> \
										</div>\
									</form> \
								</div>\
							</div>\
						</div>\
					</div>";
		$('#modals').html(html);

		var thiz = this;
		$('#user-change-audience-submit').click(function() {
			var newProject = $('#user-change-audience-new').val();
			console.log(newProject);
			API.userChangeAudience(thiz, newProject, function(){
				window.location.href = window.location.href;
			});

			$('.changeaudience_modal').modal("hide");
		});
		$('#user-change-audience-close').click(function() {
			$('.changeaudience_modal').modal("hide");
		});
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

	User.prototype.createRow = function() {
		var html = "";
		html += "<div data-user='" + this.id + "' data-user-row class='oa'> \
					<p class='fl'> \
						<span data-user='" + this.id + "' data-field='id'></span>. \
						<b><span data-user='" + this.id + "' data-field='forename'></span></b><br/> \
						<span data-user='" + this.id + "' data-field='num_emails'></span> emails - \
						last active <span data-user='" + this.id + "' data-field='lastactive'></span> \
					</p> \
					&nbsp;<button data-user='" + this.id + "' class='fr btn btn-sm btn-primary admin-edit-user-password'>Change Password</button> \
					&nbsp;<button data-user='" + this.id + "' class='fr btn btn-sm btn-primary admin-edit-user' style='margin-right:5px;'>Edit</button>&nbsp; \
				</div>";
		$('#admin-users-list').append(html);

		var th = this;
		$('.admin-edit-user[data-user='+this.id+']').click(function() { th.open() });
		$('.admin-edit-user-password[data-user='+this.id+']').click(function() { th.openChangePasswordAdmin() });

		this.update();
	}
	User.prototype.removeRow = function() {
		$('div[data-user=' + this.id + '][data-user-row]').remove();
	}
	User.prototype.open = function() {
		$('#edit-user-forename').val( this.field('forename') );
		$('#edit-user-surname').val( this.field('surname') );
		$('#edit-user-email').val( this.field('email') );
		$('#edit-user-color').val( this.field('color') );
		$('#edit-user-admin').prop("checked", (this.field('admin')==1)?true:false);
		// edit-user-password
		$('#admin-edit-user-form').show();

		var th = this;

		$('#nav-save-user').unbind('click');
		$('#nav-save-user').click(function() { th.save() } );

		$('#nav-close-user').unbind('click');
		$('#nav-close-user').click(function() { th.close() } );

		$('#nav-delete-user').unbind('click');
		$('#nav-delete-user').click(function(){ API.removeUser(th); });
	}
	User.prototype.save = function() {
		var th = this;
		var forename = $('#edit-user-forename').val();
		var surname = $('#edit-user-surname').val();
		var email = $('#edit-user-email').val();
		var color = $('#edit-user-color').val();
		var admin = $('#edit-user-admin').prop('checked');
		API.saveUser(this, forename, surname, email, color, admin, function() {
			th.close();
		});
	}
	User.prototype.close = function() {
		$('#admin-edit-user-form').hide();
	}
	User.prototype.update = function() {
		$('#admin-users-list [data-user=' + this.id + '][data-field=id]').html(this.id);
		$('#admin-users-list [data-user=' + this.id + '][data-field=forename]').html(this.field('forename'));
		$('#admin-users-list [data-user=' + this.id + '][data-field=forename]').css('color', this.field('color'));
		$('#admin-users-list [data-user=' + this.id + '][data-field=num_emails]').html(this.field('num_emails'));
		$('#admin-users-list [data-user=' + this.id + '][data-field=lastactive]').html(impresslist.util.relativetime_contact(this.field('lastactivity')));
	}
	User.prototype.onAdded = function() {
		this.createRow();
	}
	User.prototype.onRemoved = function() {
		this.removeRow();
		this.close();
	}

	User.prototype.openChangePasswordAdmin = function() {
		$('#edit-user-password').val( '' );
		$('#edit-user-password-confirm').val( '' );

		$('#admin-edit-user-password-form').show();

		var th = this;
		$('#nav-save-user-password').unbind('click');
		$('#nav-save-user-password').click(function() { th.savePassword() } );

		$('#nav-close-user-password').unbind('click');
		$('#nav-close-user-password').click(function() { th.closeChangePasswordAdmin() } );
	}
	User.prototype.savePassword = function() {
		var th = this;
		var password1 = $('#edit-user-password').val();
		var password2 = $('#edit-user-password-confirm').val();
		API.saveUserPassword(this, password1, password2, function() {
			th.closeChangePasswordAdmin();
		});
	}
	User.prototype.closeChangePasswordAdmin = function() {
		$('#admin-edit-user-password-form').hide();
	}

//youtubermodal search
YouTuberBatchModal = function() {

}
	YouTuberBatchModal.open = function(){

		var html = "<div class='modal fade youtuber_batch_add_modal' tabindex='-1' role='dialog'> \
						<div class='modal-dialog'> \
							<div class='modal-content' style='padding:5px;'> \
								<div style='min-height:100px;padding:20px;'> \
									<h3>Youtuber Batch Add</h3> \
									<form role='form' class='oa' onsubmit='return false;'>	\
										<div class='row'>\
											<div class='form-group col-md-5'>\
												<input id='youtuber_batch_add_modal_search_text' class='form-control' type='text' value='' placeholder='Search'/>\
											</div>\
											<div class='form-group col-md-3'>\
												<button id='youtuber_batch_add_modal_search_relevance' type='submit' class='btn btn-primary'>Search (Relevance)</button>\
											</div>\
											<div class='form-group col-md-2'>\
												<button id='youtuber_batch_add_modal_search_latest' type='submit' class='btn btn-primary' style='margin-left:10px;'>Search (Latest)</button>\
											</div>\
										</div>\
									</form>\
									<div id='youtuber_batch_add_no_results' style='display:none'>\
										<div class='alert alert-info' role='alert'> \
											<span class='glyphicon glyphicon-thumbs-down' aria-hidden='true'></span> \
											<span class='sr-only'>Error:</span> \
											Dang! There were no results.\
										</div>\
									</div>\
									<div id='youtuber_batch_add_results_container'  style='display:none'>\
										<h4>Results</h4>\
										<div id='youtuber_batch_add_results_list' class='oa'>\
										</div>\
									</div>\
								</div>\
							</div>\
						</div>\
					</div>";
		$('#modals').html(html);
		console.log(html);

		$('.youtuber_batch_add_modal').modal('show');

		var resultsFunc = function(results) {
			$('#youtuber_batch_add_results_list').html('');
			if (results.length == 0) {
				$('#youtuber_batch_add_no_results').show();
				$('#youtuber_batch_add_results_container').hide();
			}
			else if (results.length > 0) {
				$('#youtuber_batch_add_no_results').hide();
				$('#youtuber_batch_add_results_container').show();
			}

			for(var i = 0; i < results.length; i++) {
				var channelExists = impresslist.findYoutuberByChannelId(results[i].channel.id) != null;
				var html = "";
				html += "<div class='oa' style='padding-bottom:10px;'>";
				html += "<div class='fl' style='width:80px'><img src='" + results[i].video.thumbnail + "' width=80px/></div>";
				html += "<div class='fl' style='width:400px;padding-left:10px;'>";
				html += 	"<p><b><a href='http://youtube.com/watch?v=" + results[i].video.id + "' target='new'>" + results[i].video.title + "</a></b></p>"
				html += 	"<p><b>Channel:</b> <a href='http://www.youtube.com/channel/" + results[i].channel.id + "'><i>" + results[i].channel.title + "</i></a></p>";
				if (!channelExists) {
					html += 	"<button \
									id='youtuber_batch_add_channel_" + results[i].channel.id + "'  \
									data-channel-id='" + results[i].channel.id + "' \
									data-channel-title='" + results[i].channel.title + "' \
									type='submit' \
									class='btn btn-primary btn-sm'\
									>Add YouTuber</button>";
				}
				//html += 	"<button id='youtuber_batch_add_channel' type='submit' class='btn btn-success btn-sm'>Add YouTuber</button>";
				html += "</div>";
				$('#youtuber_batch_add_results_list').append(html);

				$('#youtuber_batch_add_channel_' + results[i].channel.id).unbind('click');
				$('#youtuber_batch_add_channel_' + results[i].channel.id).off('click');
				$('#youtuber_batch_add_channel_' + results[i].channel.id).click(function(){
					var channelId = $(this).attr('data-channel-id');
					var channelName = $(this).attr('data-channel-title');
					var notes = $('#youtuber_batch_add_modal_search_text').val(); // notes are search terms for now!
					API.addYoutuber(false, function(yter){
						//yter.youtubeId = channelId;
						//yter.name_override = channelName;
						yter.saveWith(channelId, channelName, notes);
					});
				})
			}
		}

		$('#youtuber_batch_add_modal_search_latest').click(function(){
			var str = $('#youtuber_batch_add_modal_search_text').val();
			API.searchYouTube(str, "date", resultsFunc, function(){});
		});
		$('#youtuber_batch_add_modal_search_relevance').click(function(){
			var str = $('#youtuber_batch_add_modal_search_text').val();
			API.searchYouTube(str, "relevance", resultsFunc, function(){});
		});
	}


Podcast = function(data) {
	DBO.call(this, data);
}
	Podcast.prototype = Object.create(DBO.prototype);
	Podcast.prototype.constructor = Podcast;
	Podcast.prototype.init = function(data) {
		DBO.prototype.init.call(this, data);
		this.id = parseInt(this.field('id'));
		this.name = this.field('name');


	}


TwitchChannel = function(data) {
	DBO.call(this, data);
}
	TwitchChannel.prototype = Object.create(DBO.prototype);
	TwitchChannel.prototype.constructor = TwitchChannel;
	TwitchChannel.prototype.init = function(data) {
		DBO.prototype.init.call(this, data);
		this.id = parseInt(this.field('id'));
		this.name = this.field('name');

		this.initPriorities('priorities');
	}

	TwitchChannel.prototype.createTableRow = function() {
		var html = "	<tr data-twitchchannel-id='" + this.field('id') + "' data-twitchchannel-tablerow='true' class='table-list' data-toggle='modal' data-target='.twitchchannel_modal'> \
							<!-- <td data-twitchchannel-id='" + this.field('id') + "' data-field='name' data-value='" + this.field('name') + "'>" + this.field('name') + "</td> -->\
\
							<!-- <td data-twitchchannel-id='" + this.field('id') + "' data-field='id' data-value='" + this.field('id') + "'>" + this.field('id') + "</td> -->\
							<td data-value='" + this.field('name') + "'> \
								<span data-twitchchannel-id='" + this.field('id') + "' data-field='name' >" + this.field('name') + "</span> \
								<div data-twitchchannel-id='" + this.field('id') + "' data-field='email-list'></div> \
							</td> \
\
\
							<td data-twitchchannel-id='" + this.field('id') + "' data-field='priority' data-value='" + this.priority() + "'>" + Priority.name(this.priority()) + "</td> \
							<td data-twitchchannel-id='" + this.field('id') + "' data-field='subscribers' data-value='" + this.field('subscribers') + "'>" + new Number(this.field('subscribers')).toLocaleString() + "</td> \
							<td data-twitchchannel-id='" + this.field('id') + "' data-field='views' data-value='" + this.field('views') + "'>" + new Number(this.field('views')).toLocaleString() + "</td> \
							<td data-twitchchannel-id='" + this.field('id') + "' data-field='twitter_followers' data-value='" + this.field('twitter_followers') + "'>" + this.twitterCell() + "</td> \
							<td data-twitchchannel-id='" + this.field('id') + "' data-field='lastpostedon' data-value='" + this.field('lastpostedon') + "'>" + impresslist.util.relativetime_contact(this.field('lastpostedon')) + "</td> \
							<td data-twitchchannel-id='" + this.field('id') + "' data-field='tags' data-value='" + this.field('tags') + "'>" + impresslist.util.buildTags(this.field('tags')) + "</td> \
						</tr>";
		$('#twitchchannels').append(html);

		var twitchchannel = this;
		$(this.openSelector()).click(function() {
			twitchchannel.open();
		});
	};
	TwitchChannel.prototype.openSelector = function() {
		return "#twitchchannels [data-twitchchannel-tablerow='true'][data-twitchchannel-id='" + this.id + "']";
	};

	TwitchChannel.prototype.removeTableRow = function() {
		$("#twitchchannels [data-twitchchannel-tablerow='true'][data-twitchchannel-id='" + this.id + "']").remove();
	}
	TwitchChannel.prototype.onAdded = function() {
		this.createTableRow();
	}
	TwitchChannel.prototype.onRemoved = function() {
		//$("[data-twitchchannel-id='" + this.id + "'][data-twitchchannel-tablerow='true']").remove();
		this.removeTableRow();
		this.close();
	}
	TwitchChannel.prototype.preventOpen = function() {
		$('#modals').html("");
	}
	TwitchChannel.prototype.open = function() {
		var html = "<div class='modal fade twitchchannel_modal' tabindex='-1' role='dialog'> \
						<div class='modal-dialog'> \
							<div class='modal-content' style='padding:5px;'> \
								<div style='min-height:100px;padding:20px;'>";

			html += " 				<h3 data-twitchchannel-id='" + this.id + "' data-field='name'>" + this.field('name') + "</h3> ";
			html += "				<form role='form' class='oa' onsubmit='return false;'>	\
										<div class='row'>\
											<div class='form-group col-md-8'>\
												<label for='url'>Channel Name:&nbsp; </label> \
												<input data-twitchchannel-id='" + this.id + "' data-input-field='twitchUsername' class='form-control' type='text' value='" + this.field('twitchUsername') + "' />\
											</div>\
											<div class='form-group col-md-4'>\
												<label for='url'>Priority:&nbsp; </label>"
													var priority = this.priority();
													html += "	<select data-twitchchannel-id='" + this.id + "' data-input-field='priority' class='form-control'>\
																	<option value='3' " + ((priority==3)?"selected='true'":"") + ">High</option>\
																	<option value='2' " + ((priority==2)?"selected='true'":"") + ">Medium</option>\
																	<option value='1' " + ((priority==1)?"selected='true'":"") + ">Low</option>\
																	<option value='0' " + ((priority==0)?"selected='true'":"") + ">N/A</option>\
																</select>";
			html += "						</div>\
										</div>\
										<div class='form-group'>\
											<p data-twitchchannel-id='" + this.id + "' data-field='twitchDescription'>" + this.field("twitchDescription") + "</p>\
										</div>\
										<div class='form-group'>\
											<label for='email'>Email:&nbsp; </label> \
											<input data-twitchchannel-id='" + this.id + "' data-input-field='email' class='form-control' type='text' value='" + this.field('email') + "' />\
										</div>\
										<div class='form-group'>\
											<label for='twitter'>Twitter Username:&nbsp; </label> \
											<input data-twitchchannel-id='" + this.id + "' data-input-field='twitter' class='form-control' type='text' value='" + this.field('twitter') + "' />\
										</div>\
										<div class='form-group'>\
											<label for='email'>Notes:&nbsp; </label> \
											<textarea data-twitchchannel-id='" + this.id + "' data-input-field='notes' class='form-control' style='height:100px;'>" + this.field('notes') + "</textarea>\
										</div>\
										<div class='form-group'>\
											<label for='tags'>Tags:</label><Br/> \
											<input id='twitchchannel-modal-tagsinput' data-twitchchannel-id='" + this.id + "' data-input-field='tags' class='form-control' type='text' value='" + impresslist.util.tagStringToInputField(this.field('tags')) + "' data-role=\"tagsinput\" />\
										</div>\
										<div class='fl'> \
											<button id='save_twitchchannelId" + this.id + "' type='submit' class='btn btn-primary'>Save</button>";
			html += "						&nbsp;<button id='close_twitchchannelId" + this.id + "' type='submit' class='btn btn-default'>Close</button>";
			html += " 					</div><div class='fr'> \
											<button id='delete_twitchchannelId" + this.id + "' type='submit' class='btn btn-danger'>Remove</button> \
										</div>\
									</form>";

		html += "				</div>\
							</div>\
						</div>\
					</div>";
		$('#modals').html(html);
		$('#twitchchannel-modal-tagsinput').tagsinput(impresslist.config.misc.tagSearchConfig);

		var twitchchannel = this;
		$("#save_twitchchannelId" + this.id).click(function() { twitchchannel.save(); });
		$("#close_twitchchannelId" + this.id).click(function() { twitchchannel.close(); });
		$("#delete_twitchchannelId" + this.id).click(function() { API.removeTwitchChannel(twitchchannel); });

		$("[data-twitchchannel-id='" + this.id + "'][data-input-field='priority']").change(function() {
			twitchchannel.savePriority();
		});
	}
	TwitchChannel.prototype.update = function() {
		$("[data-twitchchannel-id='" + this.id + "'][data-field='name']").html(this.name);
		$("[data-twitchchannel-id='" + this.id + "'][data-field='twitchUsername']").html(this.field('twitchUsername'));
		$("[data-twitchchannel-id='" + this.id + "'][data-field='priority']").html(Priority.name(this.priority()));
		$("[data-twitchchannel-id='" + this.id + "'][data-field='twitchDescription']").html(this.field('twitchDescription'));
		$("[data-twitchchannel-id='" + this.id + "'][data-field='views']").html( new Number(this.field('views')).toLocaleString() );
		$("[data-twitchchannel-id='" + this.id + "'][data-field='twitter']").html(this.field('twitter'));
		$("[data-twitchchannel-id='" + this.id + "'][data-field='twitter_followers']").html( this.twitterCell() );
		$("[data-twitchchannel-id='" + this.id + "'][data-field='lastpostedon']").html( impresslist.util.relativetime_contact(this.field('lastpostedon')) );
		$("[data-twitchchannel-id='" + this.id + "'][data-field='tags']").html( impresslist.util.buildTags(this.field('tags')) );
	};

	TwitchChannel.prototype.close = function() {
		$('.twitchchannel_modal').modal('hide');
	}
	TwitchChannel.prototype.filter = function(text) {
		var elementExtras = $("#twitchchannels [data-twitchchannel-extra-tablerow='true'][data-twitchchannel-id='" + this.id + "']");
		elementExtras.hide();

		var element = $("#twitchchannels [data-twitchchannel-tablerow='true'][data-twitchchannel-id='" + this.id + "']");
		if (this.search(text) && this.filter_isHighPriority() && this.filter_hasEmail()) { // && this.isContactedByMe() && this.isRecentlyContacted()) {
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
	TwitchChannel.prototype.filter_isHighPriority = function() {
		if ($('#filter-high-priority').is(':checked')) {
			return (this.priority() == 3);
		}
		return true;
	}
	TwitchChannel.prototype.filter_hasEmail = function() {
		if ($('#filter-email-attached').is(':checked')) {
			return (this.field('email').length > 0);
		}
		return true;
	}
	TwitchChannel.prototype.search = function(text) {
		var ret = true;

		if (text.length == 0) { return ret; }

		ret = this.name.toLowerCase().indexOf(text) != -1;
		if (ret) { return ret; }

		ret = this.field('tags').indexOf(text) != -1;
		if (ret) { return ret; }

		if ($('#search-option-full').is(':checked')) {
			ret = this.fields['notes'].toLowerCase().indexOf(text) != -1;
			if (ret) { return ret; }

			ret = this.fields['description'].toLowerCase().indexOf(text) != -1;
			if (ret) { return ret; }
		}

		ret = this.fields['twitter'].toLowerCase().indexOf(text) != -1;
		if (ret) { return ret; }

		return false;
	}
	TwitchChannel.prototype.save = function() {
		var channel = $("[data-twitchchannel-id=" + this.id + "][data-input-field='twitchUsername']").val();
		var email   = $("[data-twitchchannel-id=" + this.id + "][data-input-field='email']").val();
		var twitter = $("[data-twitchchannel-id=" + this.id + "][data-input-field='twitter']").val();
		var notes   = $("[data-twitchchannel-id=" + this.id + "][data-input-field='notes']").val();
		var tags = impresslist.util.tagInputFieldToString($('#twitchchannel-modal-tagsinput').val());

		API.saveTwitchChannel(this, channel, email, twitter, notes, tags);
	};
	TwitchChannel.prototype.saveWith = function(channel, nameOverride) {
		//API.saveTwitchChannel(this, channel, nameOverride, "", "", "");
	}
	TwitchChannel.prototype.savePriority = function() {
		var priority = $("[data-twitchchannel-id='" + this.id + "'][data-input-field='priority']").val();
		API.setTwitchChannelPriority(this, priority, impresslist.config.user.game);
	}
	TwitchChannel.prototype.refreshEmails = function() {
		// Extra (hidden) rows for each e-mail address the person has.
		var emails = []
		if (this.field('email').length > 0) {
			emails.push({
				type: "twitchchannel",
				typeId: this.field("id"),
				typeName: this.name + " (TwitchChannel)",
				twitchchannel: this.field('id'),
				name: this.name,
				email: this.field('email')
			});
		}

		var extraEmails = "";
		for(var i = 0; i < emails.length; i++) {
			extraEmails += "	<div data-type='twitchchannel' data-twitchchannel-id='" + this.field('id') + "' data-twitchchannel-extra-tablerow='true' style='padding:5px;'>";
			extraEmails += "		<input \
										data-type='twitchchannel' \
										data-twitchchannel-id='" + this.field('id') + "' \
										data-checkbox='true' \
										data-twitchchannel-checkbox='true' \
										data-mailout-name='" + emails[i]['name'] + " (TwitchChannel)' \
										data-mailout-type='" + emails[i]['type'] + "' \
										data-mailout-typeid='" + emails[i]['typeId'] + "' \
										data-mailout-typename='" + emails[i]['typeName'] + "' \
										data-mailout-email='" + emails[i]['email'] + "' \
										type='checkbox' \
										value='1'/>";
			extraEmails += "		&nbsp; " + emails[i]['typeName'] + " - " + emails[i]['email'];
			extraEmails += "	</div>";
		}

		$('div[data-twitchchannel-id="' + this.field('id') + '"][data-field="email-list"]').html(extraEmails);

		var twitchchannel = this;
		$("input[data-twitchchannel-id='" + this.field('id') + "'][data-checkbox='true']").click(function(e) {
			impresslist.refreshMailoutRecipients();
			twitchchannel.preventOpen();
			e.stopPropagation();
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
							<!-- <td data-youtuber-id='" + this.field('id') + "' data-field='name' data-value='" + this.field('name') + "'>" + this.field('name') + "</td> -->\
\
							<!-- <td data-youtuber-id='" + this.field('id') + "' data-field='id' data-value='" + this.field('id') + "'>" + this.field('id') + "</td> -->\
							<td data-value='" + this.field('name') + "'> \
								<span data-youtuber-id='" + this.field('id') + "' data-field='name' >" + this.field('name') + "</span> \
								<div data-youtuber-id='" + this.field('id') + "' data-field='email-list'></div> \
							</td> \
\
\
							<td data-youtuber-id='" + this.field('id') + "' data-field='priority' data-value='" + this.priority() + "'>" + Priority.name(this.priority()) + "</td> \
							<td data-youtuber-id='" + this.field('id') + "' data-field='subscribers' data-value='" + this.field('subscribers') + "'><a href='http://youtube.com/user/" + this.field('channel') + "' target='new'>" + new Number(this.field('subscribers')).toLocaleString() + "</a></td> \
							<td data-youtuber-id='" + this.field('id') + "' data-field='views' data-value='" + this.field('views') + "'>" + new Number(this.field('views')).toLocaleString() + "</td> \
							<td data-youtuber-id='" + this.field('id') + "' data-field='twitter_followers' data-value='" + this.field('twitter_followers') + "'>" + this.twitterCell() + "</td> \
							<td data-youtuber-id='" + this.field('id') + "' data-field='lastpostedon' data-value='" + this.field('lastpostedon') + "'>" + impresslist.util.relativetime_contact(this.field('lastpostedon')) + "</td> \
							<td data-youtuber-id='" + this.field('id') + "' data-field='tags' data-value='" + this.field('tags') + "'>" + impresslist.util.buildTags(this.field('tags')) + "</td> \
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
	Youtuber.prototype.preventOpen = function() {
		$('#modals').html("");
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
											<div class='form-group col-md-5'>\
												<label for='url'>Name (Override):&nbsp; </label> \
												<input data-youtuber-id='" + this.id + "' data-input-field='name' class='form-control' type='text' value='" + this.field('name_override') + "' />\
											</div>\
											<div class='form-group col-md-3'>\
												<label for='url'>Priority:&nbsp; </label>"
													var priority = this.priority();
													html += "	<select data-youtuber-id='" + this.id + "' data-input-field='priority' class='form-control'>\
																	<option value='3' " + ((priority==3)?"selected='true'":"") + ">High</option>\
																	<option value='2' " + ((priority==2)?"selected='true'":"") + ">Medium</option>\
																	<option value='1' " + ((priority==1)?"selected='true'":"") + ">Low</option>\
																	<option value='0' " + ((priority==0)?"selected='true'":"") + ">N/A</option>\
																</select>";
			html += "						</div>\
											<div class='form-group col-md-4'>\
												" + this.countrySelectHtml('youtuber') + "\
											</div>\
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
										<div class='form-group'>\
											<label for='tags'>Tags:</label><Br/> \
											<input id='youtuber-modal-tagsinput' data-youtuber-id='" + this.id + "' data-input-field='tags' class='form-control' type='text' value='" + impresslist.util.tagStringToInputField(this.field('tags')) + "' data-role=\"tagsinput\" />\
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
		$('#youtuber-modal-tagsinput').tagsinput(impresslist.config.misc.tagSearchConfig);

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
		$("[data-youtuber-id='" + this.id + "'][data-field='tags']").html( impresslist.util.buildTags(this.field('tags')) );
	};

	Youtuber.prototype.close = function() {
		$('.youtuber_modal').modal('hide');
	}
	Youtuber.prototype.filter = function(text) {
		var elementExtras = $("#youtubers [data-youtuber-extra-tablerow='true'][data-youtuber-id='" + this.id + "']");
		elementExtras.hide();

		var element = $("#youtubers [data-youtuber-tablerow='true'][data-youtuber-id='" + this.id + "']");
		if (this.search(text) && this.filter_isHighPriority() && this.filter_hasEmail()) { // && this.isContactedByMe() && this.isRecentlyContacted()) {
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

		ret = this.field('tags').indexOf(text) != -1;
		if (ret) { return ret; }

		if ($('#search-option-full').is(':checked')) {
			ret = this.fields['notes'].toLowerCase().indexOf(text) != -1;
			if (ret) { return ret; }

			ret = this.fields['description'].toLowerCase().indexOf(text) != -1;
			if (ret) { return ret; }
		}

		ret = this.fields['twitter'].toLowerCase().indexOf(text) != -1;
		if (ret) { return ret; }

		return false;
	}
	Youtuber.prototype.save = function() {
		var nameOverride = $("[data-youtuber-id=" + this.id + "][data-input-field='name']").val();
		var channel = $("[data-youtuber-id=" + this.id + "][data-input-field='channel']").val();
		var email   = $("[data-youtuber-id=" + this.id + "][data-input-field='email']").val();
		var twitter = $("[data-youtuber-id=" + this.id + "][data-input-field='twitter']").val();
		var notes   = $("[data-youtuber-id=" + this.id + "][data-input-field='notes']").val();
		var country = $("[data-youtuber-id='" + this.id + "'][data-input-field='country']").val();
		var tags = impresslist.util.tagInputFieldToString($('#youtuber-modal-tagsinput').val());

		API.saveYoutuber(this, channel, nameOverride, email, twitter, notes, country, tags);
	};
	Youtuber.prototype.saveWith = function(channel, nameOverride, notes) {
		API.saveYoutuber(this, channel, nameOverride, "", "", notes, "", "todo");
	}
	Youtuber.prototype.savePriority = function() {
		var priority = $("[data-youtuber-id='" + this.id + "'][data-input-field='priority']").val();
		API.setYoutuberPriority(this, priority, impresslist.config.user.game);
	}
	Youtuber.prototype.refreshEmails = function() {
		// Extra (hidden) rows for each e-mail address the person has.
		var emails = []
		if (this.field('email').length > 0) {
			emails.push({
				type: "youtuber",
				typeId: this.field("id"),
				typeName: this.name + " (Youtuber)",
				youtuber: this.field('id'),
				name: this.name,
				email: this.field('email')
			});
		}

		var extraEmails = "";
		for(var i = 0; i < emails.length; i++) {
			extraEmails += "	<div data-type='youtuber' data-youtuber-id='" + this.field('id') + "' data-youtuber-extra-tablerow='true' style='padding:5px;'>";
			extraEmails += "		<input \
										data-type='youtuber' \
										data-youtuber-id='" + this.field('id') + "' \
										data-checkbox='true' \
										data-youtuber-checkbox='true' \
										data-mailout-name='" + emails[i]['name'] + " (Youtuber)' \
										data-mailout-type='" + emails[i]['type'] + "' \
										data-mailout-typeid='" + emails[i]['typeId'] + "' \
										data-mailout-typename='" + emails[i]['typeName'] + "' \
										data-mailout-email='" + emails[i]['email'] + "' \
										type='checkbox' \
										value='1'/>";
			extraEmails += "		&nbsp; " + emails[i]['typeName'] + " - " + emails[i]['email'];
			extraEmails += "	</div>";
		}

		$('div[data-youtuber-id="' + this.field('id') + '"][data-field="email-list"]').html(extraEmails);

		var youtuber = this;
		$("input[data-youtuber-id='" + this.field('id') + "'][data-checkbox='true']").click(function(e) {
			impresslist.refreshMailoutRecipients();
			youtuber.preventOpen();
			e.stopPropagation();
		});
	};



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

		var html = "";
		if (pub) {
			html += "<div data-perpub-id='" + this.id + "' data-perpub-tablerow='true' class='panel panel-default'> \
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
		}
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


PersonTwitchChannel = function(data) {
	DBO.call(this, data);
}
	PersonTwitchChannel.prototype = Object.create(DBO.prototype);
	PersonTwitchChannel.prototype.constructor = PersonTwitchChannel;
	PersonTwitchChannel.prototype.init = function(data) {
		DBO.prototype.init.call(this, data);
		this.id = parseInt(this.fields['id']);
	}
	PersonTwitchChannel.prototype.open = function() {
		console.log('per tw open');
		var obj = this;
		var tw = impresslist.findTwitchChannelById(this.fields['twitchchannel']);
		var html = "<div data-pertw-id='" + this.id + "' data-pertw-tablerow='true' class='panel panel-default'> \
						<div class='panel-heading oa'> \
							<h3 class='panel-title fl'>" + tw.name + "&nbsp;</h3> \
						</div> \
						<div class='panel-body'> \
							<div class='row'> \
								<div class='fr padx'> \
									<button id='delete_personTwitchChannelId" + this.id + "' type='submit' class='btn btn-danger' data-pertw-id='" + this.id + "'>Remove</button> \
								</div> \
							</div> \
						</div> \
					</div>";
		$('#person-twitchchannels').append(html);

		$('#delete_personTwitchChannelId' + this.id).click(function() { API.removePersonTwitchChannel(obj); });

		$("[data-pertw-id='" + this.id + "'] a").click(function(e) {
			e.preventDefault();
			$("#person_tabs [data-tab='person_messages']").click();
		});
	}

	PersonTwitchChannel.prototype.update = function() {

	}
	PersonTwitchChannel.prototype.onAdded = function(fromInit) {
		if (!fromInit) {
			this.open();
		}
	};
	PersonTwitchChannel.prototype.onRemoved = function() {
		$("[data-pertw-id='" + this.id + "'][data-pertw-tablerow='true']").remove();
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
		if (data.recipients.substr(0, 1) != '[') {
			this.recipientsData = [];
		} else {
			this.recipientsData = JSON.parse(data.recipients);
		}

		for(var i = 0; i < this.recipientsData.length; i++) {
			if (this.recipientsData[i].read) {
				this.numOpens++;
			}
			this.numRecipients++;
		}
	}
	SimpleMailout.showAll = function() {
		$("[data-simplemailout-tablerow='true']").show();
	}
	SimpleMailout.prototype.createTableRow = function() {
		var html = "	<tr data-simplemailout-id='" + this.field('id') + "' data-simplemailout-tablerow='true' class='table-list' > \
							<td data-simplemailout-id='" + this.field('id') + "' data-field='name' data-value='" + this.field('name') + "'>" + this.field('name') + "</td> \
							<td data-simplemailout-id='" + this.field('id') + "' data-field='numRecipients' data-value='" + this.numRecipients + "'>" + this.numRecipients + "</td> \
							<td data-simplemailout-id='" + this.field('id') + "' data-field='numOpens' data-value='" + this.numOpens + "'>...</td>\
							<td data-simplemailout-id='" + this.field('id') + "' data-field='timestamp' data-value='" + this.field('timestamp') + "'>...</td>\
							<td data-simplemailout-id='" + this.field('id') + "' data-field='visible' data-value='" + this.field('visible') + "' style='display:none;'>" + this.field('visible') + "</td>";
		html += "		</tr>";
		$('#mailout-list-tbody').append(html);
		$('#mailout-list-footer').hide();

		if (!this.field('visible')) {
			$("[data-simplemailout-id='" + this.field('id') + "'][data-simplemailout-tablerow='true']").hide();
		}

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
			openText = Math.round((((1.0*this.numOpens)/(1.0*this.numRecipients))*100));
		}

		$("[data-simplemailout-id='" + this.id + "'][data-field='name']").html( this.field('name') );
		$("[data-simplemailout-id='" + this.id + "'][data-field='numRecipients']").html( recipientsText );
		$("[data-simplemailout-id='" + this.id + "'][data-field='numOpens']").html( openText + "%" );
		$("[data-simplemailout-id='" + this.id + "'][data-field='numOpens']").attr('data-value', openText );
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
	SimpleMailout.prototype.duplicate = function() {
		var th = this;
		API.duplicateSimpleMailout(this, function(){
			th.close();
		});
	}
	SimpleMailout.prototype.save = function(callback) {
		if (typeof callback == 'undefined') {
			callback = function(data) {
				console.log('saved');
				if (data.checks.length > 0) {
					API.infoMessage(data.checks.join('<br/>\n'));;
				}
			};
		}
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

		var publicationsSelected = $('input[data-type="publication"][data-checkbox="true"]:checked');
		for(var i = 0; i < publicationsSelected.length; i++) {
			var obj = {	};
			obj.type = $(publicationsSelected[i]).attr('data-mailout-type');
			obj.sent = false;
			obj.read = false;
			if (obj.type == 'publication') {
				obj.publication_id = $(publicationsSelected[i]).attr('data-mailout-typeid');
			}
			recipients.push(obj);
		}
		var youTubersSelected = $('input[data-type="youtuber"][data-checkbox="true"]:checked');
		for(var i = 0; i < youTubersSelected.length; i++) {
			var obj = {	};
			obj.type = $(youTubersSelected[i]).attr('data-mailout-type');
			obj.sent = false;
			obj.read = false;
			if (obj.type == 'youtuber') {
				obj.youtuber_id = $(youTubersSelected[i]).attr('data-mailout-typeid');
			}
			recipients.push(obj);
		}
		var twitchChannelsSelected = $('input[data-type="twitchchannel"][data-checkbox="true"]:checked');
		for(var i = 0; i < twitchChannelsSelected.length; i++) {
			var obj = {	};
			obj.type = $(twitchChannelsSelected[i]).attr('data-mailout-type');
			obj.sent = false;
			obj.read = false;
			if (obj.type == 'twitchchannel') {
				obj.twitchchannel_id = $(twitchChannelsSelected[i]).attr('data-mailout-typeid');
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
		$('#mailout-writepage-duplicate').show();
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
		$('#mailout-writepage-duplicate').unbind("click");
		$('#mailout-writepage-duplicate').click(function() {
			mailout.duplicate();
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
			} else if (this.recipientsData[i].type == "publication") {
				var p = impresslist.findPublicationById(this.recipientsData[i].publication_id);
				if (p != null) {
					var box = $('input[data-type="publication"][data-mailout-typeid="' + p.id + '"][data-checkbox="true"][data-mailout-type="publication"]');
					$(box).prop("checked", true);
				}
			} else if (this.recipientsData[i].type == "youtuber") {
				var p = impresslist.findYoutuberById(this.recipientsData[i].youtuber_id);
				if (p != null) {
					var box = $('input[data-type="youtuber"][data-mailout-typeid="' + p.id + '"][data-checkbox="true"][data-mailout-type="youtuber"]');
					$(box).prop("checked", true);
				}
			} else if (this.recipientsData[i].type == "twitchchannel") {
				var p = impresslist.findTwitchChannelById(this.recipientsData[i].twitchchannel_id);
				if (p != null) {
					var box = $('input[data-type="twitchchannel"][data-mailout-typeid="' + p.id + '"][data-checkbox="true"][data-mailout-type="twitchchannel"]');
					$(box).prop("checked", true);
				}
			}
			$(box).attr("mailout-read", this.recipientsData[i].read);
			$(box).attr("mailout-reminded-twitter-dm", (this.recipientsData[i].remind && this.recipientsData[i].remind.twitterdm)?true:false);
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
		this.rowSelector = "#people [data-person-tablerow='true'][data-person-id='" + this.id + "']";
		//this.publications = [];

		this.initPriorities('priorities');

	}
	Person.prototype.update = function() {
		$("[data-person-id='" + this.id + "'][data-field='name']").html(this.fullname());
		$("[data-person-id='" + this.id + "'][data-field='priority']").html(Priority.name(this.priority()));
		$("[data-person-id='" + this.id + "'][data-field='twitter_followers']").html( this.twitterCell() );
		$("[data-person-id='" + this.id + "'][data-field='tags']").html( impresslist.util.buildTags(this.field('tags')) );
	},
	Person.prototype.save = function() {
		var firstname = $("[data-person-id=" + this.id + "][data-input-field='firstname']").val();
		var surnames = $("[data-person-id=" + this.id + "][data-input-field='surnames']").val();
		var email = $("[data-person-id=" + this.id + "][data-input-field='email']").val();
		var twitter = $("[data-person-id=" + this.id + "][data-input-field='twitter']").val();
		var notes = $("[data-person-id=" + this.id + "][data-input-field='notes']").val();
		var country = $("[data-person-id='" + this.id + "'][data-input-field='country']").val();
		var language = $("[data-person-id='" + this.id + "'][data-input-field='lang']").val();
		var tags = impresslist.util.tagInputFieldToString($('#person-modal-tagsinput').val());
		var outofdate = $("[data-person-id=" + this.id + "][data-input-field='outofdate']").is(':checked');

		API.savePerson(this, firstname, surnames, email, twitter, notes, country, language, tags, outofdate);
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
									if (impresslist.personPublications[i].field('person') == this.id) {
										var pub = impresslist.findPublicationById(impresslist.personPublications[i].field('publication'));
										if (pub) {
											emails.push( {"type": pub.field('name'), "email": impresslist.personPublications[i].field('email')} );
										}
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
							<li role="presentation"><a role="tab" href="#" data-tab="person_youtubeChannels" data-toggle="tab">Youtubes</a></li> \
							<li role="presentation"><a role="tab" href="#" data-tab="person_twitchChannels" data-toggle="tab">Twitchs</a></li> \
							<li role="presentation"><a role="tab" href="#" data-tab="person_messages" data-toggle="tab">Messages</a></li> \
							<li role="presentation"><a role="tab" href="#" data-tab="person_keys" data-toggle="tab">Keys</a></li> \
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
									<div class='row'>\
										<div class='form-group col-md-3'>\
											<label for='language'>Language:</label>";
											var language = this.field('lang');
											html += "	<select data-person-id='" + this.id + "' data-input-field='lang' class='form-control'>\
															<option value='en' " + ((language=='en')?"selected='true'":"") + ">English</option>\
															<option value='fr' " + ((language=='fr')?"selected='true'":"") + ">French</option>\
															<option value='it' " + ((language=='it')?"selected='true'":"") + ">Italian</option>\
															<option value='de' " + ((language=='de')?"selected='true'":"") + ">German</option>\
															<option value='es' " + ((language=='es')?"selected='true'":"") + ">Spanish</option>\
															<option value='pt' " + ((language=='pt')?"selected='true'":"") + ">Portuguese</option>\
														</select>";
				html += "				</div>\
										<div class='form-group col-md-3'>\
											" + this.countrySelectHtml('person') + "\
										</div>\
										<div class='form-group col-md-6'>\
											<label for='tags'>Tags:</label><Br/> \
											<input id='person-modal-tagsinput' data-person-id='" + this.id + "' data-input-field='tags' class='form-control' type='text' value='" + impresslist.util.tagStringToInputField(this.field('tags')) + "' data-role=\"tagsinput\" />\
										</div>\
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

				// Twitch Channels panel
				html += "	<div role='tabpanel' class='tab-pane pady' data-tab='person_twitchChannels'> \
								<div class='form-group'>\
									<label for='add-twitchchannel'>Add:&nbsp;</label> \
									<input id='add-twitchchannel-search' type='text' class='form-control' placeholder='Search' /> \
								</div>\
								<div id='add-twitchchannel-results-container' style='display:none;'>\
									<table class='table table-striped'>\
										<thead>\
											<th>Name</th> \
											<th>Subscribers</th> \
										</thead> \
										<tbody id='add-twitchchannel-results'> \
										</tbody> \
									</table>\
								</div> \
								<div id='person-twitchchannels'>";

				html += "		</div>\
							</div>";

				// Keys panel
				html += "	<div role='tabpanel' class='tab-pane pady' data-tab='person_keys'> \
								<div class='form-group'>\
									<label for='add-keys'>Add:&nbsp;</label> \
									<input id='add-keys-search' type='text' class='form-control' placeholder='Search' /> \
								</div>\
								<div id='add-keys-results-container' style='display:none;'>\
									<table class='table table-striped'>\
										<thead>\
											<th>Key</th> \
											<th>Platform</th> \
											<th>Subplatform</th> \
										</thead> \
										<tbody id='add-keys-results'> \
										</tbody> \
									</table>\
								</div> \
								<div id='person-keys'> \
									<div class='pady'><b>All Keys</b></div> \
									<table class='table table-striped sortable'> \
										<thead> \
											<th>Game</th> \
											<th>Key</th> \
											<th>Platform</th> \
											<th>Subplatform</th> \
											<th>Assigned On</th> \
											<th>&nbsp;</th> \
										</thead> \
										<tbody id='person-keys-list'>\
										</tbody>\
									</table> \
								</div>\
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
									<div class='pady'><b><a class='person-view-all-messages'>All Messages</a> </b> >&nbsp;<span data-view-email-field='subject'></span></div> \
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
									<div class='oa'> \
										<button class='person-view-all-messages btn btn-sm btn-default fl'>Return to Messages</button> \
										<button class='person-remove-message btn btn-sm btn-danger fr' style='display:none'>Remove</button> \
									</div>\
								</div> \
							</div>";



		html += "		</div>";
		html += "	</div>";


		html += "			</div>\
						</div>\
					</div>";
		$('#modals').html(html);

		$('#person-modal-tagsinput').tagsinput(impresslist.config.misc.tagSearchConfig);


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

		// Find/add keys
		$("#person_tabs [data-tab='person_keys']").click(function(){

			API.assignedKeys("person", person.id, function(d) {
				var html = "";
				for(var i = 0; i < d.keys.length; i++) {

					var obj = d.keys[i];
						// <div data-key-id='" + obj.id + "' data-key-tablerow='true' class='panel panel-default'> \
						// 			<div class='row oa'> \
						// 				<b>" + obj.keystring + "</b>\
						// 				<div class='fl padx'> \
						// 					<label for='submit'>&nbsp;</label><br/> \
						//
						// 				</div> \
						// 			</div> \
						// 		</div>";


					var dateformat = new Date(obj.assignedByUserTimestamp * 1000);
					var dateformatStr = dateformat.toUTCString(); // toISOString

					html += "	<tr data-key-id='" + obj.id + "'> \
									<td>" + impresslist.findGameById(obj.game).name + "</td> \
									<td>" + obj.keystring + "</td> \
									<td>" + obj.platform + "</td> \
									<td>" + obj.subplatform + "</td> \
									<td>" + dateformatStr + "</td> \
									<td>\
										<button id='unassign_key_" + this.id + "' type='submit' class='btn btn-danger' data-key-id='" + obj.id + "'>X</button> \
									</td> \
								</tr>";
				}
				if (d.keys.length == 0) {
					html += "	<tr> \
									<td colspan='6'>No Keys</td> \
								</tr>";
				}
				$('#person-keys-list').html(html);
			}, function(e) {

			});
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

		// Add Twitch Channel binds
		$("#add-twitchchannel-search").keyup(function() {
			var searchfield = $(this);
			var text = $(this).val().toLowerCase();
			if (text.length == 0) {
				$("#add-twitchchannel-results").html("");
				$('#add-twitchchannel-results-container').hide();
				return;
			}
			var html = "";
			for(var i = 0; i < impresslist.twitchchannels.length; i++) {
				var include = impresslist.twitchchannels[i].search(text);
				if (include) {
					html += "	<tr class='table-list' data-twitchchannel-id='" + impresslist.twitchchannels[i].id + "' data-add-twitchchannel-result='true' >\
									<td>" + impresslist.twitchchannels[i].name + "</td> \
									<td>" + impresslist.twitchchannels[i].field('subscribers') + "</td> \
								</tr>";
				}
			}
			if (html.length == 0) {
				html += "	<tr>\
								<td colspan='2'>No Results</td> \
							</tr>";
			}
			$("#add-twitchchannel-results").html(html);
			$('#add-twitchchannel-results-container').show();

			$("[data-add-twitchchannel-result='true']").click(function() {
				var ytId = $(this).attr("data-twitchchannel-id");
				API.addPersonTwitchChannel(person, ytId);

				searchfield.val("");
				$("#add-twitchchannel-results").html("");
				$('#add-twitchchannel-results-container').hide();
			});

		});

		// Init youtube channels for this person.
		for(var i = 0; i < impresslist.personYoutubeChannels.length; ++i) {
			var peryt = impresslist.personYoutubeChannels[i];
			if (peryt.field('person') == this.id) {
				peryt.open();
			}
		}

		for(var i = 0; i < impresslist.personTwitchChannels.length; ++i) {
			var pertw = impresslist.personTwitchChannels[i];
			if (pertw.field('person') == this.id) {
				pertw.open();
			}
		}

		// Viewing Emails
		var returnToMessages = function() {
			$('#person-view-email').hide();
			$('#all-messages').show();
		};
		$(".person-view-all-messages").click(returnToMessages);
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


			if (impresslist.config.user.admin) { $('.person-remove-message').show(); }
			$('.person-remove-message').unbind('click');
			$('.person-remove-message').click(function() {

				API.request('/email/remove/', { id: emailId }, function( data ) {
					API.successMessage("Email successfully removed.");
					$('#all-messages tr[data-open-field=email][data-email-id=' + emailId + ']').remove();
					returnToMessages();
				}, function(){});

			});

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
							<td data-person-id='" + this.field('id') + "' data-field='last_contacted' data-value='" + this.field('lastcontacted') + "'>" + impresslist.util.relativetime_contact(this.field('lastcontacted')) + " " + lastcontactedbystring + "</td>\
							<td data-person-id='" + this.field('id') + "' data-field='tags' data-value='" + this.field('tags') + "'>" + impresslist.util.buildTags(this.field('tags')) + "</td>";
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

					var publication = impresslist.findPublicationById( impresslist.personPublications[i].field('publication'));
					if (!publication) { continue; }
					emails.push({
						type: "personPublication",
						typeId: impresslist.personPublications[i].field("id"),
						typeName: publication.field("name"),
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

	Person.prototype.filterTags = function(tagsArray) {
		return DBO.prototype.filterTags.call(this, tagsArray);
	}
	Person.prototype.filter = function(text) {
		var elementExtras = $("#people [data-person-extra-tablerow='true'][data-person-id='" + this.id + "']");
		elementExtras.hide();

		if (this.search(text) && this.filter_isContactedByMe() && this.filter_isRecentlyContacted() && this.filter_isHighPriority() && this.filter_hasEmail() && this.filter_isAssignedToMe() && this.filter_isOutOfDate()) {
			this.show();
			if (impresslist.selectModeIsOpen) {
				elementExtras.show();
			}
			return true;
		} else {
			this.hide();
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

		ret = this.field('tags').indexOf(text) != -1;
		if (ret) { return ret; }

		if ($('#search-option-full').is(':checked')) {
			ret = this.fields['notes'].toLowerCase().indexOf(text) != -1;
			if (ret) { return ret; }
		}

		ret = this.fields['email'].toLowerCase().indexOf(text) != -1;
		if (ret) { return ret; }

		// Search all publications too.
		var len = impresslist.personPublications.length;
		for(var i = 0; i < len; ++i) {
			if (impresslist.personPublications[i].fields['person'] == this.id) {
				var pub = impresslist.findPublicationById( impresslist.personPublications[i].fields['publication'] );
				if (!pub) { return false; }
				ret = pub.search(text);
				if (ret) { return ret; }
			}
		}

		// Search all youtube channels too.
		len = impresslist.personYoutubeChannels.length;
		for(var i = 0; i < len; ++i) {
			if (impresslist.personYoutubeChannels[i].fields['person'] == this.id) {
				var pub = impresslist.findYoutuberById( impresslist.personYoutubeChannels[i].fields['youtuber'] );
				if (!pub) { return false; }
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
	Publication.prototype.preventOpen = function() {
		$('#modals').html("");
	}
	Publication.prototype.open = function() {

		var html = "<div class='modal fade publication_modal' tabindex='-1' role='dialog'> \
						<div class='modal-dialog'> \
							<div class='modal-content' style='padding:5px;'> \
								<div style='min-height:100px;padding:20px;'>";

			html += " 				<h3 data-publication-id='" + this.id + "' data-field='name'>" + this.field('name') + "</h3> ";
			html += "				<form role='form' class='oa' onsubmit='return false;'>	\
										<div class='row'>\
											<div class='form-group  col-md-6'>\
												<label for='name'>Name:&nbsp; </label> \
												<input data-publication-id='" + this.id + "' data-input-field='name' class='form-control' type='text' value='" + this.field('name') + "' /> \
											</div>\
											<div class='form-group col-md-3'>\
												<label for='email'>Priority:</label>";
												var priority = this.priority();
												html += "	<select data-publication-id='" + this.id + "' data-input-field='priority' class='form-control'>\
																<option value='3' " + ((priority==3)?"selected='true'":"") + ">High</option>\
																<option value='2' " + ((priority==2)?"selected='true'":"") + ">Medium</option>\
																<option value='1' " + ((priority==1)?"selected='true'":"") + ">Low</option>\
																<option value='0' " + ((priority==0)?"selected='true'":"") + ">N/A</option>\
															</select>";
			html += "						</div>\
											<div class='form-group col-md-3'>\
												" + this.countrySelectHtml('publication') + "\
											</div>\
										</div>\
										<div class='form-group'>\
											<label for='url'>URL:&nbsp; </label> \
											<input data-publication-id='" + this.id + "' data-input-field='url' class='form-control' type='text' value='" + this.field('url') + "' />\
										</div>\
										<div class='form-group'>\
											<label for='url'>General/Tips Email:&nbsp; </label> \
											<input data-publication-id='" + this.id + "' data-input-field='email' class='form-control' type='text' value='" + this.field('email') + "' />\
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
											<textarea data-publication-id='" + this.id + "' data-input-field='notes' class='form-control' style='height:50px;'>" + this.field('notes') + "</textarea>\
										</div>\
										<div class='form-group'>\
											<label for='tags'>Tags:</label><Br/> \
											<input id='publication-modal-tagsinput' data-publication-id='" + this.id + "' data-input-field='tags' class='form-control' type='text' value='" + impresslist.util.tagStringToInputField(this.field('tags')) + "' data-role=\"tagsinput\" />\
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
		$('#publication-modal-tagsinput').tagsinput(impresslist.config.misc.tagSearchConfig);

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
		var email = $("[data-publication-id=" + this.id + "][data-input-field='email']").val();
		var rssfeedurl = $("[data-publication-id=" + this.id + "][data-input-field='rssfeedurl']").val();
		var twitter = $("[data-publication-id=" + this.id + "][data-input-field='twitter']").val();
		var notes = $("[data-publication-id=" + this.id + "][data-input-field='notes']").val();
		var country = $("[data-publication-id=" + this.id + "][data-input-field='country']").val();
		var tags = impresslist.util.tagInputFieldToString($('#publication-modal-tagsinput').val());

		API.savePublication(this, name, url, email, rssfeedurl, twitter, notes, country, tags);
	}
	Publication.prototype.savePriority = function() {
		var priority = $("[data-publication-id='" + this.id + "'][data-input-field='priority']").val();
		API.setPublicationPriority(this, priority, impresslist.config.user.game);
	}

	Publication.prototype.update = function() {
		$("[data-publication-id='" + this.id + "'][data-field='name']").html(this.name);
		$("[data-publication-id='" + this.id + "'][data-field='priority']").html(Priority.name(this.priority()));
		$("[data-publication-id='" + this.id + "'][data-field='url']").html(this.field('url'));
		$("[data-publication-id='" + this.id + "'][data-field='email']").html(this.field('email'));
		$("[data-publication-id='" + this.id + "'][data-field='twitter']").html(this.field('twitter'));
		$("[data-publication-id='" + this.id + "'][data-field='twitter_followers']").html( this.twitterCell() );
		$("[data-publication-id='" + this.id + "'][data-field='tags']").html( impresslist.util.buildTags(this.field('tags')) );
	}

	Publication.prototype.close = function() {
		//$('#modals').html("");
		$('.publication_modal').modal('hide');
	},
	Publication.prototype.createTableRow = function() {
		var html = "	<tr data-publication-tablerow='true' data-publication-id='" + this.id + "' class='table-list' data-toggle='modal' data-target='.publication_modal'> \
							<!-- <td data-publication-id='" + this.field('id') + "' data-field='id' 				data-value='" + this.field('id')+ "' >" + this.field('id') + "</td> --> \
							<!-- <td data-publication-id='" + this.field('id') + "' data-field='name' 				data-value='" + this.field('name')+ "' >" + this.icon() + this.field('name') + "</td> -->\
\
							<td data-value='" + this.field('name') + "'> \
								<span data-publication-id='" + this.field('id') + "' data-field='name' data-value='" + this.field('name')+ "'>" + this.icon() + this.field('name') + "</span> \
								<div data-publication-id='" + this.field('id') + "' data-field='email-list'></div> \
							</td> \
\
							<td data-publication-id='" + this.field('id') + "' data-field='priority' 			data-value='" + this.priority() + "'>" + Priority.name(this.priority()) + "</td> \
							<td data-publication-id='" + this.field('id') + "' data-field='url' 				data-value='" + this.field('url')+ "' style='max-width:250px;'><a href='" + this.field('url') + "' target='new'>" + this.field('url') + "</a></td> \
							<td data-publication-id='" + this.field('id') + "' data-field='twitter_followers' 	data-value='" + this.field('twitter_followers')+ "'>" + this.twitterCell() + "</td> \
							<td data-publication-id='" + this.field('id') + "' data-field='lastpostedon' 		data-value='" + this.field('lastpostedon') + "'>" + impresslist.util.relativetime_contact(this.field('lastpostedon')) + "</td> \
							<td data-publication-id='" + this.field('id') + "' data-field='tags' 				data-value='" + this.field('tags') + "' style='max-width:200px;'>" + impresslist.util.buildTags(this.field('tags')) + "</td> \
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

		var elementExtras = $("#publications [data-publication-extra-tablerow='true'][data-publication-id='" + this.id + "']");
		elementExtras.hide();

		var element = $("#publications [data-publication-tablerow='true'][data-publication-id='" + this.id + "']");
		if (this.search(text) && this.filter_isHighPriority()) { // && this.isContactedByMe() && this.isRecentlyContacted()) {
			if (impresslist.selectModeIsOpen) {
				elementExtras.show();
			}
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

		ret = this.field('tags').indexOf(text) != -1;
		if (ret) { return ret; }

		if ($('#search-option-full').is(':checked')) {
			ret = this.fields['notes'].toLowerCase().indexOf(text) != -1;
			if (ret) { return ret; }
		}

		ret = this.fields['url'].toLowerCase().indexOf(text) != -1;
		if (ret) { return ret; }

		ret = this.fields['email'].toLowerCase().indexOf(text) != -1;
		if (ret) { return ret; }

		ret = this.fields['twitter'].toLowerCase().indexOf(text) != -1;
		if (ret) { return ret; }

		return false;
	}
	Publication.prototype.refreshEmails = function() {
		// Extra (hidden) rows for each e-mail address the person has.
		var emails = []
		if (this.field('email').length > 0) {
			emails.push({
				type: "publication",
				typeId: this.field("id"),
				typeName: this.name + " (Publication)",
				publication: this.field('id'),
				name: this.name,
				email: this.field('email')
			});
		}

		var extraEmails = "";
		for(var i = 0; i < emails.length; i++) {
			extraEmails += "	<div data-type='publication' data-publication-id='" + this.field('id') + "' data-publication-extra-tablerow='true' style='padding:5px;'>";
			extraEmails += "		<input \
										data-type='publication' \
										data-publication-id='" + this.field('id') + "' \
										data-checkbox='true' \
										data-publication-checkbox='true' \
										data-mailout-name='" + emails[i]['name'] + " (Publication)' \
										data-mailout-type='" + emails[i]['type'] + "' \
										data-mailout-typeid='" + emails[i]['typeId'] + "' \
										data-mailout-typename='" + emails[i]['typeName'] + "' \
										data-mailout-email='" + emails[i]['email'] + "' \
										type='checkbox' \
										value='1'/>";
			extraEmails += "		&nbsp; " + emails[i]['typeName'] + " - " + emails[i]['email'];
			extraEmails += "	</div>";
		}

		$('div[data-publication-id="' + this.field('id') + '"][data-field="email-list"]').html(extraEmails);

		var publication = this;
		$("input[data-publication-id='" + this.field('id') + "'][data-checkbox='true']").click(function(e) {
			impresslist.refreshMailoutRecipients();
			publication.preventOpen();
			e.stopPropagation();
		});
	};

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
		var followers = 0;
		var following = 0;
		try { followers = JSON.parse(this.field('twitter_followers')).length; } catch(e) {}
		try { following = JSON.parse(this.field('twitter_friends')).length; } catch(e) {}

		//console.log(followers);
		//console.log(following);
		var html = "";
		html += "<div class='social-account-list-item' id='social-twitteracc-" + this.id + "'>";
		html += "	<div class='fl'><img src='" + this.field("twitter_image") + "' style='width:60px;'/></div>";
		html += "	<div class='fl' style='padding-left:10px;'>"
					 + this.field("twitter_name") + "<br/> "
					 + "<a href='http://twitter.com/" + this.field("twitter_handle") + "' target='new'>@" + this.field("twitter_handle") + "</a><br/>"
					 + "Followers: <b>" + followers + "</b> Following: <b>" + following + "</b>"
					+ "</div>";

		html += "	<button id='social-remove-twitteracc-" + this.id + "' class='btn btn-danger fr'>X</button>";
		//html += "	<button id='' data-years='1' class='btn btn-sm btn-info fr'>Inactive Followings (1Y)</button>";
		//html += "	<button id='' class='btn btn-sm btn-info fr'>Inactive Followings (0.5Y)</button>";
		//html += "	<button id='' class='btn btn-sm btn-info fr'>Unrequited Followings</button> ";

		html += "	<div class='fr dropdown' style='margin-right:8px;'> \
		  				<button class='btn btn-primary dropdown-toggle' type='button' data-toggle='dropdown'>View Tools <span class='caret'></span></button> \
					  	<ul class='dropdown-menu dropdown-menu-right'> \
					    	<li><a href='javascript:;' id='social-twitteracc-" + this.id + "-unrequited-followings' >Unrequited Followings</a></li> \
					    	<li><a href='javascript:;' id='social-twitteracc-" + this.id + "-inactive-followings-1' data-years='1'>Inactive Followings (1 year)</a></li> \
					    	<li><a href='javascript:;' id='social-twitteracc-" + this.id + "-inactive-followings-half' data-years='0.5'>Inactive Followings (6 mths)</a></li> \
					    	<li><a href='javascript:;' id='social-twitteracc-" + this.id + "-inactive-followings-qt' data-years='0.25'>Inactive Followings (3 mths)</a></li> \
					  	</ul> \
					</div>";

		html += "</div>";
		$('#social-homepage-twitteracc-list').append(html);

		var th = this;
		$("#social-twitteracc-" + this.id + "-inactive-followings-1").click(function(){
			th.showInactiveFollowings($(this).attr("data-years"));
		});
		$("#social-twitteracc-" + this.id + "-inactive-followings-half").click(function(){
			th.showInactiveFollowings($(this).attr("data-years"));
		});
		$("#social-twitteracc-" + this.id + "-inactive-followings-qt").click(function(){
			th.showInactiveFollowings($(this).attr("data-years"));
		});

		$("#social-twitteracc-" + this.id + "-unrequited-followings").click(function(){
			th.showUnrequitedFollowings();
		});


		this.update();
	}

	OAuthTwitterAccount.prototype.accountListModal = function(title) {
		var height = Math.round(window.innerHeight * 0.8);
		var html = "";
		html += "	<div id='twitterAccountsList_modal' class='modal fade' tabindex='-1' role='dialog'> \
						<div class='modal-dialog'> \
							<div class='modal-content'> \
								<div style='min-height:" + height + "px;padding:20px;'>";
		html += "					<h3>" + title + "</h3>";
		html += "					<h4>" + this.field('twitter_name') + " (@" + this.field('twitter_handle') + ")</h4>";
		html += "					<div id='twitterAccountsList_data'></div>";
		html += "				</div> \
							</div> \
						</div> \
					</div>";
		$('#modals').html(html);

		$('#twitterAccountsList_modal').modal('show');
	}
	OAuthTwitterAccount.prototype.accountListModalSetTotal = function(count) {
		var html = "<b>Total: " + count + "</b><br/><br/>";
		$('#twitterAccountsList_data').append(html);
	}
	OAuthTwitterAccount.prototype.accountListModalAddAccount = function(row) {
		var html = "<div id='twitter-acc-row-" + row.twitter_id + "' class='oa' style='padding:4px;'> \
						<div class='fl'><img src='" + row.twitter_image + "' style='display:inline-block;width:75px;padding-right:6px;'/></div> \
						<div class='fl' style='width:385px;'> \
							<b>" + row.twitter_name + "</b> (<a href='http://twitter.com/" + row.twitter_handle + "' target='new'>@" + row.twitter_handle + "</a>)<br/> \
							\"<i>" + row.twitter_bio.substr(0,127) + "...</i>\"<br/> \
							" + impresslist.util.relativetime_contact(row.twitter_lastpostedon) + "\
						</div>\
						<button id='unfollow-twitter-account' data-from-id='" + this.id + "' data-from-handle='" + this.field('twitter_handle') + "' data-handle='" + row.twitter_handle + "' class='btn btn-sm btn-danger fr'>Unfollow</button>\
					</div>";
		$('#twitterAccountsList_data').append(html);

		$('#unfollow-twitter-account[data-from-handle=' + this.field('twitter_handle') + '][data-handle=' + row.twitter_handle + ']').click(function(e) {
			console.log('Unfollow', $(this).attr('data-from-handle'), $(this).attr('data-handle'));
			$(this).prop("disabled",true);

			API.doOAuthTwitterAccountUnfollow(
				$(this).attr('data-from-id'),
				$(this).attr('data-handle'),
				function(data) {
					$('#twitter-acc-row-' + row.twitter_id).fadeOut();
				},
				function(){
					$(this).prop("disabled",false);
				}
			);
		})
	}
	OAuthTwitterAccount.prototype.showInactiveFollowings = function(years) {
		var th = this;
		this.accountListModal("Inactive Followings (" + years + " years)");
		API.getOAuthTwitterAccountInactiveFollowings(this.field("twitter_handle"), years, function(data) {
			th.accountListModalSetTotal(data.accounts.length);
			for(var i = 0; i < data.accounts.length; i++) {
				th.accountListModalAddAccount(data.accounts[i]);
			}
		}, null);
	}
	OAuthTwitterAccount.prototype.showUnrequitedFollowings = function() {
		var th = this;
		this.accountListModal("Unrequited Followings");
		API.getOAuthTwitterAccountUnrequitedFollowings(this.field("twitter_handle"), function(data) {
			th.accountListModalSetTotal(data.accounts.length);
			for(var i = 0; i < data.accounts.length; i++) {
				th.accountListModalAddAccount(data.accounts[i]);
			}
		}, null);
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

OAuthFacebookAccount = function(data) {
	DBO.call(this, data);
}
	OAuthFacebookAccount.prototype = Object.create(DBO.prototype)
	OAuthFacebookAccount.prototype.constructor = OAuthFacebookAccount;
	OAuthFacebookAccount.prototype.init = function(data) {
		DBO.prototype.init.call(this, data);
		this.id = parseInt(this.field('id'));
	}
	OAuthFacebookAccount.prototype.onAdded = function(fromInit) {
		this.createItem(fromInit);
		$('#social-homepage-facebookacc-list-none').hide();
		if (!fromInit) {
			$('#social-addfacebookaccount-cancel').click();
		}
	}
	OAuthFacebookAccount.prototype.onRemoved = function() {
		this.removeItem();

		if (impresslist.oauthFacebookAccounts.length - 1 == 0) {
			$('#social-homepage-facebookacc-list-none').show();
		}
	}
	OAuthFacebookAccount.prototype.createItem = function(fromInit) {
		var html = "";
		html += "<div class='social-account-list-item' id='social-facebookacc-" + this.id + "'>";
		html += "	<img src='" + this.field("facebook_image") + "' /> <a href='http://facebook.com/" + this.field("facebook_id") + "' target='new'>" + this.field("facebook_name") + "</a>";
		html += "	<button id='social-remove-facebookacc-" + this.id + "' class='btn btn-sm btn-danger fr'>X</button>";
		html += "</div>";
		$('#social-homepage-facebookacc-list').append(html);
		this.update();
	}
	OAuthFacebookAccount.prototype.removeItem = function() {
		$("#social-facebookacc-" + this.id).remove();
	}
	OAuthFacebookAccount.prototype.update = function() {
		var th = this;
		$("#social-remove-facebookacc-" + this.id).click(function(){
			API.removeOAuthFacebookAccount(th);
		});
	}

OAuthFacebookPage = function(data) {
	DBO.call(this, data);
}
	OAuthFacebookPage.prototype = Object.create(DBO.prototype)
	OAuthFacebookPage.prototype.constructor = OAuthFacebookPage;
	OAuthFacebookPage.prototype.init = function(data) {
		DBO.prototype.init.call(this, data);
		this.id = parseInt(this.field('id'));
	}
	OAuthFacebookPage.prototype.onAdded = function(fromInit) {
		this.createItem(fromInit);
		$('#social-homepage-facebookacc-list-none').hide();
		//if (!fromInit) {
		//	$('#social-addfacebookpage-cancel').click();
		//}
	}
	OAuthFacebookPage.prototype.onRemoved = function() {
		this.removeItem();

		if (impresslist.oauthFacebookPages.length - 1 == 0) {
			$('#social-homepage-facebookacc-list-none').show();
		}
	}
	OAuthFacebookPage.prototype.createItem = function(fromInit) {
		var html = "";
		html += "<p id='social-facebookpage-" + this.id + "'>";
		html += "	<img src='" + this.field("page_image") + "' /> <a href='http://facebook.com/" + this.field("page_id") + "' target='new'>" + this.field("page_name") + "</a>";
		html += "	<button id='social-remove-facebookpage-" + this.id + "' class='btn btn-sm btn-danger fr'>X</button>";
		html += "</p>";
		$('#social-homepage-facebookacc-list').append(html);
		this.update();
	}
	OAuthFacebookPage.prototype.removeItem = function() {
		$("#social-facebookacc-" + this.id).remove();
	}
	OAuthFacebookPage.prototype.update = function() {
		var th = this;
		$("#social-remove-facebookpage-" + this.id).click(function(){
			API.removeOAuthFacebookPage(th);
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
		html += "	<a href='" + this.field("fullname") + "' target='new'><img style='padding:0px;width:100%;' src='" + this.field("fullname") + "' /></a>";

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
									<!-- <option value='fbpost' " + ((this.field('type')=='fbpost')?"selected='true'":"") + ">Facebook Post</option>\
									<option value='fbshare' " + ((this.field('type')=='fbshare')?"selected='true'":"") + ">Facebook Share</option>-->\
								</select>\
							</div>\
						</div>\
						<div class='col-sm-6'>\
							<div class='form-group'>\
								<label for='account'>Account:&nbsp; </label> \
								<select id='social-modal-account'  class='form-control'>";
								/*html += "<option value='---'> --- Twitter Accounts ---</option>";
									for(var i = 0; i < impresslist.oauthTwitterAccounts.length; i++) {
										var tw = impresslist.oauthTwitterAccounts[i];
										html += "<option value='" + tw.field("id") + "' " + ((this.field('typedata').account==tw.id)?"selected='true'":"") + " >@" + tw.field("twitter_handle") + "</option>";
									}
									html += "<option value='---'> --- Facebook Pages ---</option>";
									for(var i = 0; i < impresslist.oauthFacebookPages.length; i++) {
										var fbp = impresslist.oauthFacebookPages[i];
										html += "<option value='" + fbp.field("id") + "' " + ((this.field('typedata').account==fbp.id)?"selected='true'":"") + " >" + fbp.field("page_name") + " (Page)</option>";
									}*/
		html += "				</select>\
							</div>\
						</div>\
					</div>";

		html += "	<div class='form-group'> \
						<label for='timepicker'>Date &amp; Time:</label>\
						<div class='input-group date' id='social-modal-timepicker'>\
							<input id='social-modal-edit-timestamp' type='text' class='form-control' value=''/>\
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

			// Facebook Post
			html += "	<div class='social-modal-fbpost' style='display:none;'>";
			html += "		<div class='form-group'>\
								<label for='message'>Message: </label> \
								<textarea id='social-modal-fbpost-message' data-socialitem-id='" + this.id + "' class='form-control' style='height:100px;'>" + ((this.field('typedata').message)?this.field('typedata').message:"") + "</textarea>\
							</div>";
			html += "		<div class='form-group'>\
								<label for='attachments'>Attachments:&nbsp; </label>\
								<select id='social-modal-fbpost-attachments' multiple='multiple'>";
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
							<div class='social-modal-fbpost-attachment-preview-container' style='margin-bottom:15px;'></div>\
						</div>";

			// Facebook Share
			html += "	<div class='social-modal-fbshare' style='display:none;'> \
							<div class='form-group'>\
								<label for='account'>Post:&nbsp; </label> \
								<select id='social-modal-fbshare-select'  class='form-control'>";
									for(var i = 0; i < impresslist.socialTimelineItems.length; i++) {
										var it = impresslist.socialTimelineItems[i];
										if (it.field('type') == 'fbpost') {
											html += "<option value='" + it.field("id") + "' " + ((this.field('typedata').tweet==it.id)?"selected='true'":"") + " >" + impresslist.findOAuthFacebookPageById(parseInt(it.field('typedata').account)).field('page_name') + " " + it.field('typedata').message + "</option>";
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
			utime = Date.now() / 1000;
		}
		$('#social-modal-timepicker').datetimepicker();
		$('#social-modal-timepicker').data("DateTimePicker").defaultDate(moment(utime, "X"));
		$('#social-modal-timepicker').data("DateTimePicker").format("MMMM Do YYYY HH:mm zz");

		var th = this;
		var onAttachmentsChanged = function(type) {
			var selectedOptions = $('#social-modal-'+type+'-attachments option:selected');
			var selectedOptionValues = $('#social-modal-'+type+'-attachments option:selected').map(function(a, item){return item.value;});
        	var html = "";
        	for(var i = 0; i < selectedOptionValues.length; i++) {
        		html += "<img src='images/uploads/" + selectedOptionValues[i] + "' style='width:100px;height:100px;margin-right:5px;'/>";
        	}
            $('.social-modal-'+type+'-attachment-preview-container').html(html);
            th.testAttachments = selectedOptionValues.length;
            th.updateTwitterCharactersLeft();
		}
		$('#social-modal-tweet-attachments').multiselect({
			enableFiltering: true,
			//includeSelectAllOption: true,
			enableCaseInsensitiveFiltering: true,
			onSelectAll: function() { onAttachmentsChanged('tweet'); },
			onChange: function() { onAttachmentsChanged('tweet'); }
		});
		$('#social-modal-fbpost-attachments').multiselect({
			enableFiltering: true,
			enableCaseInsensitiveFiltering: true,
			onSelectAll: function() { onAttachmentsChanged('fbpost'); },
			onChange: function() { onAttachmentsChanged('fbpost'); }
		});

		var onTypeChanged = function(ty) {
			$('.social-modal-blank').hide();
			$('.social-modal-tweet').hide();
			$('.social-modal-retweet').hide();
			$('.social-modal-fbpost').hide();
			$('.social-modal-fbshare').hide();
			if (ty == 'tweet') {
				onAttachmentsChanged(ty);
				$('.social-modal-tweet').show();
			} else if (ty == 'retweet') {
				$('.social-modal-retweet').show();
			} else if (ty == 'fbpost') {
				onAttachmentsChanged(ty);
				$('.social-modal-fbpost').show();
			} else if (ty == 'fbshare') {
				$('.social-modal-fbshare').show();
			}

			$('#social-modal-account').html("");
			if (ty == "tweet" || ty == "retweet") {
				var html = "<option value='---'> --- Twitter Accounts ---</option>";
				for(var i = 0; i < impresslist.oauthTwitterAccounts.length; i++) {
					var tw = impresslist.oauthTwitterAccounts[i];
					html += "<option value='" + tw.field("id") + "' " + ((th.field('typedata').account==tw.id)?"selected='true'":"") + " >@" + tw.field("twitter_handle") + "</option>";
				}
				$('#social-modal-account').html(html);
			}
			if (ty == "fbpost" || ty == "fbshare") {
				var html = "<option value='---'> --- Facebook Pages ---</option>";
				for(var i = 0; i < impresslist.oauthFacebookPages.length; i++) {
					var fbp = impresslist.oauthFacebookPages[i];
					html += "<option value='" + fbp.field("id") + "' " + ((th.field('typedata').account==fbp.id)?"selected='true'":"") + " >" + fbp.field("page_name") + " (Page)</option>";
				}
				$('#social-modal-account').html(html);
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
		var acc;
		var thedate = moment(this.field('timestamp'), "X").format("MMMM Do YYYY HH:mm zz");
		var reldate = impresslist.util.relativetime(this.field('timestamp'));
		if (this.field("type") == "tweet") {
			acc = impresslist.findOAuthTwitterAccountById(parseInt(this.field('typedata').account));
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
								<img class='icon' src='" + acc.field("twitter_image") + "' /> \
								<p style='min-height:40px;margin-bottom:0px;'>" + this.field('typedata').message + "</p>";

								var attachments = this.field('typedata').attachments;
								if (attachments.length > 0) {
									html += "<p class='text-muted attachments'>Attachment/s: " + attachments.length + " <i class='glyphicon glyphicon-picture'></i></p>";
									for(var i = 0; i < attachments.length; i++) {
										html += "<p class='socialqueue-imageattachment-container' data-social-id='" + this.id + "'  data-attachment-id='" + i + "'><img class='socialqueue-imageattachment' data-social-id='" + this.id + "' data-attachment-id='" + i + "' src='images/uploads/" + attachments[i].file + "'/></p>";
									}
								}

			html += "		</div> \
						</div>";
		} else if (this.field("type") == "retweet") {
			acc = impresslist.findOAuthTwitterAccountById(parseInt(this.field('typedata').account));
			html += "	<div class='socialqueue-item indented thinnest " + (this.field('ready')==1?"ready":"") + "'> \
							<div class='oa'> \
								<img class='icon' src='" + acc.field("twitter_image") + "'/> \
								<p class='fl'><b>Retweet by <a href='http://twitter.com/" + acc.field('twitter_handle') + "'>@" + acc.field('twitter_handle') + "</a></b></p> \
								<p class='fr text-muted' style='margin-left:10px;'> <a data-socialitem-id='" + this.id + "' data-toggle='modal' data-target='.social_modal' style='cursor:pointer'><i class='glyphicon glyphicon-pencil'></i></a></p> \
								<p class='fr text-muted'><i class='glyphicon glyphicon-time'></i> <span data-social-id='" + this.id + "' data-toggle='tooltip' data-placement='left' title='" + reldate + "'>" + thedate + "</span> </p> \
							</div> \
						</div>";
		} else if (this.field("type") == "fbpost") {
			acc = impresslist.findOAuthFacebookPageById(parseInt(this.field('typedata').account));
			html += "	<div class='socialqueue-item " + (this.field('ready')==1?"ready":"") + "'> \
							<div class='oa'> \
								<p class='fl'><b>Facebook: <a href='http://facebook.com/" + acc.field('page_id') + "'>" + acc.field('page_name') + "</a></b></p>";
			html += "			<p class='fr text-muted' style='margin-left:10px;'> <a data-socialitem-id='" + this.id + "' data-toggle='modal' data-target='.social_modal'    style='cursor:pointer' title='Edit'><i class='glyphicon glyphicon-pencil'></i></a></p>";
			if (this.field('ready')==1) {
				html += "		<p class='fr text-muted' style='margin-left:10px;'> <a class='social-timeline-opensharedialog' data-socialitem-id='" + this.id + "' data-toggle='modal' data-target='.socialShare_modal' style='cursor:pointer' title='Add Shares'><i class='glyphicon glyphicon-plus'></i></a></p>";
			}
			html += "			<p class='fr text-muted'><i class='glyphicon glyphicon-time'></i> <span data-social-id='" + this.id + "' data-toggle='tooltip' data-placement='left' title='" + reldate + "'>" + thedate + "</span></p> \
							</div> \
							<div> \
								<img class='icon' src='" + acc.field("page_image") + "' /> \
								<p style='min-height:40px;margin-bottom:0px;'>" + this.field('typedata').message + "</p>";

								var attachments = this.field('typedata').attachments;
								if (attachments.length > 0) {
									html += "<p class='text-muted attachments'>Attachment/s: " + attachments.length + " <i class='glyphicon glyphicon-picture'></i></p>";
									for(var i = 0; i < attachments.length; i++) {
										html += "<p class='socialqueue-imageattachment-container' data-social-id='" + this.id + "'  data-attachment-id='" + i + "'><img class='socialqueue-imageattachment' data-social-id='" + this.id + "' data-attachment-id='" + i + "' src='images/uploads/" + attachments[i].file + "'/></p>";
									}
								}

			html += "		</div> \
						</div>";
		} else if (this.field("type") == "fbshare") {
			acc = impresslist.findOAuthFacebookPageById(parseInt(this.field('typedata').account));
			html += "	<div class='socialqueue-item indented thinnest " + (this.field('ready')==1?"ready":"") + "'> \
							<div class='oa'> \
								<img class='icon' src='" + acc.field("page_image") + "'/> \
								<p class='fl'><b>Facebook Share by <a href='http://facebook.com/" + acc.field('page_id') + "'>" + acc.field('page_name') + "</a></b></p> \
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
				tweet: tw,
				account: account
			};
			API.saveSocialTimelineItem(this, type, typedata, timestamp, ready);
		}
		else if (type == 'fbpost') {
			var account = $("#social-modal-account").val();
			var typedata = {
				message: $('#social-modal-fbpost-message').val(),
				account: account,
				attachments: []
			};

			var selectedOptionValues = $('#social-modal-fbpost-attachments option:selected').map(function(a, item){return item.value;});
			for(var i = 0; i < selectedOptionValues.length; i++) {
				typedata.attachments.push({
					"type": "image",
					"file": selectedOptionValues[i]
				});
			}

			API.saveSocialTimelineItem(this, type, typedata, timestamp, ready);
		}
		else if (type == 'fbshare') {
			var account = $("#social-modal-account").val();
			var fbpid = $('#social-modal-fbshare-select').val();
			var typedata = {
				post: fbpid,
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
			audience: 0,
			gmail: 0,
			admin: false,
			superadmin: false,
			imapServer: '',
			smtpServer: ''
		},
		tags: {

		},
		misc: {
			countries: []
		}
	},
	people: [],
	publications: [],
	personPublications: [],
	personYoutubeChannels: [],
	personTwitchChannels: [],
	youtubers: [],
	twitchchannels: [],
	podcasts: [],
	emails: [],
	users: [],
	companies: [],
	games: [],
	audiences: [],
	coverage: [],
	watchedgames: [],
	simpleMailouts: [],
	oauthTwitterAccounts: [],
	oauthFacebookAccounts: [],
	oauthFacebookPages: [],
	socialUploads: [],
	socialTimelineItems: [],
	loading: {
		people: false,
		publications: false,
		personPublications: false,
		personYoutubeChannels: false,
		personTwitchChannels: false,
		youtubeChannels: false,
		twitchChannels: false,
		emails: false,
		users: false,
		companies: false,
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
		},
		category: {
			home: 0,
			home_loaded: false,
			social: 0,
			social_loaded: false,
			mailout: 0,
			mailout_loaded: false,
			project: 0,
			project_loaded: false
		},
		onPageChanged: function(page) {
			console.log('Page changed to:', page);

			if (page == "home" && !this.category.home_loaded) {
				this.load_home();
			}
			else if (page == "social" && !this.category.social_loaded) {
				this.load_social();
			}
			else if (page == "mailout" && !this.category.mailout_loaded) {
				this.load_mailout();
			}
			else if (page == "project" && !this.category.project_loaded) {
				this.load_project();
			}
		},
		onCategoryItemLoaded: function(category) {
			impresslist.loading.category[category] -= 1;
			if (impresslist.loading.category[category] == 0) {
				impresslist.loading.category[category + "_loaded"] = true;
				this.onCategoryFullyLoaded(category);
				impresslist.refreshTotals();
			}
		},
		onCategoryFullyLoaded: function(category) {
			if (category == "project") {
				if (impresslist.coverage.length == 0) { $('#coverage-footer').show(); } else { $('#coverage-footer').hide(); }
				if (impresslist.watchedgames.length == 0) { $('#watchedgames-footer').show(); } else { $('#watchedgames-footer').hide(); }
			}
		},
		load_home: function() {
			if (impresslist.loading.category.home_loaded || impresslist.loading.category.home > 0) { return; }
			console.log("Loading Home...");
			// Audience
			impresslist.loading.category.home += 8;
			//API.listJobs();
			API.listPeople();
			API.listPublications();
			API.listPersonPublications();
			API.listPersonYoutubeChannels();
			API.listPersonTwitchChannels();
			API.listYoutubeChannels();
			API.listTwitchChannels();
			API.listEmails();
		},
		load_social:function() {
			if (impresslist.loading.category.social_loaded || impresslist.loading.category.social > 0) { return; }
			// Social
			console.log("Loading Social...");
			impresslist.loading.category.social += 5;
			API.listOAuthTwitterAccounts();
			API.listOAuthFacebookAccounts();
			API.listOAuthFacebookPages();
			setTimeout(function(){ // TODO: Fix this properly - there's a race condition.
				impresslist.loading.load_social_timeline();
			},1000);
		},
		load_social_timeline: function() {
			API.listSocialTimeline();
			API.listSocialUploads();
		},
		load_mailout:function() {
			if (impresslist.loading.category.mailout_loaded || impresslist.loading.category.mailout > 0) { return; }
			// Mailouts
			console.log("Loading Mailouts");
			impresslist.loading.category.mailout += 1;
			API.listSimpleMailouts();
		},
		load_project:function() {
			if (impresslist.loading.category.project_loaded || impresslist.loading.category.project > 0) { return; }

			console.log("Loading Project");
			impresslist.loading.category.project += 2;
			API.listCoverage(true, function(data) {
				$('#coverage-stats-table').html( impresslist.util.templates.StatsTable(data.stats) );
			});
			API.listWatchedGames();
		}
	},
	route: function() {
		var path = window.location.pathname;
		if (path == "/") {
			impresslist.changePageTo('home');
		}
		else if (path == "/mailout/") {
			impresslist.changePageTo('mailout');
		}
		else if (path == "/project/") {
			impresslist.changePageTo('project');
		}
		else if (path == "/social/") {
			impresslist.changePageTo('social');
		}
	},
	init: function() {

		this.route();

		// Navigation links
		var thiz = this;
		$('#nav-add-person').click(API.addPerson);
		$('#nav-add-publication').click(API.addPublication);
		$('#nav-add-youtuber').click(API.addYoutuber);
		$('#nav-add-twitchchannel').click(API.addTwitchChannel);
		$('#nav-add-podcast').click(API.addPodcast);
		$('#nav-add-youtuber-search-batch').click(function() { YouTuberBatchModal.open(); });
		$('#nav-add-coverage-publication').click(API.addPublicationCoverage);
		$('#nav-add-coverage-youtuber').click(API.addYoutuberCoverage);
		$('#nav-add-simplemailout').click(API.addSimpleMailout);
		$('#nav-add-user').click(API.addUser);
		$('#nav-user-changeproject').click(function() { thiz.findUserById(thiz.config.user.id).openChangeProject(); });
		$('#nav-user-changeaudience').click(function() { thiz.findUserById(thiz.config.user.id).openChangeAudience(); });
		$('#nav-user-changepassword').click(function() { thiz.findUserById(thiz.config.user.id).openChangePassword(); });
		$('#nav-user-changeimapsettings').click(function() { thiz.findUserById(thiz.config.user.id).openChangeIMAPSettings(); });
		$('.nav-user-changeimapsettings').click(function() { $('#nav-user-changeimapsettings').click(); });
		$('#nav-home').click(this.changePage);
		$('#nav-social').click(this.changePage);
		$('#nav-mailout').click(this.changePage);
		$('#nav-mailout-addrecipients').click(this.changePage);
		$('#nav-mailout-tips').click(this.changePage);
		$('#nav-project').click(this.changePage);
		$('#nav-admin').click(this.changePage);
		$('#nav-importtool').click(this.changePage);
		$('#nav-help').click(this.changePage);
		$('#nav-feedback').attr("href", impresslist.util.mailtoClient("ashley@forceofhab.it", "impresslist feedback", ""));
		$('#sql-query-submit').click(function() { API.sqlQuery( $('#sql-query-text').val() ); });

		$('#nav-add-project').click(function(){
			API.request('/project/add/', {
				name: $('#nav-add-project-name').val(),
				iconurl: $('#nav-add-project-iconurl').val(),
			},
				function(data) {
					var game = new Game(data.project);
					impresslist.addGame(game, false);
				},
				function() {
					API.errorMessage("Could not add Project.");
				}
			);
		});


		// Dynamic bits.
		$('#current-user-name').html(this.findUserById(this.config.user.id).fullname());
		$('#current-project-name').html(this.findGameById(this.config.user.game).field('name'));
		$("[data-last-backup='true']").html(new Date(this.config.system.backups.lastmanual*1000).toUTCString());

		// Full Search.
		var fullsearchKeyUpTimeout = null;
		$('#full-search').keyup(function(key){
			if (impresslist.util.cancellableKeys.indexOf(key.keyCode) >= 0) { return; }
			if (fullsearchKeyUpTimeout != null) { clearTimeout(fullsearchKeyUpTimeout); fullsearchKeyUpTimeout = null; }
			fullsearchKeyUpTimeout = setTimeout(function() {
				if (fullsearchKeyUpTimeout != null) {
					//impresslist.refreshFilter();//
					var q = $('#full-search').val();
					if (q.length > 0) {

						$('#full-search-results-container').show();
						$('#full-search-results-loading').show();
						$('#full-search-results').hide();
						$('#full-search-results-error').hide();
						API.search(q, function(results) {
							$('#full-search-results-loading').hide();

							// populate with results
							var html = "";

							// People
							html += "<h4 style='margin-top:0px;font-weight:bold;'>People:</h4>";
							if (results.people.length > 0) {
								html += "<table class='table'>";
								for(var i = 0; i < results.people.length; i++) {
									var p = results.people[i];
									html += "	<tr onclick=\"impresslist.findPersonById(" + p.id + ").open();\" data-person-id='" + p.id + "' data-person-tablerow='true' data-toggle='modal' data-target='.person_modal' style='cursor:pointer;'>\
													<td data-value='" + p.forename + " " + p.surnames + "'><span data-person-id='" + p.id + "' data-field='name'>" + p.firstname + " " + p.surnames + "</span></td> \
													<td data-person-id='" + p.id + "'><a href='http://twitter.com/" + p.twitter + "' target='new'>" + p.twitter + "</a></td> \
													<td data-person-id='" + p.id + "'data-value='" + p.tags + "'>" + impresslist.util.buildTags(p.tags) + "</td> \
												</tr>";
								}
								html += "</table>";
							} else {
								html += "<p style='margin-bottom:20px;font-weight:bold;'>None</h3>";
							}

							// Publications
							html += "<h4 style='margin-top:0px;font-weight:bold;'>Publications:</h4>";
							if (results.publications.length > 0) {
								html += "<table class='table'>";
								for(var i = 0; i < results.publications.length; i++) {
									var p = results.publications[i];
									html += "	<tr onclick=\"impresslist.findPublicationById(" + p.id + ").open();\" data-publication-id='" + p.id + "' data-publication-tablerow='true' data-toggle='modal' data-target='.publication_modal' style='cursor:pointer;'>\
													<td data-value='" + p.name + "'><span data-publication-id='" + p.id + "' data-field='name'>" + p.name + "</span></td> \
													<td data-publication-id='" + p.id + "'><a href='http://twitter.com/" + p.twitter + "' target='new'>" + p.twitter + "</a></td> \
													<td data-publication-id='" + p.id + "'data-value='" + p.tags + "'>" + impresslist.util.buildTags(p.tags) + "</td> \
												</tr>";
								}
								html += "</table>";
							} else {
								html += "<p style='margin-bottom:20px;font-weight:bold;'>None</h3>";
							}

							// YouTubers
							html += "<h4 style='margin-top:0px;font-weight:bold;'>YouTubers:</h4>";
							if (results.youtubers.length > 0) {
								html += "<table class='table'>";
								for(var i = 0; i < results.youtubers.length; i++) {
									var p = results.youtubers[i];
									html += "	<tr onclick=\"impresslist.findYoutuberById(" + p.id + ").open();\" data-youtuber-id='" + p.id + "' data-youtuber-tablerow='true' data-toggle='modal' data-target='.youtuber_modal' style='cursor:pointer;'>\
													<td data-value='" + p.name + "'><span data-youtuber-id='" + p.id + "' data-field='name'>" + p.name + "</span></td> \
													<td data-youtuber-id='" + p.id + "'><a href='http://twitter.com/" + p.twitter + "' target='new'>" + p.twitter + "</a></td> \
													<td data-youtuber-id='" + p.id + "'data-value='" + p.tags + "'>" + impresslist.util.buildTags(p.tags) + "</td> \
												</tr>";
								}
								html += "</table>";
							} else {
								html += "<p style='margin-bottom:20px;font-weight:bold;'>None</h3>";
							}

							// Twitch Channels
							html += "<h4 style='margin-top:0px;font-weight:bold;'>Twitch Channels:</h4>";
							if (results.twitchchannels.length > 0) {
								html += "<table class='table'>";
								for(var i = 0; i < results.twitchchannels.length; i++) {
									var p = results.twitchchannels[i];
									html += "	<tr onclick=\"impresslist.findTwitchChannelById(" + p.id + ").open();\" data-twitchchannel-id='" + p.id + "' data-twitchchannel-tablerow='true' data-toggle='modal' data-target='.twitchchannel_modal' style='cursor:pointer;'>\
													<td data-value='" + p.name + "'><span data-twitchchannel-id='" + p.id + "' data-field='name'>" + p.name + "</span></td> \
													<td data-twitchchannel-id='" + p.id + "'><a href='http://twitter.com/" + p.twitter + "' target='new'>" + p.twitter + "</a></td> \
													<td data-twitchchannel-id='" + p.id + "'data-value='" + p.tags + "'>" + impresslist.util.buildTags(p.tags) + "</td> \
												</tr>";
								}
								html += "</table>";
							} else {
								html += "<p style='margin-bottom:0px;margin-bottom:0px;font-weight:bold;'>None</h3>";
							}


							$('#full-search-results').html(html);
							$('#full-search-results').show();

						}, function(){
							$('#full-search-results-loading').hide();
							$('#full-search-results').show();
							// populate with error message.
							$('#full-search-results-error').html("There was an error in the request?");
							$('#full-search-results-error').show();
						});
					} else {
						$('#full-search-results-container').hide();
						$('#full-search-results-error').hide();
						$('#full-search-results').hide();
					}
				}
				fullsearchKeyUpTimeout = null;
			}, 300);
		});


		// Set up search / filter
		var searchKeyUpTimeout = null;
		$('#filter-search').keyup(function(key){
			if (impresslist.util.cancellableKeys.indexOf(key.keyCode) >= 0) { return; }
			if (searchKeyUpTimeout != null) { clearTimeout(searchKeyUpTimeout); searchKeyUpTimeout = null; }
			searchKeyUpTimeout = setTimeout(function() {
				if (searchKeyUpTimeout != null) { impresslist.refreshFilter(); }
				searchKeyUpTimeout = null;
			}, 300);
		});
		$('#filter-recent-contact').change(this.refreshFilter);
		$('#filter-personal-contact').change(this.refreshFilter);
		$('#filter-high-priority').change(this.refreshFilter);
		$('#filter-email-attached').change(this.refreshFilter);
		$('#filter-assigned-self').change(this.refreshFilter);
		$('#filter-show-outofdate').change(this.refreshFilter);
		$('.filter-tag').change(this.refreshTagFilter);

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


			var formData = new FormData();
			formData.append("data", importString);
			var url = "api.php?endpoint=/import/";
			//url += "&data=" + encodeURIComponent(importString);
			url += "&audience=" + encodeURIComponent(impresslist.config.user.audience);
			url += "&type=" + encodeURIComponent(importType);
			url += "&order=" + encodeURIComponent(importOrder.join());
			$.ajax({
				url: url,
				type: "POST",
				data: formData,
				contentType:false,
				cache:false,
				processData:false,
				success: function(result) {
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
				},
				error: function(e, e2, e3) {
					API.errorMessage("Could not import list.");
					importtool_disableForm(false);
				}
			});

		});

		// Mailout tool.
		$('#mailout-content').keyup(function(){

			var curGame = impresslist.findGameById(impresslist.config.user.game).name;
			var steam_keys_md = "**Steam Keys:**\n\n";
			steam_keys_md += "* XXXXX-XXXXX-XXXXX *(Sent by Nick on 23rd March 2015)*\n";
			steam_keys_md += "* XXXXX-XXXXX-XXXXX *(Sent by Ashley on 3rd March 2015)*\n\n";

			var switch_keys_md = "**Nintendo Switch Keys:**\n\n";
			switch_keys_md += "* XXXXX-XXXXX-XXXXX *(Sent by Nick on 23rd March 2015)*\n";
			switch_keys_md += "* XXXXX-XXXXX-XXXXX *(Sent by Ashley on 3rd March 2015)*\n\n";

			var md_content = $(this).val();
			md_content = md_content.replace("{{first_name}}", "(First Name)");
			md_content = md_content.replace("{{steam_key}}", "XXXXX-XXXXX-XXXXX");
			md_content = md_content.replace("{{steam_keys}}", steam_keys_md);
			md_content = md_content.replace("{{switch_key}}", "XXXXX-XXXXX-XXXXX");
			md_content = md_content.replace("{{switch_keys}}", switch_keys_md);

			// foreach game project, check for {{switch_keys:game_name}}
			for(var i = 0; i < impresslist.games.length; i++) {

				var steam_keys_md = "**Steam Keys (" + impresslist.games[i].name + "):**\n\n";
				steam_keys_md += "* XXXXX-XXXXX-XXXXX *(Sent by Nick on 23rd March 2015)*\n";
				steam_keys_md += "* XXXXX-XXXXX-XXXXX *(Sent by Ashley on 3rd March 2015)*\n\n";

				var switch_keys_md = "**Nintendo Switch Keys (" + impresslist.games[i].name + "):**\n\n";
				switch_keys_md += "* XXXXX-XXXXX-XXXXX *(Sent by Nick on 23rd March 2015)*\n";
				switch_keys_md += "* XXXXX-XXXXX-XXXXX *(Sent by Ashley on 3rd March 2015)*\n\n";

				md_content = md_content.replace("{{switch_keys:" + impresslist.games[i].field('nameuniq') + "}}", switch_keys_md);
				md_content = md_content.replace("{{steam_keys:" + impresslist.games[i].field('nameuniq') + "}}", steam_keys_md);

			}

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
		$('.nav-select-edit').click(function(){
			impresslist.toggleSelectMode();
		});
		$('.nav-select-all').click(function(){
			var type = $(this).attr('data-type');
			selectAllCheckedForType(type, true);
			impresslist.refreshMailoutRecipients();
		});
		$('.nav-deselect-all').click(function(){
			var type = $(this).attr('data-type');
			selectAllCheckedForType(type, false);
			impresslist.refreshMailoutRecipients();
		});

		// Keys functions
		$('#nav-project').click(function() {
			$('.current-project-name').html(impresslist.findGameById(impresslist.config.user.game).field('name'));
		});
		$('#nav-keys').click(function() {

			var addremovekeysform = function(datacount){
				// remove keys form
				$('#keys-remove-submit').unbind('click');
				$('#keys-remove-platform').change(function(){
					var val = $('#keys-remove-platform').val();
					$('#keys-remove-subplatform-row').hide();
					if (val == "steam") {
						$('#keys-remove-subplatform').val("---");
					} else if (val == "switch") {
						$('#keys-remove-subplatform-row').show();
					}
				});
				$('#keys-remove-submit').click(function(){
					var submitGame = impresslist.config.user.game;
					var platform = $('#keys-remove-platform').val();
					var subplatform = $('#keys-remove-subplatform').val();
					var amount = $('#keys-remove-amount').val();
					if (subplatform == "---") { subplatform = ''; }

					API.popKeys(submitGame, platform, subplatform, amount, function(data) {

						// show removed keys
						var html = "";
						for(var i = 0; i < data.keys.length; i++) {
							html += "<li>" + data.keys[i].keystring + "</li>";
						}
						$('#keys-removed-list').html(html);
						$('#keys-removed-container').show();

						$('#steam_keys_available_count').html( parseInt($('#' + platform + '_keys_available_count').html()) - data.count);
					});

				});

				$('#keys-remove-form').show();
			}

			API.listKeys(impresslist.config.user.game, 'steam', '', true, function(data) {
				$('#steam_keys_assigned_count').html(data.count);
			});
			API.listKeys(impresslist.config.user.game, 'steam', '', false, function(data) {
				$('#steam_keys_available_count').html(data.count);
				if (data.count > 0) {
					addremovekeysform();
				}
			});

			var switchRegions = ['us', 'eu', 'au', 'jp'];
			for(var i = 0; i < switchRegions.length; i++) {
				var region = switchRegions[i];
				var dothis = function(region){
					API.listKeys(impresslist.config.user.game, 'switch', region, true, function(data) {
						$('#switch_' + region + '_keys_assigned_count').html(data.count);
					});
					API.listKeys(impresslist.config.user.game, 'switch', region, false, function(data) {
						$('#switch_' + region + '_keys_available_count').html(data.count);
						if (data.count > 0) {
							addremovekeysform();
						}
					});
				};
				dothis(region);
			}







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
		$('#keys-add-platform').change(function(){
			var val = $('#keys-add-platform').val();
			$('#keys-add-subplatform-row').hide();
			if (val == "steam") {
				$('#keys-add-subplatform').val("---");
			} else if (val == "switch") {
				$('#keys-add-subplatform-row').show();
			}
		});
		$('#keys-add-submit').click(function(){
			var keys_setFormEnabled = function(boo) {
				boo = !boo;
				$('#keys-add-platform').attr("disabled", boo);
				$('#keys-add-subplatform').attr("disabled", boo);
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

			var submitSubplatform = $('#keys-add-subplatform').val();
			if (submitSubplatform == "---") { submitSubplatform = ""; }

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
			API.addKeys(submitList, submitGame, submitPlatform, submitSubplatform, expiresOn, function(d) {
				keys_setFormEnabled(true);
				$('#keys-add-platform').val('---');
				$('#keys-add-subplatform').val('---');
				$('#keys-add-textfield').val('');
				$('#nav-keys').click();
			}, function(){
				keys_setFormEnabled(true);
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
		$('#social-addfacebookpage').click(function() {

			var html = "<div class='modal fade addfacebookpage_modal' tabindex='-1' role='dialog'> \
							<div class='modal-dialog'> \
								<div class='modal-content' style='padding:5px;'> \
									<div style='min-height:100px;padding:20px;'> \
										<h3>Add Facebook Page</h3> \
										<form id='social-addfacebookpage-form' role='form' class='oa' onsubmit='return false;'>\
											<p>Loading...</p>";
								/*			<div class='form-group'>\
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
											</div>\*/
			html += "					</form> \
									</div>\
								</div>\
							</div>\
						</div>";
			$('#modals').append(html);

			//$('#social-addfacebookpage').attr('disabled', 'disabled');
			API.queryOAuthFacebookPages(function(data){
				var html = "";
				for(var i = 0; i < data.length; i++) {
					html += "<p id='social-officialfacebookpage-" + data[i].id + "'>";
					html += "	<img src='" + data[i].image + "' /> <a href='http://facebook.com/" + data[i].id + "' target='new'>" + data[i].name + "</a>";
					html += "	<button id='social-add-officialfacebookpage-" + data[i].id + "' class='btn btn-lg btn-primary fr' style='margin-top:2px;' \
									data-page-id='" + data[i].id + "' \
									data-page-name='" + data[i].name + "' \
									data-page-accessToken='" + data[i].access_token + "' \
									data-page-image='" + data[i].image + "' \
									\
								>Add</button>";
					html += "</p>";
				}
				if (data.length == 0) {
					html += "<p>No pages...</p>";
				}
				$('#social-addfacebookpage-form').html(html);

				for(var i = 0; i < data.length; i++) {
					$('#social-add-officialfacebookpage-' + data[i].id).click(function() {
						var th = this;
						API.addOAuthFacebookPage(
							$(this).attr("data-page-id"),
							$(this).attr("data-page-name"),
							$(this).attr("data-page-accessToken"),
							$(this).attr("data-page-image"),
							function(dt) {
								$("#social-officialfacebookpage-" + dt.facebookpage.page_id).remove();
							}
						);

					});
				}

			});
		});


		// Chat functionality
		this.chat.init();

		// TODO functionality
		this.jobs.init();

		//this.refreshFilter();
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
		for(var i = 0; i < this.publications.length; i++) {
			this.publications[i].refreshEmails();
		}
		for(var i = 0; i < this.youtubers.length; i++) {
			this.youtubers[i].refreshEmails();
		}
		for(var i = 0; i < this.twitchchannels.length; i++) {
			this.twitchchannels[i].refreshEmails();
		}
		this.refreshMailoutRecipients();

		var type = 'person'; //$(this).attr('data-type');
		$('.checkbox-column[data-type="' + type+ '"]').each(function(){
			$(this).toggle();
		});
		type = 'publication'; //$(this).attr('data-type');
		$('.checkbox-column[data-type="' + type+ '"]').each(function(){
			$(this).toggle();
		});
		type = 'youtuber'; //$(this).attr('data-type');
		$('.checkbox-column[data-type="' + type+ '"]').each(function(){
			$(this).toggle();
		});
		type = 'twitchchannel'; //$(this).attr('data-type');
		$('.checkbox-column[data-type="' + type+ '"]').each(function(){
			$(this).toggle();
		});
		this.refreshFilter();


	},

	changePageTo: function(page) {
		$("[data-type-page='true']").hide();
		$("[data-type-page='true'][data-page='" + page + "']").show();
		impresslist.loading.onPageChanged(page);
	},
	changePage: function() {
		var page = $(this).attr('data-nav-page');
		impresslist.changePageTo(page);
	},


	refreshFilter: function() {
		impresslist.applyFilter($('#filter-search').val().toLowerCase());
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
		var countTwitchChannelsVisible = 0;
		for(var i = 0; i < impresslist.twitchchannels.length; i++) {
			if (impresslist.twitchchannels[i].filter(text)) { countTwitchChannelsVisible++; }
		}

		if (countPeopleVisible == 0) { $('#people-footer').show(); } else { $('#people-footer').hide(); }
		if (countPublicationsVisible == 0) { $('#publications-footer').show(); } else { $('#publications-footer').hide(); }
		if (countYoutubeChannelsVisible == 0) { $('#youtubers-footer').show(); } else { $('#youtubers-footer').hide(); }
		if (countTwitchChannelsVisible == 0) { $('#twitchchannels-footer').show(); } else { $('#twitchchannels-footer').hide(); }

		if (impresslist.people.length > 0 && countPeopleVisible == 0) { $('#people-container').hide(); } else { $('#people-container').show(); }
		if (impresslist.publications.length > 0 && countPublicationsVisible == 0) { $('#publications-container').hide(); } else { $('#publications-container').show(); }
		if (impresslist.youtubers.length > 0 && countYoutubeChannelsVisible == 0) { $('#youtubers-container').hide(); } else { $('#youtubers-container').show(); }
		if (impresslist.twitchchannels.length > 0 && countTwitchChannelsVisible == 0) { $('#twitchchannels-container').hide(); } else { $('#twitchchannels-container').show(); }

		$('#people-count').html("(" + countPeopleVisible + ")");
		$('#publication-count').html("(" + countPublicationsVisible + ")");
		$('#youtuber-count').html("(" + countYoutubeChannelsVisible + ")");
		$('#twitchchannel-count').html("(" + countTwitchChannelsVisible + ")");

		if (text.length > 0) { $('#chat-container').hide(); } else { $('#chat-container').show(); }
	},
	refreshTotals: function() {
		if (impresslist.people.length == 0) { $('#people-footer').show(); } else { $('#people-footer').hide(); }
		if (impresslist.publications.length == 0) { $('#publications-footer').show(); } else { $('#publications-footer').hide(); }
		if (impresslist.youtubers.length == 0) { $('#youtubers-footer').show(); } else { $('#youtubers-footer').hide(); }
		if (impresslist.twitchchannels.length == 0) { $('#twitchchannels-footer').show(); } else { $('#twitchchannels-footer').hide(); }

		$('#people-count').html("(" + impresslist.people.length + ")");
		$('#publication-count').html("(" + impresslist.publications.length + ")");
		$('#youtuber-count').html("(" + impresslist.youtubers.length + ")");
		$('#twitchchannel-count').html("(" + impresslist.twitchchannels.length + ")");
	},
	refreshTagFilter: function() {
		var tagsEnabled = [];
		$('.filter-tag').each(function(r, e) {
			if ($(e).prop("checked")) {
				tagsEnabled.push($(e).attr('data-tag'));
			}
		});
		impresslist.applyTagFilter(tagsEnabled);
	},
	applyTagFilter: function(tags) {
		var countPeopleVisible = 0;
		for(var i = 0; i < impresslist.people.length; i++) {
			if (impresslist.people[i].filterTags(tags)) { countPeopleVisible++; }
		}
		var countPublicationsVisible = 0;
		for(var i = 0; i < impresslist.publications.length; i++) {
			if (impresslist.publications[i].filterTags(tags)) { countPublicationsVisible++; }
		}
		var countYoutubeChannelsVisible = 0;
		for(var i = 0; i < impresslist.youtubers.length; i++) {
			if (impresslist.youtubers[i].filterTags(tags)) { countYoutubeChannelsVisible++; }
		}
		var countTwitchChannelsVisible = 0;
		for(var i = 0; i < impresslist.twitchchannels.length; i++) {
			if (impresslist.twitchchannels[i].filterTags(tags)) { countTwitchChannelsVisible++; }
		}

		if (countPeopleVisible == 0) { $('#people-footer').show(); } else { $('#people-footer').hide(); }
		if (countPublicationsVisible == 0) { $('#publications-footer').show(); } else { $('#publications-footer').hide(); }
		if (countYoutubeChannelsVisible == 0) { $('#youtubers-footer').show(); } else { $('#youtubers-footer').hide(); }
		if (countTwitchChannelsVisible == 0) { $('#twitchchannels-footer').show(); } else { $('#twitchchannels-footer').hide(); }

		if (impresslist.people.length > 0 && countPeopleVisible == 0) { $('#people-container').hide(); } else { $('#people-container').show(); }
		if (impresslist.publications.length > 0 && countPublicationsVisible == 0) { $('#publications-container').hide(); } else { $('#publications-container').show(); }
		if (impresslist.youtubers.length > 0 && countYoutubeChannelsVisible == 0) { $('#youtubers-container').hide(); } else { $('#youtubers-container').show(); }
		if (impresslist.twitchchannels.length > 0 && countTwitchChannelsVisible == 0) { $('#twitchchannels-container').hide(); } else { $('#twitchchannels-container').show(); }

		console.log('countPeopleVisible', countPeopleVisible);
		console.log('countPublicationsVisible', countPublicationsVisible);
		console.log('countYoutubeChannelsVisible', countYoutubeChannelsVisible);
		console.log('countTwitchChannelsVisible', countTwitchChannelsVisible);

		$('#people-count').html("(" + countPeopleVisible + ")");
		$('#publication-count').html("(" + countPublicationsVisible + ")");
		$('#youtuber-count').html("(" + countYoutubeChannelsVisible + ")");
		$('#twitchchannel-count').html("(" + countTwitchChannelsVisible + ")");
	},
	refreshMailoutRecipients: function() {
		var peopleSelected = $('input[data-type="person"][data-checkbox="true"]:checked');
		var publicationsSelected = $('input[data-type="publication"][data-checkbox="true"]:checked');
		var youtubersSelected = $('input[data-type="youtuber"][data-checkbox="true"]:checked');
		var twitchchannelsSelected = $('input[data-type="twitchchannel"][data-checkbox="true"]:checked');

		if (peopleSelected.length + publicationsSelected.length + youtubersSelected.length + twitchchannelsSelected.length == 0) {
			$('#mailout-recipients-none').show();
			$('#mailout-recipients').hide();
			return;
		}

		var combined = [];
		for(var i = 0; i < peopleSelected.length; i++) { combined.push(peopleSelected[i]); };
		for(var i = 0; i < publicationsSelected.length; i++) { combined.push(publicationsSelected[i]); };
		for(var i = 0; i < youtubersSelected.length; i++) { combined.push(youtubersSelected[i]); };
		for(var i = 0; i < twitchchannelsSelected.length; i++) { combined.push(twitchchannelsSelected[i]); };

		var html = "";
		for(var i = 0; i < combined.length; i++) {

			var readBool = $(combined[i]).attr('mailout-read');
			var remindedTwitterDM = $(combined[i]).attr('mailout-reminded-twitter-dm') == "true";
			var personName = $(combined[i]).attr('data-mailout-name');
			var personType = $(combined[i]).attr('data-mailout-type');
			var personTypeId = $(combined[i]).attr('data-mailout-typeid');
			var personTypeName = $(combined[i]).attr('data-mailout-typename');
			html += "<tr>"
			html += "	<td data-value='" + personName + "'>" + personName + "</td>";
			html += "	<td data-value='" + personTypeName + "'>" + personTypeName + "</td>";
			html += "	<td class='mailout-readflags mailout-read-" + readBool + "' data-value='" + readBool + "'>" + readBool + "</td>";
			html += "	<td class='mailout-readflags'>";
			if (remindedTwitterDM) {
				html += "	• DM : <a href='javascript:;'>done!</a>";
			} else {
				html += "	• <a id='mailout-recipient-remind-twitter-dm-" + personType + "-" + personTypeId + "' href='javascript:;'>DM</a>";
			}
			html += "	</td>";
			// " + $(peopleSelected[i]).attr('data-mailout-email') + "
			html += "</tr>";
		}

		$('#mailout-recipients-tbody').html(html);

		for(var i = 0; i < combined.length; i++) {
			var personType = $(combined[i]).attr('data-mailout-type');
			var personTypeId = $(combined[i]).attr('data-mailout-typeid');
			var openFunc = function(personType, personTypeId) {
				$("#mailout-recipient-remind-twitter-dm-" + personType + "-" + personTypeId).click(function(){

					var obj = null;
					if (personType == "person") {
						obj = impresslist.findPersonById(personTypeId);
					}
					else if (personType == "publication") {
						obj = impresslist.findPublicationById(personTypeId);
					}
					else if (personType == "personPublication") {
						personId = impresslist.findPersonPublicationById(personTypeId).person.id;
						obj = impresslist.findPersonById(personId);
					}
					else if (personType == "youtuber") {
						obj = impresslist.findYoutuberById(personTypeId);
					}
					else if (personType == "twitchchannel") {
						obj = impresslist.findTwitchChannelById(personTypeId);
					}
					//TwitterDM.open();
				});
			}
			openFunc(personType, personTypeId);
		}

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
	addPersonTwitchChannel: function(obj, fromInit) {
		this.personTwitchChannels.push(obj);
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
	addCompany: function(obj, fromInit) {
		this.companies.push(obj);
		obj.onAdded();
		if (!fromInit) { impresslist.refreshFilter(); }
	},
	addYoutuber: function(obj, fromInit) {
		this.youtubers.push(obj);
		obj.onAdded();
		if (!fromInit) { impresslist.refreshFilter(); }
	},
	addTwitchChannel: function(obj, fromInit) {
		this.twitchchannels.push(obj);
		obj.onAdded();
		if (!fromInit) { impresslist.refreshFilter(); }
	},
	addPodcast: function(obj, fromInit) {
		this.podcasts.push(obj);
		obj.onAdded();
		if (!fromInit) { impresslist.refreshFilter(); }
	},
	addGame: function(obj, fromInit) {
		this.games.push(obj);
		obj.onAdded();
	},
	addAudience: function(obj, fromInit) {
		this.audiences.push(obj);
		obj.onAdded();
	},
	addCoverage: function(obj, fromInit) {
		this.coverage.push(obj);
		obj.onAdded(fromInit);
	},
	addWatchedGame: function(obj, fromInit) {
		this.watchedgames.push(obj);
		obj.onAdded(fromInit);
	},
	removeWatchedGame: function(obj) {
		for(var i = 0, len = this.watchedgames.length; i < len; ++i) {
			if (this.watchedgames[i].id == obj.id) {
				console.log('watched game removed: ' + obj.id);
				obj.onRemoved();
				this.watchedgames.splice(i, 1);
				impresslist.refreshFilter();
				break;
			}
		}
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
	addOAuthFacebookAccount: function(obj, fromInit) {
		this.oauthFacebookAccounts.push(obj);
		obj.onAdded(fromInit);
	},
	removeOAuthFacebookAccount: function(obj) {
		for(var i = 0, len = this.oauthFacebookAccounts.length; i < len; ++i) {
			if (this.oauthFacebookAccounts[i].id == obj.id) {
				console.log('oauth facebook removed: ' + obj.id);
				obj.onRemoved();
				this.oauthFacebookAccounts.splice(i, 1);
				break;
			}
		}
	},
	addOAuthFacebookPage: function(obj, fromInit) {
		this.oauthFacebookPages.push(obj);
		obj.onAdded(fromInit);
	},
	removeOAuthFacebookPage: function(obj) {
		for(var i = 0, len = this.oauthFacebookPages.length; i < len; ++i) {
			if (this.oauthFacebookPages[i].id == obj.id) {
				console.log('oauth facebook page removed: ' + obj.id);
				obj.onRemoved();
				this.oauthFacebookPages.splice(i, 1);
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
	removeTwitchChannel: function(obj) {
		for(var i = 0, len = this.twitchchannels.length; i < len; ++i) {
			if (this.twitchchannels[i].id == obj.id) {
				console.log('twitchchannels removed: ' + obj.id);
				obj.onRemoved();
				this.twitchchannels.splice(i, 1);
				impresslist.refreshFilter();
				break;
			}
		}
	},
	removePodcast: function(obj) {
		for(var i = 0, len = this.podcasts.length; i < len; ++i) {
			if (this.podcasts[i].id == obj.id) {
				console.log('podcast removed: ' + obj.id);
				obj.onRemoved();
				this.podcasts.splice(i, 1);
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
	removePersonTwitchChannel: function(obj) {
		for(var i = 0, len = this.personTwitchChannels.length; i < len; ++i) {
			if (this.personTwitchChannels[i].id == obj.id) {
				console.log('person twitch channel removed: ' + obj.id);
				obj.onRemoved();
				this.personTwitchChannels.splice(i, 1);
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
	removeUser: function(obj) {
		for(var i = 0, len = this.users.length; i < len; ++i) {
			if (this.users[i].id == obj.id) {
				console.log('user removed: ' + obj.id);
				obj.onRemoved();
				this.users.splice(i, 1);
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

		console.error("impresslist: could not findPublicationById: " + id);
		return null;
	},
	findYoutuberByChannelId: function(channelId) {
		// var r = this.binarySearchByField(this.youtubers, 'youtubeId', channelId);
		// if (r != null) { return r; }
		for(var i = 0; i < this.youtubers.length; ++i) {
			if (this.youtubers[i].field('youtubeId') == channelId || this.youtubers[i].field('channel') == channelId) {
				return this.youtubers[i];
			}
		}

		console.log("impresslist: could not findYoutuberByChannelId: " + channelId);
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
	findTwitchChannelById: function(id) {
		/*for(var i = 0; i < this.youtubers.length; ++i) {
			if (this.youtubers[i].id == id) {
				return this.youtubers[i];
			}
		}*/
		var r = this.binarySearchById(this.twitchchannels, id);
		if (r != null) { return r; }

		console.log("impresslist: could not findTwitchChannelById: " + id);
		return null;
	},
	findPodcastById: function(id) {
		var r = this.binarySearchById(this.podcasts, id);
		if (r != null) { return r; }

		console.log("impresslist: could not findPodcastById: " + id);
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
	findOAuthFacebookAccountById: function(id) {
		var r = this.binarySearchById(this.oauthFacebookAccounts, id);
		if (r != null) { return r; }
		console.log("impresslist: could not findOAuthFacebookAccountById: " + id);
		return null;
	},
	findOAuthFacebookPageById: function(id) {
		var r = this.binarySearchById(this.oauthFacebookPages, id);
		if (r != null) { return r; }
		console.log("impresslist: could not findOAuthFacebookPageById: " + id);
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
	enabled: false,
	selectors: {
		text: "#jobs-textarea",
		button: "#jobs-save-all"
	},
	init: function(fromInit) {
		if (!this.enabled) { return; }
		$('#jobs-pill-link').show();

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
	enabled: false,
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
		if (!this.enabled) {
			return;
		}
		$('#chat-pill-link').show();

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
	cancellableKeys: [
		37, 38, 39, 40, // arrows
		16, // shift
		17, // ctrl
		18, // alt
		20, // capslock
		91 // cmd
	],
	formatnumber: function(v) {
		return new Number(v).toLocaleString();
	},
	iframe: function(url, title) {
		var height = Math.round(window.innerHeight * 0.8);
		var html = "";
		html += "	<div id='iframe_modal' class='modal fade' tabindex='-1' role='dialog'> \
						<div class='modal-dialog'> \
							<div class='modal-content' style='padding:5px;'> \
								<div style='min-height:" + height + "px;padding:0px;'>";
		html += "					<iframe id='iframe_modal_frame' src='" + url + "' style='width:100%; height:100%; min-height:" + height + "px; border:0;'></iframe>";
		html += "				</div> \
							</div> \
						</div> \
					</div>";
		$('#modals').html(html);

		$('#iframe_modal').modal('show');
	},
	findTagKey: function(tagName) {
		var inverseTags = [];
		for(var key in impresslist.config.tags) {
			inverseTags[impresslist.config.tags[key].name] = key;
		}
		return inverseTags[tagName];
	},
	tagStringToInputField: function(str) {
		var output = "";
		if (str.trim().length == 0) { return output; }

		var bits = str.split(",");
		console.log(bits);
		for(var i = 0; i < bits.length; i++) {
			output += impresslist.config.tags[bits[i]].name;
			if (i != bits.length - 1) {
				output += ",";
			}
		}
		return output;
	},
	tagInputFieldToString: function(data) {
		var array = [];
		if (typeof data === "string") {
			if (data.trim().length == 0) {
				return "";
			}
			array = data.split(",");
		} else {
			array = data;
		}

		var str = "";
		for(var i = 0; i < array.length; i++) {
			str += impresslist.util.findTagKey(array[i]);
			if (i != array.length - 1) {
				str += ",";
			}
		}
		return str;
	},
	buildTags: function(tagsString) {
		var html = "";
		if (tagsString.length == 0) { return "None"; }
		var tags = tagsString.split(",");
		for(var i = 0; i < tags.length; i++) {
			html += "<span class='tag tag-" + tags[i]+ "'>" + impresslist.config.tags[tags[i]].name + "</span>";
		}
		return html;
	},

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
	},
	templates: {
		StatsTable: function(stats) {
			var table = "";
			table += "	<table class='table'>\
							<thead class='thead-light'>\
								<th colspan='6'>Youtube</th>\
							</thead>\
							<thead class='thead-light'>\
								<th>Videos</th>\
								<th>Views</th>\
								<th>Likes</th>\
								<th>Dislikes</th>\
								<th>Favourites</th>\
								<th>Comments</th>\
							</thead>\
							<tr>\
								<td id='coverage-stats-youtube-videos'>" + impresslist.util.formatnumber(stats.youtube.videoCount) + "</td>\
								<td id='coverage-stats-youtube-views'>" +  impresslist.util.formatnumber(stats.youtube.viewCount) + "</td>\
								<td id='coverage-stats-youtube-likes'>" +  impresslist.util.formatnumber(stats.youtube.likeCount) + "</td>\
								<td id='coverage-stats-youtube-dislikes'>" + impresslist.util.formatnumber(stats.youtube.dislikeCount) + "</td>\
								<td id='coverage-stats-youtube-favourites'>" + impresslist.util.formatnumber(stats.youtube.favoriteCount) + "</td>\
								<td id='coverage-stats-youtube-comments'>" + impresslist.util.formatnumber(stats.youtube.commentCount) + "</td>\
							</tr>\
						</table>";
			return table;
		}
	}
}
impresslist.noop = function() {}
