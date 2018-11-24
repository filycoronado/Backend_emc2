app.controller('AddClaimController', ['$scope', '$rootScope', '$http', '$routeParams', '$location', function ($scope, $rootScope, $http, $routeParams, $location) {
        $scope.client = {};


        $scope.id_client = $routeParams.id;


        $scope.vehicles = [];
        $scope.cities = [];
        $scope.providers = [];



        $http.post('index.php?r=api/get_client_by_id', {id_client: $scope.id_client}).then(function success(response) {
            $scope.client = response.data;
        });

        $http.post('index.php?r=api/get_cars_by_id', {id_client: $scope.id_client}).then(function success(response) {
            $scope.vehicles = response.data;

        });

        $http.post('index.php?r=api/get_cyties').then(function success(response) {
            $scope.cities = response.data;
            //console.log($scope.cities);
        });





        $scope.response;
        $scope.AllClaim = {
            "City": "",
            "ProviderSelected": "",
            "VehicleSelected": "",
            "VehicleConditions": "",
            "vehicleType": "",
            "VehicleColor": "",
            "vehilePlates": "",
            "mtv": 0,
            "loadMiles": 0,
            "originL": "",
            "DestinationL": "",
            "ClaimType": "",
            "total":0,
            "observations": "Observations Here..",

        };
        $scope.GetProviders = function () {
            $http.post('index.php?r=api/get_provider_bycity', {id_ciudad: $scope.AllClaim.City}).then(function success(response) {
                $scope.providers = response.data;

            });
        };

        $scope.saveClaim = function () {

            console.log($scope.AllClaim);
            $http.post('index.php?r=api/save_claim', {claimForm: $scope.AllClaim, id_client: $scope.id_client}).then(
                    function success(response) {
                        $scope.response = response.data;
                        $location.path("/Customer/" + $scope.id_client);
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
            });


        };

    }
]);