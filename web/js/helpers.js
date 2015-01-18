overwatchApp.run(function(showLoading, $rootScope, $window, $http) {
    $rootScope.$on('$routeChangeStart', function() {
        showLoading(true);
    });
    
    $rootScope.$on('$routeChangeSuccess', function(event, current) {
        if (current.title) {
            $window.document.title = current.title + " - Overwatch";
        } else {
            $window.document.title = "Overwatch";
        }
    });
});

overwatchApp.factory('showLoading', function($rootScope) {
    return function(show) {
        $rootScope.isLoading = show;
    };
});

overwatchApp.factory('isGranted', function() {
    var userHasRole = function(role) {
        if (role !== 'ROLE_SUPER_ADMIN' && userHasRole('ROLE_SUPER_ADMIN')) {
            return true;
        } else {
            return (currentUser.roles.indexOf(role) !== -1);
        }
    };
    
    var userInGroup = function(groupName) {
        if (userHasRole('ROLE_SUPER_ADMIN')) {
            return true;
        } else {
            return (currentUser.groups.indexOf(groupName) !== -1);
        }
    };
    
    return function(role, group) {
        switch (role) {
            case 'SUPER_ADMIN':
                return userHasRole('ROLE_SUPER_ADMIN');
                break;
            case 'ADMIN':
                return userHasRole('ROLE_ADMIN') && userInGroup(group);
                break;
            case 'USER':
                return userInGroup(group);
                break;
            default:
                return false;
        }
    }
});

overwatchApp.filter('roleToCss', function() {
    //we're re-using the test CSS classes here, so they look a little odd...
    return function(input) {
        if (input === "ROLE_SUPER_ADMIN") {
            return "failed";
        } else if (input === "ROLE_ADMIN") {
            return "unsatisfactory";
        } else if (input === "ROLE_USER") {
            return "passed";
        } else {
            return "";
        }
    }
});

//From https://gist.github.com/keithics/9911022 (Thanks @keithics!)
overwatchApp.filter('ucfirst', function() {
    return function(input,arg) {
        return input.replace(/(?:^|\s)\S/g, function(a) { return a.toUpperCase(); });
    };
});

//From http://stackoverflow.com/a/27222204 (Thanks @Anam!)
overwatchApp.filter('strLimit', function($filter) {
   return function(input, limit) {
      if (input.length <= limit) {
          return input;
      }

      return $filter('limitTo')(input, limit) + '...';
   };
});

//From https://github.com/uttesh/ngtimeago/blob/master/ngtimeago.js (Thanks @uttesh!)
overwatchApp.filter('timeago', function() {
    return function(input, p_allowFuture) {
        var input = input * 1000; //convert from php timestamps to js timestamps
        
        if (isNaN(input)) {
            return "never";
        }
        
        var substitute = function (stringOrFunction, number, strings) {
                var string = (typeof stringOrFunction === 'function') ? stringOrFunction(number, dateDifference) : stringOrFunction;
                var value = (strings.numbers && strings.numbers[number]) || number;
                return string.replace(/%d/i, value);
            },
            nowTime = (new Date()).getTime(),
            date = (new Date(input)).getTime(),
            //refreshMillis= 6e4, //A minute
            allowFuture = p_allowFuture || false,
            strings= {
                prefixAgo: null,
                prefixFromNow: null,
                suffixAgo: "ago",
                suffixFromNow: "from now",
                seconds: "less than a minute",
                minute: "about a minute",
                minutes: "%d minutes",
                hour: "about an hour",
                hours: "about %d hours",
                day: "a day",
                days: "%d days",
                month: "about a month",
                months: "%d months",
                year: "about a year",
                years: "%d years"
            },
            dateDifference = nowTime - date,
            words,
            seconds = Math.abs(dateDifference) / 1000,
            minutes = seconds / 60,
            hours = minutes / 60,
            days = hours / 24,
            years = days / 365,
            separator = strings.wordSeparator === undefined ?  " " : strings.wordSeparator,


            prefix = strings.prefixAgo,
            suffix = strings.suffixAgo;

        if (allowFuture) {
            if (dateDifference < 0) {
                prefix = strings.prefixFromNow;
                suffix = strings.suffixFromNow;
            }
        }

        words = seconds < 45 && substitute(strings.seconds, Math.round(seconds), strings) ||
        seconds < 90 && substitute(strings.minute, 1, strings) ||
        minutes < 45 && substitute(strings.minutes, Math.round(minutes), strings) ||
        minutes < 90 && substitute(strings.hour, 1, strings) ||
        hours < 24 && substitute(strings.hours, Math.round(hours), strings) ||
        hours < 42 && substitute(strings.day, 1, strings) ||
        days < 30 && substitute(strings.days, Math.round(days), strings) ||
        days < 45 && substitute(strings.month, 1, strings) ||
        days < 365 && substitute(strings.months, Math.round(days / 30), strings) ||
        years < 1.5 && substitute(strings.year, 1, strings) ||
        substitute(strings.years, Math.round(years), strings);

        return [prefix, words, suffix].join(separator).trim();
    };
});

/*! angular-modal-service - v0.5.0 - 2014-11-24 github.com/dwmkerr/angular-modal-service */
!function(){"use strict";var a=angular.module("angularModalService",[]);a.factory("ModalService",["$document","$compile","$controller","$http","$rootScope","$q","$timeout","$templateCache",function(a,b,c,d,e,f,g,h){function i(){var a=this,i=function(a,b){var c=f.defer();if(a)c.resolve(a);else if(b){var e=h.get(b);void 0!==e?c.resolve(e):d({method:"GET",url:b,cache:!0}).then(function(a){h.put(b,a.data),c.resolve(a.data)}).catch(function(a){c.reject(a)})}else c.reject("No template or templateUrl has been specified.");return c.promise};a.showModal=function(a){var d=f.defer(),h=a.controller;return h?(i(a.template,a.templateUrl).then(function(i){var k=e.$new(),l=f.defer(),m={$scope:k,close:function(a,b){(void 0===b||null===b)&&(b=0),g(function(){l.resolve(a)},b)}};if(a.inputs)for(var n in a.inputs)m[n]=a.inputs[n];var o=angular.element(i),p=b(o),q=p(k);m.$element=q;var r=c(h,m);a.appendElement?a.appendElement.append(q):j.append(q);var s={controller:r,scope:k,element:q,close:l.promise};s.close.then(function(){k.$destroy(),q.remove()}),d.resolve(s)}).catch(function(a){d.reject(a)}),d.promise):(d.reject("No controller has been specified."),d.promise)}}var j=a.find("body");return new i}])}();