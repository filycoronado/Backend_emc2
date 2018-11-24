app.controller('StatusReportController', ['$rootScope', '$scope', '$filter', '$routeParams', '$http', '$timeout', 'GlobalServices', function ($rootScope, $scope, $filter, $routeParams, $http, $timeout, GlobalServices) {


        $scope.URL_REPORT = "na";
        $scope.clients = [];
        $scope.sortingOrder = 'agente';
        $scope.reverse = false;
        $scope.itemsPerPage = 10;
        $scope.currentPage = 1;
        $scope.StatusSel = "";
        $scope.label = 0;



        $scope.DoReport = function () {
            $scope.submitted = true;



            $http.post('index.php?r=api/get_clients_by_status',
                    {status: $scope.StatusSel}).then(
                    function success(response) {
                        $scope.resetPage();
                        $scope.clients = response.data;
                        $scope.label = $scope.StatusSel;

                    }, function error(response) {

            }
            );
        };



        $scope.getData = function () {
            return $filter('filter')($scope.clients, $scope.searchbox);
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
        $scope.Grandtotal = 0;

        $scope.getTotal = function () {

            $scope.Grandtotal = 0;
            for (var i = 0; i < $scope.clients.length; i++) {

                //console.log(Tikets);
                $scope.Grandtotal += parseFloat($scope.clients[i].total);
            }

        };
    }
]);