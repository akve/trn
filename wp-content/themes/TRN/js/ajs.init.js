var rootApp = angular.module('root', ['ngSanitize','ngMaterial','ngMessages','angular-growl','ngTable'])
.config(function($mdThemingProvider) {
	$mdThemingProvider.theme('default')
		.primaryPalette('green')
		.accentPalette('lime');
});

// set the controllers here
rootApp.controller('LoginController', LoginController);
rootApp.controller('BuyerController', BuyerController);
rootApp.controller('BuyerProfileController', BuyerProfileController);
//rootApp.controller('BuyerHistoryController', BuyerHistoryController);
rootApp.controller('SellerController', SellerController);
rootApp.controller('HeaderController', HeaderController);