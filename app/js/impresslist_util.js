impresslist = (typeof impresslist != 'undefined')?impresslist:{};
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
