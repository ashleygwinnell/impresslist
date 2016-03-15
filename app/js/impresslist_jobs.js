
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