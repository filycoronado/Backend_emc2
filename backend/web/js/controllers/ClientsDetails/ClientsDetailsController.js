app.controller('ClientsDetailsController', ['$scope', '$rootScope', '$http', '$routeParams', '$window', '$location', '$filter', function ($scope, $rootScope, $http, $routeParams, $window, $location, $filter) {
        $scope.client = {};
        $scope.note = "";
        $scope.notes = [];
        $scope.display = true;
        $scope.CurrentStatus = "no info";
        $scope.displayVehicles = true;
        $scope.displayClaims = true;
        $scope.switch = function () {
            $scope.display = !$scope.display;
        }
        $scope.switchVehicles = function () {
            $scope.displayVehicles = !$scope.displayVehicles;
        }
        $scope.switchClaims = function () {
            $scope.displayClaims = !$scope.displayClaims;
        }

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

        $scope.getState = function (v) {
            return $filter('filter')($scope.stateDictionary, {value: v})[0].text;
        };

        $scope.languageDictionary = [
            {value: 1, text: "ENGLISH"},
            {value: 2, text: "SPANISH"},
            {value: 3, text: "OTHER"}];

        $scope.getLanguage = function (v) {
            return $filter('filter')($scope.languageDictionary, {value: v})[0].text;
        };

        $scope.vehicles = [];
        $scope.payments = [];
        $scope.claims = [];

        $http.post('index.php?r=api/get_client_by_id', {id_client: $scope.id_client}).then(function success(response) {
            $scope.client = response.data;
            $scope.CompareDate();
        }, function error(response) {
            if (response.status === 404) {
                //$location.path('/404').replace();
            }
        });

        $http.post('index.php?r=api/get_cars_by_id', {id_client: $scope.id_client}).then(function success(response) {
            $scope.vehicles = response.data;
        });

        $http.post('index.php?r=api/get_payhistory_latest', {id_client: $scope.id_client}).then(function success(response) {
            $scope.payments = response.data;

        });

        $http.post('index.php?r=api/get_claims_by_id_latest', {id_client: $scope.id_client}).then(function success(response) {
            $scope.claims = response.data;


        });

        $http.post('index.php?r=api/get_notes_last', {id_client: $scope.id_client}).then(function success(response) {
            $scope.notes = response.data;


        });

        $scope.addNote = function () {

            var date = new Date().toISOString().substring(0, 10);
            $scope.noteobj = {
                user_id: "",
                client_id: $scope.id_client,
                date: date,
                note: $scope.note
            };
            $scope.notes.push($scope.noteobj);


            $http.post('index.php?r=api/save_note', {id_client: $scope.id_client, note: $scope.noteobj}).then(function success(response) {

                $('#addNewNote').modal('hide');
                $scope.noteobj = {};
                $scope.note = "";
                alert("success");
            });

        }
        $scope.CompareDate = function () {
            var date = new Date().toISOString().substring(0, 10);
            var expiration = new Date($scope.client.exp).toISOString().substring(0, 10);
            var Pending = new Date($scope.client.fechaMed);
            var paydate = new Date($scope.client.fechaMed).toISOString().substring(0, 10);
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
                $scope.CurrentStatus = "Active";

            } else if (date > paydate && date <= Pending) {
                $scope.CurrentStatus = "Pending Cancellation";

            } else if (date > Pending && date <= expiration) {
                $scope.CurrentStatus = "Cancelled";
                return "Cancelled";
            } else if (date > expiration) {
                $scope.CurrentStatus = "Expired";

                return "Expired";

            } else {

                return "wo";
            }

        };
    }
]);