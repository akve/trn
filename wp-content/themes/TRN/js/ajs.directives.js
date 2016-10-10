// a base template directive
rootApp.directive('templateAjs', function() {
	return {
		restrict: 'EA',
		replace: true,
		link: function(scope, element, attrs) {

		},
		templateUrl: function(elem,attrs) {
			return templatepath+'/remote.php?a=gettemplate&p='+attrs.ver;
		}
	}
});

// a base template directive
rootApp.directive('templatePanel', function() {
	return {
		restrict: 'EA',
		replace: true,
		link: function(scope, element, attrs) {

		},
		templateUrl: function(elem,attrs) {
			return templatepath+'/remote.php?a=getpanel&p='+attrs.ver;
		}
	}
});

// a directive for dynamic templates that change
rootApp.directive('dynamicTemplateAjs', function() {
	return {
		restrict: 'EA',
		replace: true,
		link: function(scope, element, attrs) {
			scope.getContentUrl = function() {
				var template = attrs.ver.replace(/[^A-Za-z]/g, '');
				return templatepath+'/remote.php?a=gettemplate&p='+template;
			}
		},
		template: '<div class="fullsquare" ng-include="getContentUrl()"></div>'
	}
});

// a directive that inits on repeat end
rootApp.directive('endLoad', [function() {
	return {
		restrict: 'A',
		transclude: false,
		link: function(scope, element, attrs) {
			// wait for the last item in the ng-repeat then call init
			if(scope.$last) {
				//do something when it ends
			}
		}
	};
}]);