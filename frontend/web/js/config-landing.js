app.config(['$routeProvider', '$httpProvider',
    function ($routeProvider, $httpProvider) {
        $routeProvider.
                //LANDING
                when('/', {
                    templateUrl: 'partials/site/index.html'
                }).
                when('/[Aa]bout', {
                    templateUrl: 'partials/site/about.html'
                }).
                when('/[Ss]ervices', {
                    templateUrl: 'partials/site/services.html'
                }).
                when('/[Aa]gent', {
                    templateUrl: 'partials/site/agent.html',
                    controller: 'LoginController'
                }).
                when('/[Cc]ontact', {
                    templateUrl: 'partials/site/contact.html'
                }).
                when('/[Rr]eseller', {
                    templateUrl: 'partials/site/reseller.html'
                }).
                when('/[Rr]eseller/singin', {
                    templateUrl: 'partials/site/reseller_singin.html',
                    controller: 'LoginController'
                }).
                when('/[Rr]eseller/apply', {
                    templateUrl: 'partials/site/reseller_apply.html',
                    controller: 'RegisterController'
                }).
                
                otherwise({
                    templateUrl: 'partials/404.html'
                });
        $httpProvider.interceptors.push('authInterceptor');
        $httpProvider.interceptors.push('httpInterceptor');
    }
]);
app.config(['$locationProvider', function ($locationProvider) {
        $locationProvider.hashPrefix('');
    }]);