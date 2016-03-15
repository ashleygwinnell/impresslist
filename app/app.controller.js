angular.module("App")

.controller("AppController", ['$scope', '$compile', 'App', function($scope, $compile, App) {

	$scope.App = App;

}]);

/*
.directive("dumbPassword", function() {
	var validElement = angular.element("<div>{{model.input}}</div>");

	this.link = function(scope) {
		scope.$watch("model.input", function (value) {
			if (value === "password") {
				validElement.toggleClass("alert-box alert");
			}
		});
	}

	return {
		restrict: "E",
		replace: true,
		template: "	<div> \
						<input type='text' ng-model='model.input'> \
					</div>",
		compile: function(templateElement) {
			templateElement.append(validElement);
			return link;
		}
	}
})


.directive("templateFromUrl", function() {
	var validElement = angular.element("<div>{{model.input}}</div>");

	this.link = function(scope) {
		scope.$watch("model.input", function (value) {
			if (value === "password") {
				validElement.toggleClass("alert-box alert");
			}
		});
	}

	return {
		restrict: "E",
		replace: true,
		scope: {},
		templateUrl: "testTemplate.html",
		compile: function(templateElement) {
			templateElement.append(validElement);
			return link;
		}
	}
})



.directive("country", function() {
	return {
		restrict: "E",
		controller: function() {
			this.makeAnnouncement = function(message) {
				console.log("Country says: " + message);
			}
		}
	}
})
.directive("city", function() {
	return {
		restrict: "E",
		require: "^country",
		link: function(scope, element, attrs, countryController) {
			countryController.makeAnnouncement("Do things!");
		}
	}
});

function deferExamples() {
	var defer = $q.defer();
	defer.promise.then(function() {

	});
	defer.resolve();

	// requests
	$http.get("backend/api.php?endpoint=/test/test/")
		.success(function (data) {
			console.log(data);
		});

	$http.post("/", {})
		.success(function(data){
			//console.log(data);
		})

	// promises
	var one = $q.defer();
	var two = $q.defer();
	var thr = $q.defer();

	var success = function(data) {
		console.log(data);
	}
	var all = $q.all([one.promise, two.promise, thr.promise]);
	all.then(success);

	one.promise.then(success);
	two.promise.then(success);
	thr.promise.then(success);

	$timeout(function() {
		one.resolve("one done");
	}, Math.random() * 1000);

	$timeout(function() {
		two.resolve("two done");
	}, Math.random() * 1000);

	$timeout(function() {
		thr.resolve("thr done");
	}, Math.random() * 1000);
}*/