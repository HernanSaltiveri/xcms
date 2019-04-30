// Generated by CoffeeScript 1.8.0

angular.module("ximdex.main.controller").controller('XModifyGroupUsersCtrl', [
  '$scope', '$http', 'xUrlHelper', '$window', '$filter', 'xDialog', function($scope, $http, xUrlHelper, $window, $filter, xDialog) {
    
	var orderAndReset, orderBy;
    
	orderBy = $filter('orderBy');
    
    orderAndReset = function() {
      $scope.users_not_associated = orderBy($scope.users_not_associated, 'name');
      if ($scope.users_not_associated[0] != null) {
        $scope.newUser = $scope.users_not_associated[0];
      }
      $scope.newRole = Object.keys($scope.roles)[0];
      $scope.users_associated = orderBy($scope.users_associated, 'UserName');
    };
    
    $scope.init = function() {
      $scope.newRole = Object.keys($scope.roles)[0];
    };
    
    $scope.addGroup = function() {
      var role, url, user;
      role = $scope.newRole;
      user = $scope.newUser.id;
      url = xUrlHelper.getAction({
        action: 'modifygroupusers',
        method: 'addgroupuser'
      });
      return $http.get(url + "&nodeid=" + $scope.nodeid + "&id_user=" + user + "&id_role=" + role).success(function(data, status, headers, config) {
        var index, nUser;
        if (data.result === "ok") {
          nUser = {
            IdUser: $scope.newUser.id,
            UserName: $scope.newUser.name,
            IdRole: role,
            dirty: false
          };
          $scope.users_associated.push(nUser);
          index = $scope.users_not_associated.indexOf($scope.newUser);
          $scope.users_not_associated.splice(index, 1);
          return orderAndReset();
        }
      }).error(function(data, status, headers, config) {});
    };
    
    $scope.update = function(index) {
      var url, user;
      user = $scope.users_associated[index];
      url = xUrlHelper.getAction({
        action: 'modifygroupusers',
        method: 'editgroupuser'
      });
      $http.get(url + "&nodeid=" + $scope.nodeid + "&user=" + user.IdUser + "&role=" + user.IdRole).success(function(data, status, headers, config) {
        if (data.result === "ok") {
          $scope.users_associated[index].dirty = false;
        }
      }).error(function(data, status, headers, config) {});
    };
    
    $scope.openDeleteModal = function(index) {
      $scope.index = index;
      return xDialog.openConfirmation($scope["delete"], "ui.dialog.messages.you_are_going_to_delete_this_association,_do_you_want_to_continue?");
    };
    
    return $scope["delete"] = function(res) {
      var index, url, user;
      if (! res) {
        return;
      }
      index = $scope.index;
      user = $scope.users_associated[index];
      url = xUrlHelper.getAction({
        action: 'modifygroupusers',
        method: 'deletegroupuser'
      });
      $http.get(url + "&nodeid=" + $scope.nodeid + "&user=" + user.IdUser).success(function(data, status, headers, config) {
        var nuser;
        if (data.result === "ok") {
          nuser = {
            id: user.IdUser,
            name: user.UserName
          };
          $scope.users_not_associated.push(nuser);
          $scope.users_associated.splice(index, 1);
          orderAndReset();
        }
      }).error(function(data, status, headers, config) {});
    };
  }
]);
