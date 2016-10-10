angular.module('trnadmin', ['ngSanitize','ngMaterial', 'Common', 'Buyers', 'Sellers'])
	.controller('AdminBuyerController', ['FactoryBuyers', 'RDRouter', '$mdDialog', AdminBuyerController])
	.controller('AdminSellerController', ['FactorySeller', 'RDRouter', '$timeout', '$mdDialog', AdminSellerController])
	.controller('AdminSettingsController', ['LocalDatabase', '$timeout', AdminSettingsController]);