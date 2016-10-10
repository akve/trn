function AdminBuyerController(FactoryBuyers, RDRouter, $mdDialog) {
	var abc = this;
	abc.buyers = [];

	abc.getBuyers = function() {
		abc.loading = true;
		FactoryBuyers.getBuyers(function(buyers) {
			abc.loading = false;
			abc.buyers = buyers;
		});
	}
	abc.ChangeStatus = function(buyer, index) {
		FactoryBuyers.ChangeStatus(buyer, function(changed) {
			abc.buyers[index] = changed;
		});
	}

	abc.ChangeBlockedStatus = function(buyer, index) {
		FactoryBuyers.ChangeBlockedStatus(buyer, function(changed) {
			abc.buyers[index] = changed;
		});
	}

	abc.DeleteBuyer = function(buyer, index) {
		var confirm = $mdDialog.confirm()
			.title('Are you sure you want to delete this buyer?')
			.content('This buyer will be permanently deleted.')
			//.targetEvent(ev)
			.ok('Delete')
			.cancel('Cancel');
		$mdDialog.show(confirm).then(function() {
			// delete
			FactoryBuyers.DeleteBuyer(buyer, function(changed) {
				if (changed)
					abc.buyers.splice(index, 1);
			});
		}, function() {
			//cancel
		});
	}
}

function AdminSellerController(FactorySeller, RDRouter, $timeout, $mdDialog) {
	var asc = this;
	
	

	asc.getSellers = function() {
		FactorySeller.getSellers(function(sellers) {
			asc.sellers = sellers;			
		});
	}

	asc.ViewSellerProducts = function(seller) {
		FactorySeller.getProducts(seller.id, function(products) {
			asc.ProductsShow = true;
			RDRouter.changeRoute('products');
			asc.products = products;
		});
	}

	asc.editProduct = function(product) {
		product.loading = true;
		FactorySeller.editProduct(product, function(response) {
			product.loading = false;
			product.edited = response;
			$timeout(function() {
				delete product.edited;
			}, 1000);
		});
	}

	asc.DeleteSeller = function(seller, index) {
		var confirm = $mdDialog.confirm()
			.title('Are you sure you want to delete this seller?')
			.content('This seller will be permanently deleted.')
			//.targetEvent(angular.element('body'))
			.ok('Delete')
			.cancel('Cancel');
		$mdDialog.show(confirm).then(function() {
			// delete
			FactorySeller.deleteSeller(seller, function(changed) {
				if (changed)
					asc.sellers.splice(index, 1);
			});
		}, function() {
			//cancel
		});
	}

	asc.Return = function() {
		asc.ProductsShow = false;
		RDRouter.changeRoute('');
	}
}

function AdminSettingsController(LocalDatabase, $timeout) {
	var aset = this;
	aset.settings = {};

	aset.getSettings = function() {
		LocalDatabase.getSettings(function(settings) {
			aset.settings = settings;
		});
	}

	aset.saveSettings = function() {
		aset.loading = true;
		LocalDatabase.saveSettings(aset.settings, function(settings) {
			aset.Savedlabel = true;
			aset.loading = false;
			$timeout(function() {
				aset.Savedlabel = false;
			}, 5000);
		});
	}
}