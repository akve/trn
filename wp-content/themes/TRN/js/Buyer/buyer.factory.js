function FactoryBuyers(LocalDatabase) {
	return {
		getBuyers: function(callback) {
			LocalDatabase.getBuyers(function(buyers) {
				callback(buyers);
			});
		},
		ChangeStatus: function(buyer, callback) {
			LocalDatabase.ChangeBuyerStatus(buyer, function(changed) {
				callback(changed);
			});
		},
		ChangeBlockedStatus: function(buyer, callback) {
			LocalDatabase.ChangeBlockedStatus(buyer, function(changed) {
				callback(changed);
			});
		},
		DeleteBuyer: function(buyer, callback) {
			LocalDatabase.DeleteBuyer(buyer, function(changed) {
				callback(changed);
			});
		}
	}
}