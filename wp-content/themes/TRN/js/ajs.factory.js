rootApp.factory('postfunction', function($http, $timeout) {
	return function(data, callback, url, method) {
		if (!url) {
			url = templatepath+"/remote.php";
		}
		if (method === undefined) {
			method = "POST";
		}
		if (method == "GET") {

			req = $http({
			url: url + "?" + data,
			method: method
			});

		} else {
			req = $http({
			url: url,
			method: method,
			data: data,
			headers: method == "POST" ? {'Content-Type': 'application/x-www-form-urlencoded'} : {}
			});

		}

		req.success(function(data) {
			if (typeof callback !== 'undefined')
				callback(data);
		});

		req.error(function(data) {
			console.log('post error');
		})
	}
});

rootApp.factory('GetBuyerProducts', function($filter, postfunction) {
	return function(query, returndata) {
		var post = jQuery.param({a: 'GetBuyerProducts', q: query });

		var callback = function(data)
		{
			returndata(data);
		}

		postfunction(post,callback);
	}
});

rootApp.factory('GetBuyerProfile', function($filter, postfunction) {
	return function(query, returndata) {
		var post = jQuery.param({a: 'GetBuyerProfile', q: query });

		var callback = function(data)
		{
			returndata(data);
		}

		postfunction(post,callback, null, 'GET');
	}
});

rootApp.factory('GetSellerProfile', function($filter, postfunction) {
	return function(query, returndata) {
		var post = jQuery.param({a: 'GetSellerProfile', q: query });

		var callback = function(data)
		{
			returndata(data);
		}

		postfunction(post,callback, null, 'GET');
	}
});


rootApp.factory('GetSellers', function($filter, postfunction) {
	return function(query, returndata) {
		var post = jQuery.param({a: 'GetSellers', q: query });

		var callback = function(data)
		{
			returndata(data);
		}

		postfunction(post,callback);
	}
});


rootApp.factory('GetSellerProducts', function($filter, postfunction) {
	return function(query, returndata) {
		var post = jQuery.param({a: 'GetSellerProducts', q: query });

		var callback = function(data)
		{
			returndata(data);
		}

		postfunction(post,callback);
	}
});

rootApp.factory('RequestProduct', function(postfunction) {
	return function(product, returndata) {
		var post = jQuery.param({a: 'RequestProduct', product: product });

		var callback = function(data)
		{
			returndata(data);
		}

		postfunction(post,callback,null, 'GET');
	}
});

rootApp.factory('ConfirmReview', function(postfunction) {
	return function(product, link, returndata) {
		var post = jQuery.param({a: 'ConfirmReview', product: product.id?product.id : product, link: link });

		var callback = function(data)
		{
			returndata(data);
		}

		postfunction(post,callback, null, 'GET');
	}
});

rootApp.factory('Countdown', function($timeout) {
	var timer = this;
	timer.result = "";
	var calculate =  function(endtime) {
		var totalSec = endtime;
		var days = parseInt( totalSec / 86400 );
		var hours = parseInt( totalSec / 3600 ) % 24;
		var minutes = parseInt( totalSec / 60 ) % 60;
		var seconds = totalSec % 60;

		var result = days+"D "+(hours < 10 ? "0" + hours : hours) + ":" + (minutes < 10 ? "0" + minutes : minutes) + ":" + (seconds  < 10 ? "0" + seconds : seconds);
		timer.result = result;
	}

	return function(endtime)
	{
		calculate(endtime);
		return timer.result;
	}
});

rootApp.factory('PercentSavings', function() {
	return function(product)
	{
		var retail = parseFloat(product.price);
		var price = parseFloat(product.discount_price);

		var difference = retail - price;

		var savings = Math.ceil((difference/retail)*100);

		return savings;
	}
})

rootApp.factory('GetHeaderItems', function(postfunction) {
	return function(returndata) {
		var post = jQuery.param({a: 'GetHeaderItems'});

		var callback = function(data)
		{
			returndata(data);
		}

		postfunction(post,callback, null, 'GET');

		//postfunction(post,callback);
	}
});

rootApp.factory('GetBuyerFullInfo', function(postfunction) {
	return function(returndata) {
		var post = jQuery.param({a: 'GetBuyerFullInfo'});

		var callback = function(data)
		{
			returndata(data);
		}

		postfunction(post,callback);
	}
});

rootApp.factory('getParameterByName', function() {
	return function(name) {
		name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
		var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
			results = regex.exec(location.search);
		return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
	}
});

rootApp.factory('MyFactorySeller', ['LocalDatabase', MyFactorySeller])

function MyFactorySeller(LocalDatabase) {
	return {
		hello: 'hello world'
	}
}

rootApp.factory('RDRouter', [FactoryRouter])

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