var app = angular.module('global.globalServices', []);

app.factory('GlobalServices', [ '$http', '$q', '$filter', function ( $http, $q, $filter) {
	
	var self = {
		activeSession: function(){
			var a = $q.defer();
			$http.get('../app/php/services/global/validateSession.php').then(function success(response){
				//console.log(data);
				a.resolve(response.dataAS);
			});
			return a.promise
		},
		getUser: function(){
			var u = $q.defer();
			$http.get('index.php?r=api/getuserdata').then(function success(response){
				//console.log(data);
				u.resolve(response.data);
			});
			return u.promise;
		},
		getUserAccess: function(){
			var r = {  };
			var a = $q.defer();
			$http.get('index.php?r=api/getuseraccess').then(function success(response){
				//console.log(dataUA);
				r = response.dataUA;
                
				a.resolve(r);
			});

			return a.promise;
		}
	};
	return self;
}])