angular.module("App.Api")

.factory("ApiResponseTransformer", function() {
	return function(data) {
		return JSON.parse(data);
	}
});