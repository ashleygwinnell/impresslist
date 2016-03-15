angular.module("App.Api")

.service("Api", ['$http', '$q', 'ApiResponseTransformer', function($http, $q, ApiResponseTransformer) {

	this.request = function(endpoint, data) {
		console.log("Api.request - " + endpoint);
		if (data) { console.log(data); }

		var deferred = $q.defer();
		$http({
			method: "POST",
			url: "backend/api.php?endpoint=" + endpoint,
			transformResponse: ApiResponseTransformer,
			data: data 
		})
			.success(function(data){
				deferred.resolve(data);
			});
		return deferred.promise;
	}

}]);