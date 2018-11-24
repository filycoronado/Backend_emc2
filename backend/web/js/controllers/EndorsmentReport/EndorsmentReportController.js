app.controller('EndorsmentReportController', ['$rootScope', '$scope', '$filter', '$routeParams', '$http', '$timeout', 'GlobalServices', function ($rootScope, $scope, $filter, $routeParams, $http, $timeout, GlobalServices) {
        $scope.logs = [];

        $scope.sortingOrder = 'date';
        $scope.reverse = true;
        $scope.itemsPerPage = 10;
        $scope.currentPage = 1;

        $http.get('index.php?r=api/get_change_log').then(function success(response) {
            $scope.logs = response.data;
            //console.log($scope.logs);
        });

        $scope.getData = function () {
            return $filter('filter')($scope.logs, $scope.searchbox);
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
            if ($scope.getData().length == 0 || $('#report-table>tbody>tr').length == 0)
                return 0;
            else
                return ($scope.currentPage - 1) * $scope.itemsPerPage + 1;
        };

        $scope.displayingEnd = function () {
            return ($scope.currentPage - 1) * $scope.itemsPerPage + $('#report-table>tbody>tr').length;
        };


        $scope.CompareDate = function (date) {
            var date = new Date(date).toISOString().substring(0, 10);
            return date;




        };
    }
]);