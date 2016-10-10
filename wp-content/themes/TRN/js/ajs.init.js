var rootApp = angular.module('root', ['ngSanitize','ngMaterial','ngMessages'])
.config(function($mdThemingProvider) {
	$mdThemingProvider.theme('default')
		.primaryPalette('green')
		.accentPalette('lime');
});


// set the controllers here
rootApp.controller('LoginController', LoginController);
rootApp.controller('BuyerController', BuyerController);
rootApp.controller('SellerController', SellerController);
rootApp.controller('HeaderController', HeaderController);