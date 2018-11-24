app.controller('ClaimDetailController', ['$scope', '$rootScope', '$http', '$routeParams', '$location', function ($scope, $rootScope, $http, $routeParams, $location) {
        $scope.client = {};


        $scope.id_claim = $routeParams.id;


        $scope.vehicles = [];
        $scope.cities = [];
        $scope.providers = [];







        $http.post('index.php?r=api/get_cyties').then(function success(response) {
            $scope.cities = response.data;
            //console.log($scope.cities);
        });





        $scope.response;
        $scope.AllClaim = {
            "City": "",
            "ProviderSelected": "",
            "VehicleSelected": "",
            "autoestado": "",
            "vehicleType": "",
            "color": "",
            "mtv": 0,
            "lm": 0,
            "dirorigen": "",
            "dirdestino": "",
            "tipoclaim": "",
            "claim": "Observations Here..",
            "placas": 0,
            "member_id": 0,
            "provider": "",
            "tc":0,

        };
        $scope.GetProviders = function () {
            $http.post('index.php?r=api/get_provider_bycity', {id_ciudad: $scope.AllClaim.City}).then(function success(response) {
                $scope.providers = response.data;

            });
        };


        $http.post('index.php?r=api/get_claim_byid', {id_claim: $scope.id_claim}).then(function success(response) {
            $scope.AllClaim = response.data;

            $scope.id_client = $scope.AllClaim.member_id;
            console.log($scope.AllClaim);
            $http.post('index.php?r=api/get_client_by_id', {id_client: $scope.id_client}).then(function success(response) {
                $scope.client = response.data;
            });

            $http.post('index.php?r=api/get_cars_by_id', {id_client: $scope.id_client}).then(function success(response) {
                $scope.vehicles = response.data;

            });

        });


        $scope.UpdateClaim = function () {

            console.log($scope.AllClaim);
            $http.post('index.php?r=api/update_claim', {claimForm: $scope.AllClaim, id_client: $scope.id_client}).then(
                    function success(response) {
                        $scope.response = response.data;
                        alert("Updated Success");
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