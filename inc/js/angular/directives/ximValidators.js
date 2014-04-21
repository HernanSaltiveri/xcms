/**
 *  \details &copy; 2011  Open Ximdex Evolution SL [http://www.ximdex.org]
 *
 *  Ximdex a Semantic Content Management System (CMS)
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published
 *  by the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  See the Affero GNU General Public License for more details.
 *  You should have received a copy of the Affero GNU General Public License
 *  version 3 along with Ximdex (see LICENSE file).
 *
 *  If not, visit http://gnu.org/licenses/agpl-3.0.html.
 *
 *  @author Ximdex DevTeam <dev@ximdex.com>
 *  @version $Revision$
 */
angular.module('ximdex.common.directive.validator')
	.directive('ximAlphanumeric', function(){
		return {
			require: 'ngModel',
			link: function(scope, element, attrs, ctrl){
				pattern = /^\w+$/;
				ctrl.$parsers.unshift(function(viewValue){
					if (pattern.test(viewValue)){
						ctrl.$setValidity('alphanumeric', true);
						return viewValue
					} else {
						ctrl.$setValidity('alphanumeric', false);
						return undefined
					}
				});
			}
		}
	});
angular.module('ximdex.common.directive.validator')
	.directive('ximunique', ['xCheck', function(xCheck){
		return {
			require: 'ngModel',
			link: function(scope, element, attrs, ctrl){
				var url = attrs.ximUnique;
				var context = attrs.ximUniqueContext;
				ctrl.$parsers.unshift(function(viewValue){
					xCheck.isUnique({
						value: viewValue, 
						context: context, 
						url: url
					}, function(isUnique){
						if (isUnique){
							ctrl.$setValidity('unique', true);
							return viewValue
						} else {
							ctrl.$setValidity('unique', false);
							return undefined
						}	
					});
					
				});

			}
		}
	}]);	