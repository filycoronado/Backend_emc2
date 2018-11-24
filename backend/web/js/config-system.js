app.config(['$routeProvider', '$httpProvider',
    function ($routeProvider, $httpProvider) {
        $routeProvider.
                when('/', {
                    templateUrl: 'partials/system/dashboard.html',
                    controller: 'DashboardController'
                }).
                when('/dashboard', {
                    templateUrl: 'partials/system/dashboard.html',
                    controller: 'DashboardController'
                }).
                //REPORTS
                when('/Report/Sales', {
                    templateUrl: 'partials/system/SalesReport/SalesReport.html',
                    controller: 'SalesReportController'
                }).
                when('/Report/Status', {
                    templateUrl: 'partials/system/StatusReport/StatusReport.html',
                    controller: 'StatusReportController'
                }).
                when('/Report/Endorsements', {
                    templateUrl: 'partials/system/EndorsmentReport/EndorsmentReport.html',
                    controller: 'EndorsmentReportController'
                }).
                when('/Report/Premium', {
                    templateUrl: 'partials/system/PremiumReport/PremiumReport.html',
                    controller: 'PremiumReportController'
                }).
                when('/Report/Claims', {
                    templateUrl: 'partials/system/ClaimsReport/ClaimsReport.html',
                    controller: 'ClaimsReportController'
                }).
                //CUSTOMER
                when('/Customer/add', {
                    templateUrl: 'partials/system/client/form.html',
                    controller: 'ClientFormController'
                }).
                when('/Customer/:id', {
                    templateUrl: 'partials/system/CustomerDetails/CustomerDetails.html',
                    controller: 'ClientsDetailsController'
                }).
                when('/Customer/:id/edit', {
                    templateUrl: 'partials/system/client/form.html',
                    controller: 'ClientFormController'
                }).
                when('/Customer/:id/payments', {
                    templateUrl: 'partials/system/CustomerDetails/CustomerPayments.html',
                    controller: 'ClientsPaymentsController'
                }).
                when('/Customer/:id/Notes', {
                    templateUrl: 'partials/system/CustomerDetails/CustomerNotes.html',
                    controller: 'CustomerNotesController'
                }).
                when('/Customer/:id/claims', {
                    templateUrl: 'partials/system/CustomerDetails/CustomerClaims.html',
                    controller: 'ClientsClaimsController'
                }).
                when('/Customer/:id/makePayment', {
                    templateUrl: 'partials/system/makeAPayment/MakeAPayment.html',
                    controller: 'MakeAPaymentController'
                }).
                when('/Customer/:id/addClaim', {
                    templateUrl: 'partials/system/addClaim/addClaim.html',
                    controller: 'AddClaimController'
                }).
                when('/Customers/link-authorize', {
                    templateUrl: 'partials/404.html'
                }).
                when('/Customers/Search', {
                    templateUrl: 'partials/system/SearchClients/SerachClients.html',
                    controller: 'SearchClientController'
                }).
                when('/Customer/:id/Coverages', {
                    templateUrl: 'partials/system/CustomerDetails/AddCoverages.html',
                    controller: 'AddCoveragesController'
                }).
                when('/Customer/:id/ModifyMembership', {
                    templateUrl: 'partials/system/CustomerDetails/ModifyMembership.html',
                    controller: 'AddCoveragesController2'
                }).
                when('/Claim/:id/Details', {
                    templateUrl: 'partials/system/addClaim/ClaimDetails.html',
                    controller: 'ClaimDetailController'
                }).
                when('/Reseller/View/', {
                    templateUrl: 'partials/system/Reseller/ViewPostulants.html',
                    controller: 'PostulantsController'
                }).
                when('/Customer/:id/AutoPay', {
                    templateUrl: 'partials/system/CustomerDetails/ConfAutoPay.html',
                    controller: 'ConfigureAutoPayController'
                }).
                when('/forms', {
                    templateUrl: 'partials/system/forms/forms.html'
                }).
                otherwise({
                    templateUrl: 'partials/404.html'
                });
        // Reseller/View
        $httpProvider.interceptors.push('authInterceptor');
        $httpProvider.interceptors.push('httpInterceptor');
    }
]);
app.config(['$locationProvider', function ($locationProvider) {
        $locationProvider.hashPrefix('');
    }]);