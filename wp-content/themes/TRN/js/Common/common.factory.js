function FactoryPostFunction($http, $timeout) {
	return function(data, callback, url, method, headers) {
		if (url === undefined) {
			url = templatepath+"/remote.php";
		}
		if (typeof method === 'undefined') {
			method = "POST";
		}
		if (typeof headers == 'undefined') {
			headers = {
				'Content-Type': 'application/x-www-form-urlencoded'
			};
		}
		var parameters = {
			url: url,
			method: method,
			headers: headers,
		};

		if (typeof data !== 'undefined') {
			parameters.data = data;
		}

		var req = $http(parameters);

		req.then(
			// success
			function(data) {
				if (typeof callback !== 'undefined')
					callback(data.data);
			},
			// error 
			function(data) {
				console.log('post error');
			}
		);
	};
};

function FactoryPostWithFile($http) {
	return function(data, callback, url, method) {
		if (url === undefined) {
			url = "/remote.php";
		}
		if (method === undefined) {
			method = "POST";
		}

		var fd = new FormData();
		angular.forEach(data, function(value, key) {
			fd.append(key, value);
		});

		var req = $http.post(url, fd, {
			transformRequest: angular.identity,
			headers: {
				'Content-Type': undefined
			}
		})

		req.then(
			// success
			function(data) {
				if (typeof callback !== 'undefined')
					callback(data.data);
			},
			// error 
			function(data) {
				console.log('post error');
			}
		);
	};
};

function FactoryLocalDatabase(PostFunction) {
	return {
		//seller functions
		getSellers: function(callback) {
			var post = {
				a: 'getSellers',
			};
			this.postDatabase(post, callback);
		},
		deleteSeller: function(seller, callback) {
			var post = {
				a: 'deleteSeller',
				seller: seller
			};
			this.postDatabase(post, callback);
		},
		getSellerProducts: function(sellerid, callback) {
			var post = {
				a: 'getSellerProducts',
				id: sellerid,
			};
			this.postDatabase(post, callback);
		},
		editProduct: function(product, callback) {
			var post = {
				a: 'editProduct',
				product: product,
			};
			this.postDatabase(post, callback);
		},
		//buyer functions
		getBuyers: function(callback) {
			var post = {
				a: 'getBuyers',
			};
			this.postDatabase(post, callback);
		},
		ChangeBuyerStatus: function(buyer, callback) {
			var post = {
				a: "ChangeStatus",
				buyer: buyer,
			};
			this.postDatabase(post, callback);
		},
		ChangeBlockedStatus: function(buyer, callback) {
			var post = {
				a: "ChangeBlockedStatus",
				buyer: buyer,
			};
			this.postDatabase(post, callback);
		},
		DeleteBuyer: function(buyer, callback) {
			var post = {
				a: "DeleteBuyer",
				buyer: buyer,
			};
			this.postDatabase(post, callback);
		},
		getSettings: function(callback) {
			var post = {
				a: "getSettings",
			};
			this.postDatabase(post, callback);
		},
		saveSettings: function(settings, callback) {
			var post = {
				a: "saveSettings",
				settings: settings,
			};
			console.log('post', post);
			this.postDatabase(post, callback);
		},
		postDatabase: function(parameters, callback) {
			var post = jQuery.param(parameters);
			PostFunction(post, function(data) {
				callback(data);
			});
		}
	}
};

function FactoryRouter() {
	var Route = this;
	return {
		parseQuery: function(string) {
			var parameters = string.split('&');
			var query = {};
			angular.forEach(parameters, function(p, k) {
				var par = p.split("=");
				query[par[0]] = par[1];
			});

			return query;
		},
		getRoute: function(callback) {
			var hash = window.location.hash;
			// we have to remove the query from the hash
			hash = hash.split("?")[0];

			Route.location = hash.replace(/[^A-Za-z]/g, '');

			// set a default here
			if (typeof Route.location === 'undefined' || Route.location == "")
				Route.location = "AddProject";

			if (typeof callback !== 'undefined')
				callback(Route);
		},
		changeRoute: function(route) {
			history.pushState(route, document.title, window.location.pathname + window.location.search + '#/' + route);
		},
		cleanURL: function(url) {
			url = url.replace('https://', '');
			url = url.replace(window.location.hash, '');
			url = url.replace('http://', '');

			url = url.split('/');

			//clear out filters
			for (var key = url.length - 1; key >= 0; key--) {
				var urlf = url[key];
				if (urlf !== undefined && urlf !== "" && (urlf.indexOf('f:') > -1 || urlf.indexOf('r:') > -1)) {
					url.splice(key, 1);
				}
			}

			return url;
		}
	};
};