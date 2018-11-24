app.controller('MakeAPaymentController', ['$scope', '$rootScope', '$http', '$routeParams', '$location', function ($scope, $rootScope, $http, $routeParams, $location) {
        $scope.client = {};
        $scope.cardInfo = {};

        $scope.id_client = $routeParams.id;

        $scope.stateDictionary = [
            {value: "AK", text: "Alaska"},
            {value: "AL", text: "Alabama"},
            {value: "AR", text: "Arkansas"},
            {value: "AS", text: "American Samoa"},
            {value: "AZ", text: "Arizona"},
            {value: "CA", text: "California"},
            {value: "CO", text: "Colorado"},
            {value: "CT", text: "Connecticut"},
            {value: "DC", text: "District of Columbia"},
            {value: "DE", text: "Delaware"},
            {value: "FL", text: "Florida"},
            {value: "GA", text: "Georgia"},
            {value: "GU", text: "Guam"},
            {value: "HI", text: "Hawaii"},
            {value: "IA", text: "Iowa"},
            {value: "ID", text: "Idaho"},
            {value: "IL", text: "Illinois"},
            {value: "IN", text: "Indiana"},
            {value: "KS", text: "Kansas"},
            {value: "KY", text: "Kentucky"},
            {value: "LA", text: "Louisiana"},
            {value: "MA", text: "Massachusetts"},
            {value: "MD", text: "Maryland"},
            {value: "ME", text: "Maine"},
            {value: "MI", text: "Michigan"},
            {value: "MN", text: "Minnesota"},
            {value: "MO", text: "Missouri"},
            {value: "MS", text: "Mississippi"},
            {value: "MT", text: "Montana"},
            {value: "NC", text: "North Carolina"},
            {value: "ND", text: "North Dakota"},
            {value: "NE", text: "Nebraska"},
            {value: "NH", text: "New Hampshire"},
            {value: "NJ", text: "New Jersey"},
            {value: "NM", text: "New Mexico"},
            {value: "NV", text: "Nevada"},
            {value: "NY", text: "New York"},
            {value: "OH", text: "Ohio"},
            {value: "OK", text: "Oklahoma"},
            {value: "OR", text: "Oregon"},
            {value: "PA", text: "Pennsylvania"},
            {value: "PR", text: "Puerto Rico"},
            {value: "RI", text: "Rhode Island"},
            {value: "SC", text: "South Carolina"},
            {value: "SD", text: "South Dakota"},
            {value: "TN", text: "Tennessee"},
            {value: "TX", text: "Texas"},
            {value: "UT", text: "Utah"},
            {value: "VA", text: "Virginia"},
            {value: "VI", text: "Virgin Islands"},
            {value: "VT", text: "Vermont"},
            {value: "WA", text: "Washington"},
            {value: "WI", text: "Wisconsin"},
            {value: "WV", text: "West Virginia"},
            {value: "WY", text: "Wyoming"}];

        $scope.languageDictionary = [
            {value: 1, text: "ENGLISH"},
            {value: 2, text: "SPANISH"},
            {value: 3, text: "OTHER"}];


        $http.post('index.php?r=api/get_client_by_id', {id_client: $scope.id_client}).then(function success(response) {
            $scope.client = response.data;


            if ($scope.client.credit_value > 0) {
                if ($scope.client.paymode === 1) {//1 pay pendu
                    if ($scope.client.total === $scope.client.credit_value) {
                        $scope.client.total = $scope.client.credit_value;
                        $scope.client.paid = $scope.client.total;
                        //$scope.client.credit_value = ($scope.client.credit_value - $scope.client.total);
                    } else if ($scope.client.credit_value > $scope.client.total) {
                        $scope.client.total = ($scope.client.credit_value - $scope.client.total);
                        //  $scope.client.credit_value = ($scope.client.credit_value - $scope.client.total);
                        $scope.client.paid = $scope.client.total;
                    }

                }
                if ($scope.client.paymode === 2) {//2 pays pendi

                    if ($scope.client.credit_value > $scope.client.total) {
                        $scope.client.total = ($scope.client.credit_value - $scope.client.total);
                        $scope.client.paid = $scope.client.total;
                        // $scope.client.credit_value = ($scope.client.credit_value - $scope.client.total);
                    } else {
                        $scope.client.total = $scope.client.credit_value;
                        // $scope.client.credit_value = ($scope.client.credit_value - $scope.client.total);
                        $scope.client.paid = $scope.client.total;
                    }

                }
            } else {
                $scope.client.total;
            }


        });


        $scope.DoPayment = function () {
            $scope.error = {};

            if ($scope.client.paid < $scope.client.total) {
                swal(
                        'Error!',
                        'Insufficient amount!',
                        'error'
                        );
                return false;
            }

            // $scope.client.pending = $scope.client.total - $scope.client.paid;


            $http.post('index.php?r=api/make_payment', {modelClient: $scope.client, cardInfo: $scope.cardInfo, isAddCover: 0, new_business: false, id_client: $scope.id_client}).then(
                    function success(response) {

                        if (response.data === "Error") {
                            swal(
                                    'Error!',
                                    '!!',
                                    'card without funds or error in the data!'
                                    );
                        } else {
                            $location.path("/Customer/" + $scope.id_client);
                        }
                    }, function error(response) {
                swal(
                        'Error!',
                        '!!',
                        'error'
                        );
                angular.forEach(response.data, function (error) {
                    //console.log(' - ' + error);
                    $scope.error[error.field] = error.message;
                });
            }
            );
        };


    }
]);