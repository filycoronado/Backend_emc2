app.controller('RegisterController', ['$scope', '$http', '$window', '$location',
    function($scope, $http, $window, $location) {
        $scope.showError = false;

        $scope.register = function () {
            $scope.submitted = true;
            $scope.showError = false;
            $scope.error = {};
            $http.post('index.php?r=api/register', $scope.userModel).then(
                function success(response) {
                    if (response.data.status === 'success') {
                        swal({
                          title: 'Signgup succesfull!',
                          text: "You will be able to login after your data has been reviewed!",
                          type: 'success',
                          showCancelButton: false,
                          confirmButtonColor: '#3085d6',
                          cancelButtonColor: '#d33',
                          confirmButtonText: 'Ok'
                        }).then((result) => {
                            location.href = '#/reseller/singin';
                        });
                        
                    } else {
                        swal({
                            type: 'error',
                            title: 'Oops...',
                            text: 'Something went wrong!',
                        });
                    }
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