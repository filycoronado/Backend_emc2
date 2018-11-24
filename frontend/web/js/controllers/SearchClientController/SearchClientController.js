app.controller('SearchClientController', ['$rootScope', '$scope', '$filter', '$routeParams', '$http', '$timeout', 'GlobalServices', function ($rootScope, $scope, $filter, $routeParams, $http, $timeout, $GlobalServices) {

        $scope.Clients = [];
        var today = new Date("").getDate() + 7;
//        var dd = today.getDate();
//        var mm = today.getMonth() + 1; //January is 0!
//        var yyyy = today.getFullYear();
//
//        if (dd < 10) {
//            dd = '0' + dd
//        }
//
//        if (mm < 10) {
//            mm = '0' + mm
//        }

        //  today = yyyy + '-' + mm + '-' + dd;

        $scope.currentDate = "";
        $scope.date = new Date();

        $scope.sortingOrder = 'id';
        $scope.reverse = false;
        $scope.itemsPerPage = 10;
        $scope.currentPage = 1;

        $scope.searchboxID = "";
        $scope.searchboxName = "";
        $scope.searchboxLastName = "";

        $scope.formatDate = function (date) {
            var dateOut = new Date(date);
            return dateOut;
        };
        $scope.valueLabel = "";
        $scope.CompareDate = function (date, effective, fechamed, exp) {
            var date = new Date(date).toISOString().substring(0, 10);
            var expiration = new Date(exp).toISOString().substring(0, 10);
            var Pending = new Date(fechamed);
            var paydate = new Date(fechamed).toISOString().substring(0, 10);
            Pending.setDate(Pending.getDate() + 7);
            Pending = Pending.toISOString().substring(0, 10);
            //   date = date.customFormat("#YYYY#-#MM#-#DD#");
            ///date = new Date(date.getYear() + "-" + date.getMonth() + "-" + date.getDay());
            //  paydate = new Date(paydate.getYear() + "-" + paydate.getMonth() + "-" + paydate.getDay());

            // Pending = new Date(Pending.getYear() + "-" + Pending.getMonth() + "-" + Pending.getDay());
            //  expiration = new Date(expiration.getYear() + "-" + expiration.getMonth() + "-" + expiration.getDay());

            //console.log(date);
            //console.log(paydate);
            //console.log(Pending);
            if (date <= paydate) {
                $scope.valueLabel = "Active";
                return "Active";
            } else if (date > paydate && date <= Pending) {
                $scope.valueLabel = "Pending Cancellation";
                return "PendingCancellation";
            } else if (date > Pending && date <= expiration) {
                $scope.valueLabel = "Cancelled";
                return "Cancelled";
            } else if (date > expiration) {
                $scope.valueLabel = "Expired";

                return "Expired";

            } else {

                return "wo";
            }

        };
        //ng-class="{'expired': valueLabel === 'Expired' || valueLabel === 'Cancelled','mactive':valueLabel === 'Active','pendingC':valueLabel === 'PendingCancellation'}"
        $scope.SearchCustomer = function (s) {


            $http.post('index.php?r=api/searchclients', {membershiID: $scope.searchboxID, Name: $scope.searchboxName, LastName: $scope.searchboxLastName}).then(
                    function success(response) {

                        if (response.data.status == 'success') {
                            $scope.Clients = response.data.Info;

                            //console.log($scope.Clients);
                        } else {
                            swal({
                                type: 'error',
                                title: 'Oops...',
                                text: 'Something went wrong!',
                            });
                        }
                    }, function error(response) {
                console.log('Error validacion');
                    //                angular.forEach(response.data, function (error) {
                    //                    //console.log(' - ' + error);
                    //                    $scope.error[error.field] = error.message;
                    //                });
                swal({
                    type: 'error',
                    title: 'Oops...',
                    text: 'Something went wrong!',
                });
            }
            );

        };

        $scope.getData = function () {
            return $filter('filter')($scope.Clients, $scope.searchbox);
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
            if($scope.getData().length == 0 || $('#clients-table>tbody>tr').length == 0)
                return 0;
            else 
                return ($scope.currentPage-1)*$scope.itemsPerPage + 1;
        };

        $scope.displayingEnd = function() {
            return ($scope.currentPage-1)*$scope.itemsPerPage + $('#clients-table>tbody>tr').length;
        };

    }]);