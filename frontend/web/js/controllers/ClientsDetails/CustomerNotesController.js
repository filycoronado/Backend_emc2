app.controller('CustomerNotesController', ['$rootScope', '$scope', '$filter', '$routeParams', '$http', '$timeout', 'GlobalServices', function ($rootScope, $scope, $filter, $routeParams, $http, $timeout, GlobalServices) {
        $scope.client = {};
        $scope.Notes = [];
        $scope.id_client = $routeParams.id;
        $scope.payments = [];

        $scope.sortingOrder = 'date';
        $scope.reverse = true;
        $scope.itemsPerPage = 10;
        $scope.currentPage = 1;

        $http.post('index.php?r=api/get_client_by_id', {id_client: $scope.id_client}).then(function success(response) {
            $scope.client = response.data;
        });

        $http.post('index.php?r=api/get_payhistory', {id_client: $scope.id_client}).then(function success(response) {
            $scope.payments = response.data;
        });

        $http.post('index.php?r=api/get_all_notes', {id_client: $scope.id_client}).then(function success(response) {
            $scope.Notes = response.data;
        });

        $scope.getData = function () {
            return $filter('filter')($scope.Notes, $scope.searchbox);
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
            if ($scope.getData().length == 0 || $('#payments-table>tbody>tr').length == 0)
                return 0;
            else
                return ($scope.currentPage - 1) * $scope.itemsPerPage + 1;
        };

        $scope.displayingEnd = function () {
            return ($scope.currentPage - 1) * $scope.itemsPerPage + $('#payments-table>tbody>tr').length;
        };
    }
]);