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

	$scope.changeMode = function(mode){
		$scope.currentMode = mode;
	}

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
		possibleFields: [
			{name:"contact_email", title: "Email", type:"text"},
			{name:"total_orders", title: "Total orders", type:"num"},
			{name:"orders", title: "Current orders", type:"num"},
			{name:"reviews", title: "Reviews", type:"num"},
			{name:"avg_score", title: "Avg review stars", type:"num"},
			{name:"blocked", title: "Blocked", type:"bool"},
			{name:"created", title: "Created", type:"date"}
		],
		buildQuery: function(){
			var res = "1=1";
			$scope.filter.fields.forEach(function(fld){
				var value = fld.value;
				if (fld.field.type=="text") {
					value = "'" + ((fld.condition=='like')? "%":"") + value + ((fld.condition=='like')? "%":"")  + "'";
				}
				if (fld.field.type=="date") {
					value = parseInt(new Date(value).getTime() / 1000);
				}
				res += " AND " + fld.field.name + " " + fld.condition + " " + value;
			});
			return res;
		}
	};

	$scope.rerun = function(){
		$scope.tableParams.reload();
	}

	$scope.getDate = function(v){
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

	$scope.gatherQuery = function(params){
		var order = params? params.orderBy(): ["id"];
		if (order.length >0) {
			order = order[0];
			if (order.indexOf("+") >=0) order = order.replace("+","") + " ASC ";
			if (order.indexOf("-") >=0) order = order.replace("-","") + " DESC ";
		} else {
			order = "";
		}
		var q = {
			target:$scope.currentMode,
			limit:params?params.count():99999,
			start:params? (params.count() * (params.page() - 1)) : 0,
			order: order,
			where: $scope.filter.buildQuery()
		}
		return q;
	}

	$scope.tableParams = new NgTableParams({}, {
		getData: function(params) {
			var deferred = $q.defer();
			//params.total(1);
			var q = $scope.gatherQuery(params);
			console.log("PARAMS", params, params.page(),params.count() );

			$rootScope.loadData({action:"query",data:q},function(data){
				//var data = [{name: "Moroni", age: 50} /*,*/];
				params.total(parseInt(data.totals));
				$scope.total = data.totals;
				deferred.resolve(data.rows);
				//console.log(data);
			});
			return deferred.promise;
        }
	});
//	$rootScope.showStatus("OK", "success", "", 600000);

	

});