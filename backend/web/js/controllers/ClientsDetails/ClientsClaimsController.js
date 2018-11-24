app.controller('ClientsClaimsController', ['$rootScope', '$scope', '$filter', '$routeParams', '$http', '$timeout',  'GlobalServices',  function ($rootScope, $scope, $filter, $routeParams, $http, $timeout, GlobalServices) {
        $scope.client = {};
        $scope.id_client = $routeParams.id;
        $scope.claims = [];

        $scope.sortingOrder = 'fecha';
        $scope.reverse = true;
        $scope.itemsPerPage = 10;
        $scope.currentPage = 1;

        $http.post('index.php?r=api/get_client_by_id', {id_client: $scope.id_client}).then(function success(response) {
            $scope.client = response.data;
        });

        $http.post('index.php?r=api/get_claims_by_id', {id_client: $scope.id_client}).then(function success(response) {
            $scope.claims = response.data;
            console.log($scope.claims);
        });

        $scope.getData = function () {
        return $filter('filter')($scope.claims, $scope.searchbox);
        }
        
        $scope.numberOfPages=function(){
            return Math.ceil($scope.getData().length/$scope.itemsPerPage);
        }

        $scope.resetPage = function () {
            $scope.currentPage = 1;
        };

        // change sorting order
        $scope.sort_by = function(newSortingOrder) {
            if ($scope.sortingOrder == newSortingOrder)
                $scope.reverse = !$scope.reverse;

            $scope.sortingOrder = newSortingOrder;
        };

        $scope.displayingStart = function() {
            if($scope.getData().length == 0 || $('#claims-table>tbody>tr').length == 0)
                return 0;
            else 
                return ($scope.currentPage-1)*$scope.itemsPerPage + 1;
        };

        $scope.displayingEnd = function() {
            return ($scope.currentPage-1)*$scope.itemsPerPage + $('#claims-table>tbody>tr').length;
        };
    }
]);