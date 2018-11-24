app.controller('AddCoveragesController', ['$scope', '$rootScope', '$http', '$routeParams', '$window', '$location', '$filter', function ($scope, $rootScope, $http, $routeParams, $window, $location, $filter) {
        $scope.client = {};
        $scope.display = true;
        $scope.displayVehicles = true;
        $scope.displayClaims = true;
        $scope.cardInfo = {};
        $scope.total = 0;
        $scope.NewValueMember = 0;
        $scope.switchVehicles = function () {
            $scope.displayVehicles = !$scope.displayVehicles;
        };
        $scope.id_client = $routeParams.id;
        $scope.selectedService = {};
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
        $scope.vehicles = [];
        $scope.vehicles2 = [];
        $scope.payments = [];
        $scope.claims = [];
        $scope.selectedService = {};
        $scope.services = [
            {id: 1, desc: "Roadside", baseMensual: 14, color: "#1c335c"},
            {id: 2, desc: "Glass", baseMensual: 12, color: "#79a85a"}];
        $scope.selectedPlan = {};
        $scope.planPeriods = [
            //roadside
            {value: 1, serviceId: 1, precioBase: 14, months: 1, desc: " Monthly", color: "#f15a24"},
            {value: 2, serviceId: 1, precioBase: 75, months: 6, desc: "/6 Months", color: "#00a99d"},
            {value: 3, serviceId: 1, precioBase: 150, months: 12, desc: "/1 Year", color: "#662d91"},
            //glass
            {value: 1, serviceId: 2, precioBase: 12, months: 1, desc: " Monthly", color: "#f15a24"},
            {value: 2, serviceId: 2, precioBase: 72, months: 6, desc: "/6 Months", color: "#00a99d"},
            {value: 3, serviceId: 2, precioBase: 144, months: 12, desc: "/1 Year", color: "#662d91"},
        ];
        $scope.selectService = function (s) {
            $scope.selectedService = s;
            if ($scope.client.plan) {
                $scope.selectedPlan = $filter('filter')($scope.planPeriods, {'value': $scope.client.plan, 'serviceId': $scope.client.service})[0];
            }
            //forzar glass en todos los vehiculos previamente agregados
            if ($scope.selectedService == 2) {
                angular.forEach($scope.vehicles, function (value, key) {
                    value.glass = 'Yes';
                });
            }

        };
        $scope.calcularCoverage = function (val, index) {

            if (val == "YES" || val == "Yes") {

                $scope.total += 10;
            } else {
                if (!$scope.total == 0) {
                    $scope.total -= 10;
                }

            }
            $scope.NewValueMember = $scope.calculo();
        };
        $scope.calculo = function (s) {
            var calculo = 0;

            if ($scope.selectedPlan.value) {
                // poner el precio base segun el plan seleccionadp
                calculo = $scope.selectedPlan.precioBase;
                if ($scope.selectedService == 1) {
                    // si es roadside agregar 10 dolares por mes por cada auto con glass
                    var months = $scope.selectedPlan.months;
                    angular.forEach($scope.vehicles, function (value, key) {
                        if (value.glass == 'Yes' || value.glass == 'YES')
                            calculo += (10 * months);

                    });
                } else if ($scope.selectedService == 2) {
                    var months = $scope.selectedPlan.months;
                    var value = 12 * months;
                    calculo = value * $scope.vehicles.length;
                }
            }
            return calculo;
        }
        $scope.calculoRequired = function () {
            var calculo = $scope.calculo();
//            if ($scope.selectedPlan.value) {
//
//                if ($scope.client.paymode == 1) {
//                    //pago 50%
//                    calculo /= 2;
//                }
//
//                var opcion_seleccionada = $("#pay option:selected").text();
//                if (opcion_seleccionada == "FREE") {
//                    calculo = 0;
//                }
//            }
            return calculo;
        }



        $http.post('index.php?r=api/get_client_by_id', {id_client: $scope.id_client}).then(function success(response) {
            $scope.client = response.data;
            $scope.selectedService = $scope.client.service;
            $scope.selectedPlan.value = $scope.client.plan;
            $scope.selectService(1);
        }, function error(response) {
            if (response.status === 404) {
                //$location.path('/404').replace();
            }
        });
        $http.post('index.php?r=api/get_cars_by_id', {id_client: $scope.id_client}).then(function success(response) {
            $scope.vehicles = response.data;
            $scope.vehicles2 = response.data;

        });
        $scope.client.total = $scope.NewValueMember;
        $scope.SaveCoverage = function () {
            $http.post('index.php?r=api/savecoverages', {modelClient: $scope.client, Gtotal: $scope.NewValueMember, vehicles: $scope.vehicles, id_client: $scope.id_client, cardInfo: $scope.cardInfo}).then(function success(response) {
                alert("Successfully");
                $location.path("/Customer/" + $scope.id_client);
            }, function error(response) {
                if (response.status === 404) {
                    //$location.path('/404').replace();
                }
            });
        };

        $scope.testm = function () {
            $location.path("/Customer/" + $scope.id_client);
        };

    }
]);