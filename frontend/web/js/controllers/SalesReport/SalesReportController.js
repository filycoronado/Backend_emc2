app.controller('SalesReportController', ['$rootScope', '$scope', '$filter', '$routeParams', '$http', '$timeout', 'GlobalServices', function ($rootScope, $scope, $filter, $routeParams, $http, $timeout, GlobalServices) {
        $scope.Tikets = [];
        $scope.Users = [];
        $scope.Agencies = [];
        $scope.URL_REPORT = "na";

        $scope.sortingOrder = 'agente';
        $scope.reverse = false;
        $scope.itemsPerPage = 10;
        $scope.currentPage = 1;

        $http.post('index.php?r=api/agencies_by_owner', {idOwner: 1}).then(function success(response) {
            $scope.Agencies = response.data;
            console.log($scope.Agencies);
        });
        $http.post('index.php?r=api/users_by_agency').then(function success(response) {
            $scope.Users = response.data;
            //console.log($scope.Clients_Actives);
        });
        $scope.DoReport = function () {
            $scope.submitted = true;
            $scope.showError = false;
            $scope.error = {};
            if ($scope.type_memebership == 1) {
                $scope.type_memebership = "New Business";
            } else if ($scope.type_memebership == 2) {
                $scope.type_memebership = "Payment";
            } else if ($scope.type_memebership == 3) {
                $scope.type_memebership = "Re-Active";
            } else if ($scope.type_memebership == 4) {
                $scope.type_memebership = "Renew";
            }
            console.log($scope.f1);
            $http.post('index.php?r=api/get_tikets_by_date',
                    {user: $scope.users, plan: $scope.plan, agency: $scope.agency, typeTransaction: $scope.method_payment, typeMembership: $scope.type_memebership, f1: $scope.f1, f2: $scope.f2}).then(
                    function success(response) {
                        $scope.resetPage();
                        $scope.Tikets = response.data;
                        $scope.URL_REPORT = "../../../../new-system/common/pdf/temp_pdf/SalesReport.pdf";
                    }, function error(response) {

            }
            );
        };

        $scope.Test = function () {
            console.log("here");
            $http.post('index.php?r=api/salesreport',
                    {tickets: $scope.Tikets}).then(
                    function success(response) {

                        // window.open($scope.URL_REPORT, '_blank');
                        //location.href = response.data;

                        //$scope.resetPage();
                        //$scope.Tikets = response.data;
                    }, function error(response) {

            }
            );
        };

        $scope.getData = function () {
            return $filter('filter')($scope.Tikets, $scope.searchbox);
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
    }
]);