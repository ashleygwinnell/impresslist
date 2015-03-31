API = function() {

}
API.prototype = {

}
API.addPerson = function() {
	var name = "Blank";
	var email = "blank@blank.com";
	var twitter = "";
	var notes = "";
	var url = "api.php?endpoint=/person/add/&name=" + encodeURIComponent(name) + "&email=" + encodeURIComponent(email) + "&twitter=" + encodeURIComponent(twitter) + "&notes=" + encodeURIComponent(notes);
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

			var person = new Person(json.person);
			impresslist.addPerson(person, false);
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
API.savePerson = function(person, name, email, twitter, notes) {

	var url = "api.php?endpoint=/person/save/" + 
					"&id=" + encodeURIComponent(person.id) + 
					"&name=" + encodeURIComponent(name) + 
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
			impresslist.removePublication(publication);
		})
		.fail(function() {
			API.errorMessage("Could not change Password.");
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
		return this.fields[f];
	}


Email = function(data) {
	DBO.call(this, data); 
}
	Email.prototype = Object.create(DBO.prototype)
	Email.prototype.constructor = Email;
	Email.prototype.init = function(data) {
		DBO.prototype.init.call(this, data);
		this.id = this.field('id');
		//this.user_id = this.field('user_id');
		//this.person_id = this.field('person_id');
	}

Game = function(data) {
	DBO.call(this, data); 
}
	Game.prototype = Object.create(DBO.prototype)
	Game.prototype.constructor = Email;
	Game.prototype.init = function(data) {
		DBO.prototype.init.call(this, data);
		this.id = this.field('id');
		this.name = this.field('name');
		
	}



User = function(data) {
	DBO.call(this, data); 
}
	User.prototype = Object.create(DBO.prototype)
	User.prototype.constructor = User;
	User.prototype.init = function(data) {
		DBO.prototype.init.call(this, data);
		this.id = this.field('id');
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


Youtuber = function(data) {
	DBO.call(this, data); 
}
	Youtuber.prototype = Object.create(DBO.prototype);
	Youtuber.prototype.constructor = Youtuber;
	Youtuber.prototype.init = function(data) {
		DBO.prototype.init.call(this, data);
		this.id = this.field('id');
		this.name = this.field('name');

		this.initPriorities('priorities');
	}

	Youtuber.prototype.createTableRow = function() {
		var html = "	<tr data-youtuber-id='" + this.field('id') + "' data-youtuber-tablerow='true' class='table-list' data-toggle='modal' data-target='.youtuber_modal'> \
							<td data-youtuber-id='" + this.field('id') + "' data-field='name' data-value='" + this.field('name') + "'>" + this.field('name') + "</td> \
							<td data-youtuber-id='" + this.field('id') + "' data-field='priority' data-value='" + this.priority() + "'>" + Priority.name(this.priority()) + "</td> \
							<td data-youtuber-id='" + this.field('id') + "' data-field='subscribers' data-value='" + this.field('subscribers') + "'><a href='http://youtube.com/user/" + this.field('channel') + "' target='new'>" + new Number(this.field('subscribers')).toLocaleString() + "</a></td> \
							<td data-youtuber-id='" + this.field('id') + "' data-field='views' data-value='" + this.field('views') + "'>" + new Number(this.field('views')).toLocaleString() + "</td> \
							<td data-youtuber-id='" + this.field('id') + "' data-field='twitter_followers' data-value='" + this.field('twitter_followers') + "'><a href='http://twitter.com/" + this.field('twitter') + "' target='new'>" + new Number(this.field('twitter_followers')).toLocaleString() + "</a></td> \
							<td data-youtuber-id='" + this.field('id') + "' data-field='lastpostedon' data-value='" + this.field('lastpostedon') + "'>" + impresslist.util.relativetime_contact(this.field('lastpostedon')) + "</td> \
						</tr>";
		$('#youtubers').append(html);
		
		var youtuber = this;
		$("#youtubers [data-youtuber-tablerow='true'][data-youtuber-id='" + this.id + "']").click(function() {
			youtuber.open();
		});
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
		$("[data-youtuber-id='" + this.id + "'][data-field='twitter_followers']").html("<a href='http://twitter.com/" + this.field('twitter') + "' target='new'>" + new Number(this.field('twitter_followers')).toLocaleString() + "</a>");			
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

		ret = this.field('notes').toLowerCase().indexOf(text) != -1;
		if (ret) { return ret; }

		ret = this.field('description').toLowerCase().indexOf(text) != -1;
		if (ret) { return ret; }
		
		ret = this.field('twitter').toLowerCase().indexOf(text) != -1;
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
		this.id = this.fields['id'];
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






Person = function(data) {
	DBO.call(this, data); 
}
	Person.prototype = Object.create(DBO.prototype);
	Person.prototype.constructor = Person;
	Person.prototype.init = function(data) {
		DBO.prototype.init.call(this, data);

		this.id = this.field('id');
		this.name = this.field('name');
		//this.publications = [];

		this.initPriorities('priorities');
		
	}
	Person.prototype.update = function() {
		$("[data-person-id='" + this.id + "'][data-field='name']").html(this.name);
		$("[data-person-id='" + this.id + "'][data-field='priority']").html(Priority.name(this.priority()));
		$("[data-person-id='" + this.id + "'][data-field='twitter_followers']").html("<a href='http://twitter.com/" + this.field('twitter') + "' target='new'>" + new Number(this.field('twitter_followers')).toLocaleString() + "</a>");		
	},
	Person.prototype.save = function() {
		var name = $("[data-person-id=" + this.id + "][data-input-field='name']").val();
		var email = $("[data-person-id=" + this.id + "][data-input-field='email']").val();
		var twitter = $("[data-person-id=" + this.id + "][data-input-field='twitter']").val();
		var notes = $("[data-person-id=" + this.id + "][data-input-field='notes']").val();
		
		API.savePerson(this, name, email, twitter, notes);
	}
	Person.prototype.savePriority = function() {
		var priority = $("[data-person-id='" + this.id + "'][data-input-field='priority']").val();
		API.setPersonPriority(this, priority, impresslist.config.user.game);
	}
	Person.prototype.saveUserAssignment = function() {
		var assignment = $("[data-person-id='" + this.id + "'][data-input-field='assignment']").val();
		API.setPersonAssignment(this, assignment);
	}
	
	Person.prototype.open = function() {

		var html = "<div class='modal fade person_modal' tabindex='-1' role='dialog'> \
						<div class='modal-dialog'> \
							<div class='modal-content' style='padding:5px;'>";
				
		html += "				<div style='min-height:100px;padding:20px;'>";
		
		html += "	<div style='min-height:45px;'> \
						<h3 class='fl' data-person-id='" + this.id + "' data-field='name'>" + this.field('name') + "</h3> \
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
							<li role="presentation"><a role="tab" href="#" data-tab="person_messages" data-toggle="tab">Messages</a></li> \
						</ul> \
						<div class="tab-content">';

				// Personal profile panel.
				html += "	<div role='tabpanel' class='tab-pane active pady' data-tab='person_profile'>\
								<form role='form' class='oa' onsubmit='return false;'>	\
									<div class='row'>\
										<div class='form-group col-md-6'>\
											<label for='name'>Name:&nbsp; </label> \
											<input data-person-id='" + this.id + "' data-input-field='name' class='form-control' type='text' value='" + this.field('name') + "' />\
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
	Person.prototype.createTableRow = function() {
		var lastcontactedbytemp = this.field('lastcontactedby');
		var lastcontactedbystring = "";
		if (lastcontactedbytemp > 0) {
			var lastcontactedbyuser = impresslist.findUserById(lastcontactedbytemp);
			lastcontactedbystring = "by <span style='color:" + lastcontactedbyuser.field('color') + "'>" + lastcontactedbyuser.field('forename') + "</span>";
		}

		var html = "	<tr data-person-id='" + this.field('id') + "' data-person-tablerow='true' class='table-list' data-toggle='modal' data-target='.person_modal'> \
							<!-- <td data-person-id='" + this.field('id') + "' data-field='id' data-value='" + this.field('id') + "'>" + this.field('id') + "</td> -->\
							<td data-person-id='" + this.field('id') + "' data-field='name' data-value='" + this.field('name') + "'>" + this.field('name') + "</td> \
							<td data-person-id='" + this.field('id') + "' data-field='priority' data-value='" + this.priority() + "'>" + Priority.name(this.priority()) + "</td> \
							<td data-person-id='" + this.field('id') + "' data-field='twitter_followers' data-value='" + this.field('twitter_followers') + "'><a href='http://twitter.com/" + this.field('twitter') + "' target='new'>" + new Number(this.field('twitter_followers')).toLocaleString() + "</a></td> \
							<td data-person-id='" + this.field('id') + "' data-field='last_contacted' data-value='" + this.field('lastcontacted') + "'>" + impresslist.util.relativetime_contact(this.field('lastcontacted')) + " " + lastcontactedbystring + "</td> \
						</tr>";
		$('#people').append(html);
		
		var person = this;
		$("#people [data-person-tablerow='true'][data-person-id='" + this.id + "']").click(function() {
			person.open();
		});
	},
	Person.prototype.removeTableRow = function() {
		$("#people [data-person-tablerow='true'][data-person-id='" + this.id + "']").remove();

	}

	Person.prototype.filter = function(text) {
		var element = $("#people [data-person-tablerow='true'][data-person-id='" + this.id + "']");
		if (this.search(text) && this.filter_isContactedByMe() && this.filter_isRecentlyContacted() && this.filter_isHighPriority() && this.filter_hasEmail() && this.filter_isAssignedToMe()) {
			element.show();
			return true;
		} else {
			element.hide();
			return false;
		}
	}
	Person.prototype.filter_isRecentlyContacted = function() {
		if ($('#filter-recent-contact').is(':checked')) { 
			var contactedRecently = false;
			for(var i = 0; i < impresslist.emails.length; ++i) {
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
			for(var i = 0; i < impresslist.emails.length; ++i) {
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
			for(var i = 0; i < impresslist.personPublications.length; ++i) {
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
	Person.prototype.filter_hasEmail = function() {
		if ($('#filter-email-attached').is(':checked')) { 
			if (this.field('email').length > 0) {
				return true;
			}
			for(var i = 0; i < impresslist.personPublications.length; ++i) {
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

		ret = this.name.toLowerCase().indexOf(text) != -1;
		if (ret) { return ret; }

		ret = this.field('notes').toLowerCase().indexOf(text) != -1;
		if (ret) { return ret; }

		ret = this.field('email').toLowerCase().indexOf(text) != -1;
		if (ret) { return ret; }

		// search all publications too.
		for(var i = 0; i < impresslist.personPublications.length; ++i) {
			if (impresslist.personPublications[i].field('person') == this.id) {
				var pub = impresslist.findPublicationById( impresslist.personPublications[i].field('publication') );
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
		this.id = this.fields['id'];
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
		$("[data-publication-id='" + this.id + "'][data-field='twitter_followers']").html("<a href='http://twitter.com/" + this.field('twitter') + "' target='new'>" + new Number(this.field('twitter_followers')).toLocaleString() + "</a>");		
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
							<td data-publication-id='" + this.field('id') + "' data-field='twitter_followers' 	data-value='" + this.field('twitter_followers')+ "'><a href='http://twitter.com/" + this.field('twitter') + "' target='new'>" + new Number(this.field('twitter_followers')).toLocaleString() + "</a></td> \
							<td data-publication-id='" + this.field('id') + "' data-field='lastpostedon' 		data-value='" + this.field('lastpostedon') + "'>" + impresslist.util.relativetime_contact(this.field('lastpostedon')) + "</td> \
						</tr>";
		$('#publications').append(html);

		var publication = this;
		$("#publications [data-publication-tablerow='true'][data-publication-id='" + this.id + "']").click(function() {
			publication.open();
		});
	}
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

		ret = this.field('notes').toLowerCase().indexOf(text) != -1;
		if (ret) { return ret; }
		
		ret = this.field('url').toLowerCase().indexOf(text) != -1;
		if (ret) { return ret; }

		ret = this.field('twitter').toLowerCase().indexOf(text) != -1;
		if (ret) { return ret; }

		return false;
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
		}
	},
	people: [],
	publications: [],
	personPublications: [],
	youtubers: [],
	emails: [],
	users: [],
	games: [],
	init: function() {
		// Navigation links
		var thiz = this;
		$('#nav-add-person').click(API.addPerson);
		$('#nav-add-publication').click(API.addPublication);
		$('#nav-add-youtuber').click(API.addYoutuber);
		$('#nav-user-changepassword').click(function() { thiz.findUserById(thiz.config.user.id).openChangePassword(); });
		$('#nav-admin').click(this.changePage);
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


		// Chat functionality
		this.chat.init();
		
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

		if (countPeopleVisible == 0) { $('#people-footer').show(); } else { $('#people-footer').hide(); }
		if (countPublicationsVisible == 0) { $('#publications-footer').show(); } else { $('#publications-footer').hide(); }
		if (countYoutubeChannelsVisible == 0) { $('#youtubers-footer').show(); } else { $('#youtubers-footer').hide(); }

		if (impresslist.people.length > 0 && countPeopleVisible == 0) { $('#people-container').hide(); } else { $('#people-container').show(); }
		if (impresslist.publications.length > 0 && countPublicationsVisible == 0) { $('#publications-container').hide(); } else { $('#publications-container').show(); }
		if (impresslist.youtubers.length > 0 && countYoutubeChannelsVisible == 0) { $('#youtubers-container').hide(); } else { $('#youtubers-container').show(); }

		$('#people-count').html("(" + countPeopleVisible + ")");
		$('#publication-count').html("(" + countPublicationsVisible + ")");
		$('#youtuber-count').html("(" + countYoutubeChannelsVisible + ")");

		if (text.length > 0) { $('#chat-container').hide(); } else { $('#chat-container').show(); }

	},
	
	addPerson: function(obj, fromInit) {
		obj.onAdded();
		this.people.push(obj);
		if (!fromInit) { impresslist.refreshFilter(); }
	},
	addPublication: function(obj, fromInit) {
		obj.onAdded();
		this.publications.push(obj);
		if (!fromInit) { impresslist.refreshFilter(); }
	},
	addPersonPublication: function(obj, fromInit) {
		obj.onAdded(fromInit);
		this.personPublications.push(obj);
		if (!fromInit) { impresslist.refreshFilter(); }
	},
	addEmail: function(obj, fromInit) {
		obj.onAdded();
		this.emails.push(obj);
		if (!fromInit) { impresslist.refreshFilter(); }
	},
	addUser: function(obj, fromInit) {
		obj.onAdded();
		this.users.push(obj);
		if (!fromInit) { impresslist.refreshFilter(); }
	},
	addYoutuber: function(obj, fromInit) {
		obj.onAdded();
		this.youtubers.push(obj);
		if (!fromInit) { impresslist.refreshFilter(); }
	},
	addGame: function(obj, fromInit) {
		obj.onAdded();
		this.games.push(obj);
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
	findPersonById: function(id) {
		for(var i = 0; i < this.people.length; ++i) {
			if (this.people[i].id == id) {
				return this.people[i];
			}
		}
		console.log("impresslist: could not findPersonById: " + id);
		return null;
	},
	findPublicationById: function(id) {
		for(var i = 0; i < this.publications.length; ++i) {
			if (this.publications[i].id == id) {
				return this.publications[i];
			}
		}
		console.log("impresslist: could not findPublicationById: " + id);
		return null;
	},
	findYoutuberById: function(id) {
		for(var i = 0; i < this.youtubers.length; ++i) {
			if (this.youtubers[i].id == id) {
				return this.youtubers[i];
			}
		}
		console.log("impresslist: could not findYoutuberById: " + id);
		return null;
	},
	findUserById: function(id) {
		for(var i = 0; i < this.users.length; ++i) {
			if (this.users[i].id == id) {
				return this.users[i];
			}
		}
		console.log("impresslist: could not findUserById: " + id);
		return null;
	},
	findEmailById: function(id) {
		for(var i = 0; i < this.emails.length; ++i) {
			if (this.emails[i].id == id) {
				return this.emails[i];
			}
		}
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
	backup: function() {

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

		if (elapsed < msPerMinute) {
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
