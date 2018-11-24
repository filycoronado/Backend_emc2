app.controller('Details', ['$rootScope', '$scope', '$filter', '$routeParams', '$http', '$timeout', 'GlobalServices', function ($rootScope, $scope, $filter, $routeParams, $http, $timeout, GlobalServices) {

        $scope.reseller = {};

        $scope.id_reseller = $routeParams.id;

        $http.get('index.php?r=api/get_reseller').then(function success(response) {
            $scope.reseller = response.data;

        });



    }]);