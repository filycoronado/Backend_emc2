app.controller('DashboardController', ['$rootScope', '$scope', '$filter', '$routeParams', '$http', '$timeout', 'GlobalServices', function ($rootScope, $scope, $filter, $routeParams, $http, $timeout, GlobalServices) {

        $scope.Clients_Actives = [];

        $scope.sortingOrder = 'id';
        $scope.reverse = false;
        $scope.itemsPerPage = 10;
        $scope.currentPage = 1;
  
        $http.get('index.php?r=api/get_clients_by_agency_status').then(function success(response) {
            $scope.Clients_Actives = response.data;
           
        });

        $scope.getData = function () {
            return $filter('filter')($scope.Clients_Actives, $scope.searchbox);
        }

        $scope.numberOfPages = function () {
            return Math.ceil($scope.getData().length / $scope.itemsPerPage);
        }

        $scope.resetPage = function () {
            $scope.currentPage = 1;
        };

        // change sorting order
        $scope.sort_by = function (newSortingOrder) {
            if ($scope.sortingOrder == newSortingOrder)
                $scope.reverse = !$scope.reverse;

            $scope.sortingOrder = newSortingOrder;
        };

        $scope.displayingStart = function () {
            if ($scope.getData().length == 0 || $('#clients-table>tbody>tr').length == 0)
                return 0;
            else
                return ($scope.currentPage - 1) * $scope.itemsPerPage + 1;
        };

        $scope.displayingEnd = function () {
            return ($scope.currentPage - 1) * $scope.itemsPerPage + $('#clients-table>tbody>tr').length;
        };

    }]);