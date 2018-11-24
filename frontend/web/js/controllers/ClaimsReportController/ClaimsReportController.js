app.controller('ClaimsReportController', ['$rootScope', '$scope', '$filter', '$routeParams', '$http', '$timeout', 'GlobalServices', function ($rootScope, $scope, $filter, $routeParams, $http, $timeout, GlobalServices) {
        $scope.claims = [];
        $scope.URL_REPORT = "";
        $scope.sortingOrder = 'fecha';
        $scope.reverse = true;
        $scope.itemsPerPage = 10;
        $scope.currentPage = 1;

        $scope.DoReport = function () {
            $scope.submitted = true;
            $scope.showError = false;
            $scope.error = {};

            $http.post('index.php?r=api/get_claims',
                    {type_claim: $scope.type_claim, status_claim: $scope.status_claim, paid: $scope.paid, f1: $scope.f1, f2: $scope.f2}).then(
                    function success(response) {
                        $scope.resetPage();
                        $scope.URL_REPORT = "../../../../adc/common/pdf/temp_pdf/ClaimsReport.pdf";
                        $scope.claims = response.data;
                        //cosnole.log($scope.claims);
                    }, function error(response) {

            }
            );
        };

        $scope.GetReport = function () {
            $http.post('index.php?r=api/claimpdf',
                    {type_claim: $scope.type_claim, status_claim: $scope.status_claim, paid: $scope.paid, f1: $scope.f1, f2: $scope.f2}).then(
                    function success(response) {
                        window.open('?r=api/claimpdf', '_blank');
                        //cosnole.log($scope.claims);
                    }, function error(response) {

            }
            );
        };

        $scope.getData = function () {
            return $filter('filter')($scope.claims, $scope.searchbox);
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