overwatchApp.controller('DashboardController', function(showLoading, isGranted, $scope, $http, $window, $interval) {
    $scope.groups = [];
    var fetchGroups = function() {
        $http.get(Routing.generate('overwatch_test_testgroupapi_getallgroups'))
            .success(function(groups){
                $scope.groups = groups;
                showLoading(false);
            })
        ;
    };
    var interval = $interval(fetchGroups, 60000);
    
    $scope.$on('$destroy', function() {
        $interval.cancel(interval);
    });
    
    $scope.isGranted = function(role, group) {
        return isGranted(role, group.name);
    }
    
    $scope.removeTest = function(id) {
        if (!$window.confirm('Are you sure you want to remove this test? All historical data for this test will also be deleted.')) {
            return;
        }
        
        showLoading(true);
        $http.delete(Routing.generate('overwatch_test_testapi_deletetest', {'id': id}))
            .success(function(){
                fetchGroups();
            })
        ;
    }
    
    $scope.removeGroup = function(id) {
        if (!$window.confirm('Are you sure you want to remove this group?')) {
            return;
        }
        
        showLoading(true);
        $http.delete(Routing.generate('overwatch_test_testgroupapi_deletegroup', {'id': id}))
            .success(function(){
                fetchGroups();
            })
        ;
    }
    
    $scope.createGroup = function() {
        var name = $window.prompt("Please enter a name for the new group", "Untitled Group");
        if (name === null) {
            return;
        }
        
        showLoading(true);
        $http.post(Routing.generate('overwatch_test_testgroupapi_creategroup'), {'name': name})
                .success(function(){
                    fetchGroups();
                })
        ;
    }
    
    fetchGroups();
});

overwatchApp.controller('EditGroupController', function(showLoading, $scope, $http, $routeParams, $location, $window) {
    $scope.group = {};
    
    var fetchGroup = function() {
        $http.get(Routing.generate('overwatch_test_testgroupapi_getgroup', {id: $routeParams.id}))
            .success(function(group) {
                $scope.group = group;
                showLoading(false);
            })
        ;
    };
    
    $scope.removeUser = function(id) {
        if (!$window.confirm("Are you sure you want to remove this user from group '" + $scope.group.name + "'?")) {
            return;
        }
        
        showLoading(true);
        $http.delete(Routing.generate('overwatch_test_testgroupapi_removeuserfromgroup', {groupId: $scope.group.id, userId: id}))
            .success(function(){
                fetchGroup();
            })
        ;
    };
    
    $scope.addUser = function() {
        var email = $window.prompt("Please enter the e-mail address of the user you wish to add to group '" + $scope.group.name + "'", currentUser.email);
        if (email === null) {
            return;
        }
        
        showLoading(true);
        $http.get(Routing.generate('overwatch_user_api_finduser', {email: email}))
            .success(function(user) {
                $http.post(Routing.generate('overwatch_test_testgroupapi_addusertogroup', {groupId: $scope.group.id, userId: user.id}))
                    .success(function() {
                        fetchGroup();
                    })
                ;
            })
            .error(function() {
                showLoading(false);
                $window.alert("Could not find user by e-mail address '" + email + "'. Please ensure that they are already registered.");
            })
        ;
        
    };
    
    $scope.renameGroup = function() {
        var name = $window.prompt("Please type a new name for this group", $scope.group.name);
        if (name === null || name === $scope.group.name) {
            return;
        }
        
        showLoading(true);
        $http.put(Routing.generate('overwatch_test_testgroupapi_updategroup', {id: $scope.group.id}), {name: name})
            .success(function(group) {
                $scope.group = group;
                currentUser.groups.push(group.name);
                showLoading(false);
            })
        ;
    }
    
    fetchGroup();
});

overwatchApp.controller('AddTestController', function(showLoading, $scope, $http, $routeParams, $location) {
    $scope.title = "Add test";
    $scope.test = {};
    $scope.expectations = [];
    
    $http.get(Routing.generate('overwatch_expectation_api_getall'))
        .success(function(expectations) {
            $scope.expectations = expectations;
            showLoading(false);
        })
    ;
    
    $scope.save = function() {
        showLoading(true);
        $http.post(Routing.generate('overwatch_test_testapi_createtest', {id: $routeParams.id}), $scope.test)
            .success(function() {
                $location.path('/');
            })
        ;
    }
});

overwatchApp.controller('ViewTestController', function(showLoading, $scope, $http, $routeParams, $interval) {
    $scope.test = {};
    $scope.lastRequestedResultSize = 0;
    
    $http.get(Routing.generate('overwatch_test_testapi_gettest', {id: $routeParams.id}))
        .success(function(test) {
            $scope.test = test;
            $scope.loadResults(10);
        })
    ;
    
    $scope.loadResults = function(limit) {
        $http.get(Routing.generate('overwatch_result_api_getresultsfortest', {id: $routeParams.id}) + '?pageSize=' + limit)
            .success(function(results) {
                $scope.test.results = results;
                $scope.lastRequestedResultSize = limit;
                showLoading(false);
            })
        ;
    };
    
    $scope.loadOlderResults = function() {
        showLoading(true);
        $scope.loadResults($scope.lastRequestedResultSize + 10);
    }
    
    var interval = $interval(function() {
        $scope.loadResults($scope.lastRequestedResultSize)
    }, 60000);
    
    $scope.$on('$destroy', function() {
        $interval.cancel(interval);
    });
});

overwatchApp.controller('EditTestController', function(showLoading, $scope, $http, $routeParams, $location) {
    $scope.title = "Edit test";
    $scope.test = {};
    $scope.expectations = [];
    $scope.waitingFor = 2;
    
    $http.get(Routing.generate('overwatch_expectation_api_getall'))
        .success(function(expectations) {
            $scope.expectations = expectations;
            $scope.waitingFor--;
            
            if ($scope.waitingFor === 0) {
                showLoading(false);
            }
        })
    ;
    
    $http.get(Routing.generate('overwatch_test_testapi_gettest', {id: $routeParams.id}))
        .success(function(test) {
            $scope.test = test;
            $scope.waitingFor--;
            
            if ($scope.waitingFor === 0) {
                showLoading(false);
            }
        })
    ;
    
    $scope.save = function() {
        showLoading(true);
        $http.put(Routing.generate('overwatch_test_testapi_updatetest', {id: $routeParams.id}), $scope.test)
            .success(function() {
                $location.path('/test/' + $routeParams.id);
            })
        ;
    }
});

overwatchApp.controller('ManageUsersController', function(showLoading, $scope, $http, $window) {
    $scope.users = [];
    $scope.updatedRoles = [];
    $scope.currentUserId = currentUser.id;
    
    var fetchUsers = function() {
        $http.get(Routing.generate('overwatch_user_api_getallusers'))
            .success(function(users) {
                $scope.users = users;
                showLoading(false);
            })
        ;
    };
    
    $scope.createUser = function() {
        var email = $window.prompt("Please type the new user's email address.", "");
        if (email === null) {
            return;
        }
        
        showLoading(true);
        $http.post(Routing.generate('overwatch_user_api_createuser', {'email': email}))
            .success(function(data) {
                fetchUsers();
            })
        ;
    };
    
    $scope.updateRole = function(id) {
        var realRoles = {A: "ROLE_ADMIN", S: "ROLE_SUPER_ADMIN", U: "ROLE_USER"};
        var role = $window.prompt("What role should this user be? (Enter one of S [Super Admin], A [Admin] or U [User])", "U");
        if (["A", "S", "U"].indexOf(role) === -1) {
            return;
        }
        
        showLoading(true);
        $http.put(Routing.generate('overwatch_user_api_setuserrole', {id: id, role: realRoles[role]}))
            .success(function() {
                fetchUsers();
            })
        ;
    };
    
    $scope.lockUser = function(id) {
        showLoading(true);
        $http.put(Routing.generate('overwatch_user_api_togglelockuser', {id: id}))
            .success(function() {
                fetchUsers();
            })
        ;
    };
    
    $scope.removeUser = function(id) {
        if (!$window.confirm("Are you sure you want to permanently remove this user?")) {
            return;
        }
        
        showLoading(true);
        $http.delete(Routing.generate('overwatch_user_api_deleteuser', {id: id}))
            .success(function() {
                fetchUsers();
            })
        ;
    };
    
    fetchUsers();
});

overwatchApp.controller('ManageAlertSettingsController', function(showLoading, $scope, $http) {
    $scope.settings = [];
    
    var fetchSettings = function() {
        $http.get(Routing.generate('overwatch_user_api_getalertsettings'))
            .success(function(settings) {
                $scope.settings = settings;
                showLoading(false);
            })
        ;
    }
    
    $scope.isUsersSetting = function(id) {
        return (currentUser.alertSetting === id);
    };
    
    $scope.saveSetting = function(id) {
        showLoading(true);
        $http.put(Routing.generate('overwatch_user_api_setalertsetting', {setting: id}))
            .success(function() {
                currentUser.alertSetting = id;
                showLoading(false);
            })
        ;
    };
    
    fetchSettings();
});