angular.module("Common", [])
	.factory('PostFunction', ['$http', '$timeout', FactoryPostFunction])
	.factory('LocalDatabase', ['PostFunction', FactoryLocalDatabase])
	.factory('RDRouter', [FactoryRouter])
	.directive('templateVar', [DirectiveVariableTemplate])
	.directive('rdPagination', [DirectivePagination]);