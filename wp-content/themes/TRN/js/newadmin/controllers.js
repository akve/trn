trnadmin.controller('BuyerDetailsController', function( $mdDialog, $q, $scope, $timeout, $rootScope,  NgTableParams, buyer, history) {
	console.log(buyer, history);
	$scope.buyer = buyer;
	$scope.history = history;
	$scope.products = history.products;

	if (history.products && history.products.length >0) {
		var reviewSum = 0;
		var reviewNum = 0;
		history.products.forEach(function(product){
			product.review_score = parseInt(product.review_score);
			product.got_review = parseInt(product.got_review);
			if (product.got_review && product.review_score ) {
				reviewSum += product.review_score;
				reviewNum += 1;
				var d = new Date(parseInt("" + product.got_review + "000"));
				product.reviewDate = d.toLocaleDateString();
			} else {
				$scope.availableOrders -= 1;
			}
			var d = new Date(parseInt("" + product.inserted + "000"));
			product.insertedDate = d.toLocaleDateString();
			if (product.got_review) {
						product.state = "reviewed"
					} else {
						product.state = "ordered";
					}
		});
		if (reviewNum > 0) {
			$scope.avgReview = parseInt(reviewSum / reviewNum * 10) / 10;
		}
	}
	$scope.hide = function(){
		$mdDialog.hide();
	}
});

trnadmin.controller('AdminController', function( $mdDialog, $q, $scope, $timeout, $rootScope,  NgTableParams) {

	$scope.currentMode = "buyers";



	$scope.filter = {
		fields: [],
		add :  function(){
			$scope.filter.possibleFields.forEach(function(f){
				console.log($scope.filter.addAs, f.name);
				if (f.name == $scope.filter.addAs) {
					$scope.filter.fields.push({field:f, condition:"=", value:""});
				}
			})
			$scope.filter.addAs = "";
		},
		remove: function(index) {
			$scope.filter.fields.splice(index,1);
		},
		possibleOptions: [
			{name:"=", value:"="},
			{name:">", value:">"},
			{name:"<", value:"<"},
			{name:"like", value:"like"}
		],
		addAs: "",
		possibleFields: [],
		buildQuery: function(){
			var res = "1=1";
			$scope.filter.fields.forEach(function(fld){
				var value = fld.value;
				if (fld.field.type=="text") {
					value = "'" + ((fld.condition=='like')? "%":"") + value + ((fld.condition=='like')? "%":"")  + "'";
				}
				if (fld.field.type=="date") {
					//value = parseInt(new Date(value).getTime() / 1000);
					var v = value.split("/");
					var y = new Date().getYear();
					if (y>2000) y = y-2000;
					if (y>100) y = y-100;
					y = y+2000;

					if (v.length == 3) {
						y = parseInt(v[2]);
						if (y<100) y = y+2000;
					}
					var m = parseInt(v[0]);
					var d = parseInt(v[1]);
					value = parseInt(new Date("" + y + "-" + m + "-" + d).getTime() / 1000);
				}
				res += " AND " + fld.field.name + " " + fld.condition + " " + value;
			});
			return res;
		}
	};

	$scope.rerun = function(){
		if ($scope.currentMode == "buyers") $scope.buyersParams.reload();
		if ($scope.currentMode == "sellers") $scope.sellersParams.reload();
		if ($scope.currentMode == "products") $scope.productsParams.reload();
		if ($scope.currentMode == "reviews") $scope.reviewsParams.reload();
	}

	$scope.changeMode = function(mode, skipRefresh, filterFields){
		$scope.currentMode = mode;
		if (mode == "buyers") {
			$scope.filter.possibleFields = [
				{name:"contact_email", title: "Email", type:"text"},
				{name:"name", title: "Name", type:"text"},
				{name:"total_orders", title: "Total orders", type:"num"},
				{name:"orders", title: "Current orders", type:"num"},
				{name:"reviews", title: "Reviews", type:"num"},
				{name:"avg_score", title: "Avg review stars", type:"num"},
				{name:"blocked", title: "Blocked", type:"bool"},
				{name:"created", title: "Created", type:"date"}
			];
		}
		if (mode == "sellers") {
			$scope.filter.possibleFields = [
				{name:"contact_email", title: "Email", type:"text"},
				{name:"name", title: "Name", type:"text"},
				{name:"company", title: "Company", type:"text"},
				{name:"phone", title: "Phone", type:"text"},
				{name:"contact_email", title: "Email", type:"text"},
				{name:"created", title: "Created", type:"date"},
				{name:"contact_email", title: "Email", type:"text"},
				{name:"blocked", title: "Blocked", type:"bool"},
				{name:"approval", title: "Approved", type:"bool"},
				{name:"pause", title: "Paused", type:"bool"}
			];
		}
		if (mode == "products") {
			$scope.filter.possibleFields = [
				{name:"contact_email", title: "Seller email", type:"text"},
				{name:"product_name", title: "Name", type:"text"},
				{name:"company", title: "Seller Company", type:"text"},
				{name:"seller_id", title: "Seller ID", type:"num"},
				{name:"asin", title: "ASIN", type:"text"},
				{name:"active", title: "Active/blocked", type:"bool"},
				{name:"Pause", title: "Paused", type:"bool"}
			];
		}
		if (mode == "reviews") {
			$scope.filter.possibleFields = [
				{name:"contact_email", title: "Buyer email", type:"text"},
				{name:"buyer_id", title: "Buyer id", type:"text"},	
				{name:"product_name", title: "Name", type:"text"},
				{name:"seller_id", title: "Seller ID", type:"num"},
				{name:"Company", title: "Seller Company", type:"text"},
				{name:"asin", title: "ASIN", type:"text"},
				{name:"inserted", title: "Order date", type:"date"},
				{name:"got_review", title: "Review date", type:"date"},
				{name:"review_score", title: "Review score", type:"num"}
			];
		}
		if (!filterFields) {
			$scope.filter.fields = [];
		} else {
			filterFields.forEach(function(f){
				$scope.filter.possibleFields.forEach(function(fp) {
					if (fp.name == f.field) {
						f.field = fp;
					}
				})
			})
			$scope.filter.fields = filterFields;
		}
		if (!skipRefresh) $scope.rerun();
	}

	$scope.getDate = function(v){
		if (!v || v == "0") return "";
		var d = new Date(parseInt("" + v + "000"));
		return d.toDateString();
		//new Date(parseInt("" + user.created + "000")).toLocalDateString()
	}

	$scope.changeBuyerState = function(id, data) {
		$rootScope.loadData({action:"update", data:{id:id, mode:$scope.currentMode, changes:data}},function(data){
				$rootScope.showStatus("Changed", "success");
				$scope.rerun();
			});
	}
	$scope.changeSellerState = function(id, data) {
		$rootScope.loadData({action:"update", data:{id:id, mode:$scope.currentMode, changes:data}},function(data){
				$rootScope.showStatus("Changed", "success");
				$scope.rerun();
			});
	}

	$scope.deleteSingle = function(user, mode) {
		if (confirm("Really delete?")) {
		$rootScope.loadData({action:"delete", data:{id:user.id, user_id: user.user_id, mode:mode}},function(data){
				$rootScope.showStatus("Removed", "success");
				$scope.rerun();
			});
		}
	}



	$scope.getBuyerDetails = function(buyer) {
		$rootScope.loadData({action:"get_buyer", data:{id:buyer.id}},function(data){
			$mdDialog.show({
				controller: 'BuyerDetailsController',
				controllerAs: 'pc',
				templateUrl: '/wp-content/themes/TRN/js/newadmin/buyer.html',
				parent: angular.element(document.body),
				locals: {
					buyer: buyer,
					history:data,
					onclose: function(a) {
						console.log("CLOSE", a)
					}
				},
				clickOutsideToClose: true,
				fullscreen: false
			});
			/*var modalInstance = $mdDialog.open({
                    templateUrl: '/wp-content/themes/TRN/js/newadmin/buyer.html',
                    controller: 'buyerDetailsController',
                    size: 'lg',
                    backdrop: true,
                    windowClass: 'no-animation',
                    resolve: {
                        options: function() {
                            return {
                                buyer: buyer,
                                history: data
                            }
                        }
                    }
                });*/
		});
	}

	$scope.exportToCsv = function(){
		var q = $scope.gatherQuery();
		q.mode = "csv";
		$rootScope.loadData({action:"query",data:q},function(data){
            var blob = new Blob([data], {type: "text/plain;charset=utf-8"});
            saveAs(blob, 'data.csv');
			//var data = [{name: "Moroni", age: 50} /*,*/];
			//console.log(data);
		});
	}

	//$scope.filterSettings = ;

	$scope.gatherQuery = function(params, mode){
		if (!mode) mode = $scope.currentMode;
		var order = params? params.orderBy(): ["id"];
		if (order.length >0) {
			order = order[0];
			if (order.indexOf("+") >=0) order = order.replace("+","") + " ASC ";
			if (order.indexOf("-") >=0) order = order.replace("-","") + " DESC ";
		} else {
			order = "";
		}
		var q = {
			target:mode,
			limit:params?params.count():99999,
			start:params? (params.count() * (params.page() - 1)) : 0,
			order: order,
			where: $scope.filter.buildQuery()
		}
		return q;
	}

	$scope.onGetData = function(params, mode) {
		var deferred = $q.defer();
		//params.total(1);
		var q = $scope.gatherQuery(params, mode);
		console.log("PARAMS", mode, $scope.currentMode );

		if (mode == $scope.currentMode) {
			$rootScope.loadData({action:"query",data:q},function(data){
				//var data = [{name: "Moroni", age: 50} /*,*/];
				params.total(parseInt(data.totals));
				console.log("?", data.totals);
				$scope.total = data.totals;
				deferred.resolve(data.rows);
				//console.log(data);
			});
		} else {
			deferred.resolve([]);
		}
		return deferred.promise;
    };

	$scope.buyersParams = new NgTableParams({}, {getData:function(params) {return $scope.onGetData(params, "buyers")}});
	$scope.sellersParams = new NgTableParams({}, {getData:function(params) {return $scope.onGetData(params, "sellers")}});
	$scope.productsParams = new NgTableParams({}, {getData:function(params) {return $scope.onGetData(params, "products")}});
	$scope.reviewsParams = new NgTableParams({}, {getData:function(params) {return $scope.onGetData(params, "reviews")}});

	$scope.changeMode("buyers", true);

//	$rootScope.showStatus("OK", "success", "", 600000);

	

});