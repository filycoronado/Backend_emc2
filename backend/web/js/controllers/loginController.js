app.controller('LoginController', ['$scope', '$http', '$window', '$location',
    function($scope, $http, $window, $location) {
        $scope.showError = false;

        $scope.login = function () {
            $scope.submitted = true;
            $scope.showError = false;
            $scope.error = {};
            $http.post('index.php?r=api/login', $scope.userModel).then(
                function success(response) {

                    $window.localStorage.access_token = response.data.access_token;
                    $location.path('/dashboard').replace();
                    $window.location.reload();
                }, function error(response){
                    angular.forEach(response.data, function (error) {
                        //console.log(' - ' + error);
                        $scope.showError = true;
                        $scope.error[error.field] = error.message;
                    });
                }
            );

        };

    }
]);