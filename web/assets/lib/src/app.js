var overwatchApp = angular.module('overwatch', [
    'ngRoute',
    'angularModalService',
    'ngIdle'
]);

overwatchApp.run(['$rootScope','$location', '$routeParams',
    function($rootScope, $location, $routeParams)
    {
        $rootScope.$on('$routeChangeSuccess', function(e, current, pre) {
            $rootScope.currentPage = $location.path();
        });
    }
]);

overwatchApp.config(['$routeProvider', '$httpProvider', 'IdleProvider',
    function($routeProvider, $httpProvider, IdleProvider)
    {
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
            .when('/alerts', {
                title: 'Change Alert Settings',
                templateUrl: 'partials/changeAlertSettings.html',
                controller: 'ManageAlertSettingsController'
            })
            .when('/my-account', {
                title: 'My Account',
                templateUrl: '/profile/change-password',
                controller: 'MyAccountController'
            })
            .when('/error', {
                title: 'Error',
                templateUrl: 'partials/error.html',
                controller: function(showLoading, $scope) {
                    $scope.status = {
                        code: '500',
                        text: 'Internal Server Error'
                    };
                    showLoading(false);
                }
            })
            .otherwise({
                title: 'Not Found',
                templateUrl: 'partials/error.html',
                controller: function(showLoading, $scope) {
                    $scope.status = {
                        code: '404',
                        text: 'Not Found'
                    };
                    showLoading(false);
                }
            })
        ;

        $httpProvider.interceptors.push('overwatchApiErrorHandler');

        IdleProvider.idle(5 * 60);
        IdleProvider.timeout(5);
    }
]);
