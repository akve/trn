function FactorySeller(LocalDatabase) {
	return {
		getSellers: function(callback) {
			LocalDatabase.getSellers(function(sellers) {
				callback(sellers);
			});
		},
		getProducts: function(sellerid, callback) {
			LocalDatabase.getSellerProducts(sellerid, function(products) {
				callback(products);
			});
		},
		editProduct: function(product, callback) {
			LocalDatabase.editProduct(product, function(response) {
				callback(response);
			});
		},
		deleteSeller: function(seller, callback) {
			LocalDatabase.deleteSeller(seller, function(response) {
				callback(response);
			});	
		}
	}
}