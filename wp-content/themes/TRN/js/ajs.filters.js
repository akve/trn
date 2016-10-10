rootApp.filter('SearchFilter', function() {
	return function(items, query) {

		var filtered = [];

		angular.forEach(items, function(item, i) {
			if (item.name.indexOf(query))
				filtered.push(item);
		});

		return filtered;
	}
})