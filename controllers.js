'use strict';

/* Controllers */
var userApp = angular.module('userApp', ['ngRoute']);

userApp.config(['$locationProvider', function($locationProvider){}]);

userApp.filter('limitFromTo', function(){
    return function(input, from, to){
        return (input != undefined)? input.slice(from, to) : '';
    }
});

userApp.controller('UsersListCtrl',['$scope', '$http', '$location', function($scope, $http, $location) {
  var url = 'http://api.randomuser.me/?results=100';
  $scope.from = 0;
  $scope.to = 25;

  $http.get(url).success(function(data, status, headers, config) {
    $scope.users = data['results'];

    $scope.range = [];
    for(var i = 0; i < $scope.users.length/25; i++){
      $scope.range.push(i);
    };
  });

    $scope.sortField = undefined;
    $scope.reverse = false;

    $scope.sort = function(fieldName){
      if($scope.sortField === fieldName){
        $scope.reverse = !$scope.reverse;
      } else {
        $scope.sortField = fieldName;
        $scope.reverse = false;
      }
    };

    $scope.isSortUp = function(fieldName){
      return $scope.sortField === fieldName && !$scope.reverse;
    };
    $scope.isSortDown = function(fieldName){
      return $scope.sortField === fieldName && $scope.reverse;
    }; 

    $scope.setPage = function(page){
        if(page === 0){
          $scope.from = 25 * page;
        }else{
          $scope.from = 25 * page + 1;
        }

        if(page === 0){
          $scope.to = $scope.from + 25;
        }else{
          $scope.to = $scope.from + 25 -1;
        }
          
      $scope.currentPage = page;
    }  

}]);


