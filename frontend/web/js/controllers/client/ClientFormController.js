app.controller('ClientFormController', ['$scope', '$rootScope', '$routeParams', '$http', '$timeout', '$filter', function ($scope, $rootScope, $routeParams, $http, $timeout, $filter) {

        var Id = $routeParams.id;
        $scope.edit = false;

        $scope.client = {};
        $scope.client.effective = new Date();
        $scope.client.pay = 2;
        //$scope.client.paymode = 0;

        $scope.vehicles = [];
        $scope.vehicle = {};

        $scope.cardInfo = {};

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

        $scope.selectedService = {};
        $scope.services = [
            {id: 1, desc: "Roadside", baseMensual: 14, color: "#1c335c", f1: "Towing", f2: "Flat Tire Change", f3: "Lock Out", f4: "Fuel Delivery", f5: "Baterry Jumpstart"},
            {id: 2, desc: "Solo Glass", baseMensual: 12, color: "#79a85a", f1: "Glass Replacement", f2: "", f3: "", f4: "", f5: ""}];

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

        console.log('id: ' + Id);
        if (Id !== undefined) {
            $scope.edit = true;
            $http.post('index.php?r=api/get_client_by_id', {id_client: Id}).then(function success(response) {
                $scope.client = response.data;
                $scope.client.date_birth = new Date($scope.client.date_birth + "T12:00:00Z");
            }, function error(response) {
                if (response.status === 404) {
                    //$location.path('/404').replace();
                }
            });
        }

        $scope.serviceSel = true;
        $scope.plansel = false;
        $scope.resumeplans = false;

        $scope.selectService = function (s) {
            $scope.selectedService = s;
            $scope.client.service = s.id;
            //corregir la seleccion de plan al cambiar de servicio
            if ($scope.client.plan) {
                $scope.selectedPlan = $filter('filter')($scope.planPeriods, {'value': $scope.client.plan, 'serviceId': $scope.client.service})[0];
            }

            //forzar glass en todos los vehiculos previamente agregados
            if ($scope.selectedService.id == 2) {
                angular.forEach($scope.vehicles, function (value, key) {
                    value.glass = 'Yes';
                });
            }
            $scope.serviceSel = false;
            $scope.plansel = true;

        }

        $scope.selectPlan = function (p) {
            $scope.selectedPlan = p;
            $scope.client.plan = p.value;
            $scope.calcExpDate();
            if ($scope.selectedPlan.value == 1) {
                $scope.client.paymode = '0';
            }
            $scope.plansel = false;
            $scope.resumeplans = true;
        }

        $scope.calcExpDate = function () {
            if ($scope.selectedPlan.months) {
                //console.log('period: '+$scope.selectedPlan.months+' months')
                var ed = new Date($scope.client.effective);
                ed.setMonth(ed.getMonth() + $scope.selectedPlan.months);
                $scope.client.fechaMed = ed;
            } else {
                $scope.client.fechaMed = null;
            }

        }

        $scope.addVehicle = function () {
            if ($scope.selectedService.id == 2) {
                $scope.vehicle.glass = 'Yes';
            }
            if ($scope.vehicles.length < 5) {
                $scope.vehicles.push($scope.vehicle);
                $scope.vehicle = {};
                $('#addNewCarModal').modal('hide');
            } else {
                swal(
                        'Error!',
                        'Maximum of vehicles to add exceeded!',
                        'error'
                        ).then((result) => {
                    $('#addNewCarModal').modal('hide');
                });
            }
        }

        $scope.calculo = function () {
            var calculo = 0;
            if ($scope.selectedPlan.value) {
                // poner el precio base segun el plan seleccionadp
                calculo = $scope.selectedPlan.precioBase;
                if ($scope.selectedService.id == 1) {
                    // si es roadside agregar 10 dolares por mes por cada auto con glass
                    var months = $scope.selectedPlan.months;
                    angular.forEach($scope.vehicles, function (value, key) {
                        if (value.glass == 'Yes')
                            calculo += 10 * months;
                    });
                } else if ($scope.selectedService.id == 2) {
                    calculo = $scope.vehicles.length * calculo;
                }
            }
            return calculo;
        }

        $scope.calculoRequired = function () {
            var calculo = $scope.calculo();
            if ($scope.selectedPlan.value) {

                if ($scope.client.paymode == 1) {
                    //pago 50%
                    calculo /= 2;
                }

                var opcion_seleccionada = $("#pay option:selected").text();
                if (opcion_seleccionada == "FREE") {
                    calculo = 0;
                }
            }
            return calculo;
        }

        $scope.initPaymentDay = function () {
            if ($scope.cardInfo.recurrentPayment && $scope.client.fechaMed && !$scope.cardInfo.paymentDay) {
                console.log("SETTING PAYMENT REC DATE" + $scope.client.fechaMed);
                $scope.cardInfo.paymentDay = $scope.client.fechaMed;
            }
        }

        $scope.saveClient = function () {
            $scope.error = {};
            if ($scope.edit) {
                $http.post('index.php?r=api/update_client&id=' + Id, {modelClient: $scope.client}).then(
                        function success(response) {
                            if (response.data.status === 'success') {
                                location.href = '#/Customer/' + response.data.id;
                            } else {
                                swal({
                                    type: 'error',
                                    title: 'Oops...',
                                    text: 'Something went wrong!',
                                });
                            }
                        }, function error(response) {
                    console.log('Error validacion');
                    angular.forEach(response.data, function (error) {
                        //console.log(' - ' + error);
                        $scope.error[error.field] = error.message;
                    });
                    swal({
                        type: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong!',
                    });
                }
                );
            } else {
                if ($scope.vehicles.length < 1) {
                    swal(
                            'Error!',
                            'Add at least 1 vehicle!',
                            'error'
                            );
                    return false;
                }
                if ($scope.client.paid < $scope.calculoRequired()) {
                    swal(
                            'Error!',
                            'Insufficient amount!',
                            'error'
                            );
                    return false;
                }
                $scope.client.total = $scope.calculo();
                $scope.client.pending = $scope.client.total - $scope.client.paid;
                $http.post('index.php?r=api/save_client', {modelClient: $scope.client, vehicles: $scope.vehicles, cardInfo: $scope.cardInfo}).then(
                        function success(response) {
                            if (response.data.status === 'success') {
                                location.href = '#/Customer/' + response.data.id;
                            } else {
                                swal({
                                    type: 'error',
                                    title: 'Oops...',
                                    text: 'Something went wrong!',
                                });
                            }
                        }, function error(response) {
                    console.log('Error validacion');
                    angular.forEach(response.data, function (error) {
                        //console.log(' - ' + error);
                        $scope.error[error.field] = error.message;
                    });
                    swal({
                        type: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong!',
                    });
                }
                );
            }

        };
    }]);