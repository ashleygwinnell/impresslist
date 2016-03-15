angular.module("App.Model")

.factory("Model", ['$rootScope', '$state', function($rootScope, $state) {

	var Model = {};
	Model.create = function(constructor) {

		constructor.prototype.initPriorities = function(field) {
			// Priorities:
			// 1=3,2=1
			//console.log(this.field('priorities'));
			var txt = this.field(field);
			if (typeof txt == 'undefined' || txt.length == 0) {
				this['priority_' + $rootScope.App.user.game] = 0;
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
		constructor.prototype.priority = function() {
			var ret = this['priority_' + $rootScope.App.user.game];
			if (typeof ret == 'undefined') {
				return 0;
			}
			return ret;
		}

		constructor.prototype.onAdded = function() {

		}
		constructor.prototype.onRemoved = function() {

		}
		constructor.prototype.field = function(f) {
			var r = this[f];
			if (r == null) { console.error("field " + f + " was null."); console.error(this.fields); return ""; }
			return r;
		}

		constructor.populateKeyed = function(rows, key) {
			var models = {};
			if ( rows && angular.isArray( rows ) && rows.length ) {
				for ( var i = 0; i < rows.length; ++i ) {
					models[rows[i][key]] = new constructor( rows[i] );
				}
			} else if (rows && angular.isObject(rows) ) {
				models[rows[key]] = new constructor( rows );
			}
			return models;
		}
		constructor.populate = function(rows) {
			var models = [];
			if ( rows && angular.isArray( rows ) && rows.length ) {
				for ( var i = 0; i < rows.length; ++i ) {
					models.push( new constructor( rows[i] ) );
				}
			} else if (rows && angular.isObject(rows) ) {
				models.push( new constructor( rows ) )
			}
			return models;
		}
		return constructor;
	}

	return Model;

}]);