var app = angular.module('app', ['ngRoute', 'mgcrea.ngStrap','ui.utils.masks','ui.mask','global.globalServices','ui.bootstrap','global.globalServices']);

app.controller('MainController', ['$scope', '$http', '$location', '$window', 'GlobalServices',
    function ($scope, $http, $location, $window, GlobalServices) {
        $scope.fp_loading = false;
        $scope.userData = {};
        $scope.userAccess = {};

        GlobalServices.getUser().then(function(userData){
           // console.log(userData)
            $scope.userData = userData;
        });

        GlobalServices.getUserAccess().then(function(userAccess){
            $scope.userAccess = userAccess;
        });

        $scope.loggedIn = function() {
            return Boolean($window.localStorage.access_token);
        };

        $scope.logout = function () {
            $http.post('index.php?r=api/logout', $scope.userModel).then(
                function success(response) {

                    delete $window.localStorage.access_token;
                    $location.path('/reseller/singin').replace();
                    $window.location.reload();
                }, function error(response){
                    
                }
            );
            
        };
    }
]);

app.factory('authInterceptor', function ($q, $window, $location) {
    return {
        request: function (config) {
            if ($window.localStorage.access_token) {
                //HttpBearerAuth
                config.headers.Authorization = 'Bearer ' + $window.localStorage.access_token;
            }
            return config;
        },
        responseError: function (rejection) {
            if (rejection.status === 401) {
                $location.path('/reseller/singin').replace();
            }
            return $q.reject(rejection);
        }
    };
});

app.factory('httpInterceptor', function ($q, $window, $location) {
    return {
        request: function (config) {
            //console.log(config);
            config.params = config.params || {};
            if (    $('meta[name="csrf-token"]').attr("content") 
                    && !config.url.includes('.tpl.html') 
                    && !config.url.includes('uib/template/pagination/pagination.html') ) {
                config.params.csrfToken = $('meta[name="csrf-token"]').attr("content");;
            }
            return config || $q.when(config);
        },
        responseError: function (rejection) {
            if (rejection.status === 500) {
                swal({
                  type: 'error',
                  title: 'Oops...',
                  text: 'Something went wrong!',
                  //footer: '<a href>Why do I have this issue?</a>'
                });
            }else if(rejection.status === 404){
                //$location.path('/404').replace();
            }
            return $q.reject(rejection);
        }
    };
});
//We already have a limitTo filter built-in to angular,
//let's make a startFrom filter
app.filter('startFrom', function() {
    return function(input, start) {
        if (!input || !input.length) { return; }
        start = +start; //parse to int
        return input.slice(start);
    }
});

app.filter('tel', function () {
    return function (tel) {
        if (!tel) { return ''; }

        var value = tel.toString().trim().replace(/^\+/, '');

        if (value.match(/[^0-9]/)) {
            return tel;
        }

        var country, city, number;

        switch (value.length) {
            case 10: // +1PPP####### -> C (PPP) ###-####
                country = 1;
                city = value.slice(0, 3);
                number = value.slice(3);
                break;

            case 11: // +CPPP####### -> CCC (PP) ###-####
                country = value[0];
                city = value.slice(1, 4);
                number = value.slice(4);
                break;

            case 12: // +CCCPP####### -> CCC (PP) ###-####
                country = value.slice(0, 3);
                city = value.slice(3, 5);
                number = value.slice(5);
                break;

            default:
                return tel;
        }

        if (country == 1) {
            country = "";
        }

        number = number.slice(0, 3) + '-' + number.slice(3);

        return (country + " (" + city + ") " + number).trim();
    };
});
