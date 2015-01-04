var overwatchApp = angular.module('overwatch', [
    'ngRoute'
]);

overwatchApp.config(function($routeProvider) {
    $routeProvider
            .when('/', {
                title: 'Dashboard',
                templateUrl: 'partials/dashboard.html',
                controller: 'DashboardController'
            })
            .when('/group/:id', {
                title: 'Edit Group',
                templateUrl: 'partials/editGroup.html',
                controller: 'EditGroupController'
            })
            .when('/group/:id/add-test', {
                title: 'Add test',
                templateUrl: 'partials/testForm.html',
                controller: 'AddTestController'
            })
            .when('/test/:id', {
                title: 'View test',
                templateUrl: 'partials/viewTest.html',
                controller: 'ViewTestController'
            })
            .when('/test/:id/edit', {
                title: 'Edit test',
                templateUrl: 'partials/testForm.html',
                controller: 'EditTestController'
            })
            .when('/users', {
                title: 'Manage Users',
                templateUrl: 'partials/manageUsers.html',
                controller: 'ManageUsersController'
            })
            .otherwise({
                redirectTo: '/'
            })
    ;
});