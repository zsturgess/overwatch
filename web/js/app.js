var overwatchApp = angular.module('overwatch', [
    'ngRoute',
    'overwatchControllers'
]);

overwatchApp.config(function($routeProvider) {
    $routeProvider
            .when('/', {
                templateUrl: 'partials/dashboard.html',
                controller: 'DashboardCtrl'
            })
            .otherwise({
                redirectTo: '/'
            })
    ;
});