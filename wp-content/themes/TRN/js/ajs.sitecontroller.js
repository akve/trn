var showStatus = function(growl, message, level, title, ttl) {
			console.log(message);
	        var params = {ttl: 4000, disableCountDown: false};
	        if (title) params.title = title;
	        if (ttl) params.ttl = ttl;
	        switch (level) {
	            case "error":
	                growl.error(message, params);
	                break;
	            case "warning":
	                growl.warning(message, params);
	                break;
	            case "info":
	                growl.info(message, params);
	                break;
	            default:
	                growl.success(message, params);
	                break;
	        }
	    };

/**
 *	Login Controller
 *	Controlls all login function for both buyers and sellers
 *
 */
function LoginController(postfunction, $mdToast, getParameterByName) {
	var trnl = this;

	// set base values
	trnl.PhoneSent = false;
	trnl.CodeVerified = false;

	// a list of products on the front page
	trnl.frontpageproducts = [
		{price: 39.99, sale: 3.99, percent: 90.00, img: '/templates/img/Digital-Weight-Loss-Scale.jpg', name: 'Digital Weight Scale'},
		{price: 39.99, sale: 1.99, percent: 95.00, img: '/templates/img/Mini-Espresso-Machine.jpg', name: 'Mini Espresso Machine'},
		{price: 99.99, sale: 3.99, percent: 34.99, img: '/templates/img/Organic-Cotton-Sheet-Set.jpg', name: 'Organic Cotton Sheets'},
		{price: 149.99, sale: 2.99, percent: 34.99, img: '/templates/img/Laptop-Android.jpg', name: 'Laptop Android'},
		{price: 179.99, sale: 0.99, percent: 34.99, img: '/templates/img/Unlocked-Smart-Phones.jpg', name: 'Unlocked Smart Phones'},
		{price: 59.99, sale: 0.99, percent: 34.99, img: '/templates/img/Outdoor-Espresso-Set.jpg', name: 'Outdoor Espresso Set'},
		{price: 89.99, sale: 4.49, percent: 34.99, img: '/templates/img/Unlocked-Android-Smart-Phone.jpg', name: 'Unlocked Android Smart Phones'},
		{price: 17.99, sale: 1.99, percent: 34.99, img: '/templates/img/product8.jpg', name: 'Ping Pong Bats'},
	];

	if (getParameterByName('join') == true)
		trnl.JoinClass = "join-now";
	else
		trnl.JoinClass = "";

	trnl.calcPercent = function(product) {
		return Math.ceil((1 - product.sale/product.price)*100);
	}
	trnl.ForgotPasswordSubmit = function() {
		trnl.errormessage = false;
		trnl.passwordResetMessageClass = false;
		trnl.passwordResetMessage = false;
		trnl.ForgotPasswordLoading = true;

		var post = jQuery.param({
			a: "SendForgotPassword",
			email: trnl.forgot.Email
		});

		var callback = function(data) {
			trnl.ForgotPasswordLoading = false;
			if (data.success) {
				trnl.passwordResetMessage = "Instructions on how to reset your password has been sent to your email address";
			} else {
				trnl.passwordResetMessageClass = 'error';
				trnl.passwordResetMessage = data.error;
			}
		};

		postfunction(post, callback);
	}

	trnl.ResetPassword = function() {
		trnl.errormessage = false;
		trnl.passwordResetMessageClass = false;

		var post = jQuery.param({
			a: "ResetPassword",
			key: getParameterByName('key'),
			q: getParameterByName('q'),
			password: trnl.reset.Password,
		});

		var callback = function(data) {
			console.log('data', data);
			if (data.success) {
				window.location.href = basepath + "buyer-login/";
			} else {
				trnl.passwordResetMessageClass = 'error';
				trnl.errormessage = data.error;
			}
		};

		postfunction(post, callback);
	}

	// can be used for both sign up functinos but only used for one right now
	trnl.SendVerification = function() {
		trnl.PhoneError = false;
		if (
			(typeof trnl.Phone1 === 'undefined' || trnl.Phone1 == "" || trnl.Phone1.length < 3) ||
			(typeof trnl.Phone2 === 'undefined' || trnl.Phone2 == "" || trnl.Phone2.length < 3) ||
			(typeof trnl.Phone3 === 'undefined' || trnl.Phone3 == "" || trnl.Phone3.length < 4)
		) {
			trnl.PhoneError = "Please Enter a Valid Phone Number";
			return false;
		}

		trnl.Phone = trnl.Phone1 + "-" + trnl.Phone2 + "-" + trnl.Phone3

		var post = jQuery.param({
			a: 'SendVerification',
			phone: trnl.Phone
		});

		var callback = function(data) {
			trnl.PhoneSentLoading = false;
			if (data.success)
				trnl.PhoneSent = true;
			else
				trnl.PhoneError = data.error;
		}

		trnl.PhoneSentLoading = true;
		postfunction(post, callback);
	}

	//can be used for both sign up functions but only used for buyers
	trnl.ConfirmVerification = function() {
		trnl.CodeVerifiedError = false;
		if (typeof trnl.Code === 'undefined' || trnl.Code == "") {
			trnl.CodeVerifiedError = true;
			return false;
		}

		var post = jQuery.param({
			a: 'ConfirmVerification',
			code: trnl.Code,
			phone: trnl.Phone
		});

		var callback = function(data) {
			trnl.CodeVerifiedLoading = false;
			if (data)
				trnl.CodeVerified = true;
			else
				trnl.CodeVerifiedError = true;
		}

		trnl.CodeVerifiedLoading = true;
		postfunction(post, callback);
	}

	/**
	 *	Buyer specific functions
	 */
	trnl.CreateBuyerAccount = function() {
		trnl.CreateAccountError = false;
		if (typeof trnl.account === 'undefined')
			return false;

		var post = jQuery.param({
			a: 'CreateBuyerAccount',
			code: trnl.Code,
			phone: trnl.Phone,
			account: trnl.account
		});

		var callback = function(data) {
			trnl.CreateAccountLoading = false;
			if (data)
				window.location.href = basepath + "buyer-homepage/?new=" + data;
			else
				trnl.CreateAccountError = true;
		}

		trnl.CreateAccountLoading = true;
		postfunction(post, callback);
	}

	trnl.BuyerLogin = function() {
		trnl.LoginError = false;
		trnl.LoginLoading = true;

		if (typeof trnl.login === 'undefined')
			return false;

		var post = jQuery.param({
			a: 'BuyerLogin',
			account: trnl.login
		});

		var callback = function(data) {
			trnl.LoginLoading = false;
			if (data)
				window.location.href = basepath + "buyer-homepage/";
			else
				trnl.LoginError = "Incorrect Login Information";
		}

		postfunction(post, callback);
	}

	/**
	 *	Seller specific functions
	 */
	trnl.CreateSellerAccount = function() {
		if (typeof trnl.account === 'undefined')
			return false;

		var post = jQuery.param({
			a: 'CreateSellerAccount',
			account: trnl.account
		});

		var callback = function(data) {
			if (data)
				window.location.href = basepath + "seller-products/";
		}

		postfunction(post, callback);
	}

	trnl.SellerLogin = function() {
		trnl.LoginError = false;
		trnl.LoginLoading = true;

		if (typeof trnl.login === 'undefined')
			return false;

		var post = jQuery.param({
			a: 'SellerLogin',
			account: trnl.login
		});

		var callback = function(data) {
			trnl.LoginLoading = false;
			if (data)
				window.location.href = basepath + "seller-products/";
			else
				trnl.LoginError = "Incorrect Login Information";
		}

		postfunction(post, callback);
	}

	trnl.ContactUs = function() {
		trnl.Loading = true;
		trnl.message = false;

		var post = jQuery.param({
			a: 'ContactUs',
			form: trnl.ContactForm
		});

		var callback = function(data) {
			trnl.Loading = false;
			if (data.success)
				trnl.message = data.message;
			else
				trnl.message = data.error;
		}

		postfunction(post, callback);
	}
}

/**
 *	Header Controller
 *	All functions related to the header
 *
 */
function HeaderController(GetHeaderItems, postfunction, getParameterByName, $rootScope) {
	var trnh = this;

	trnh.basepath = basepath;

	GetHeaderItems(function(data) {
		if (data.menuitems) {
			trnh.menu = data.menuitems;
			$rootScope.user = data.userdata;
			console.log($rootScope.user);
		} else {
			trnh.menu = data;
		}
	});
}

/**
 *	Buyer Controller
 *	PROFILECONTROLLER is not used ) 
 *
 */

function BuyerProfileController(postfunction, GetBuyerProducts, GetBuyerProfile, GetHeaderItems, $mdDialog, $mdMedia, PercentSavings, getParameterByName, GetSellers, RDRouter) {
	var preferences = this;
	console.log("???");

	GetHeaderItems(function(data) {
		preferences.user = data.userdata;
		console.log(preferences.user);
	});

}


function BuyerController(postfunction, GetBuyerProducts, GetBuyerProfile, GetHeaderItems, $mdDialog, $mdMedia, PercentSavings, getParameterByName, RDRouter, growl) {
	var trnb = this;
	var preferences = this;


	var initData = function(buyerproducts){
		if (!buyerproducts) buyerproducts = trnb.BuyerProducts;
		preferences.availableOrders = 5;
		preferences.avgReview = 0;
		if (preferences.tmpProfile.products && preferences.tmpProfile.products.length >0) {
			var reviewSum = 0;
			var reviewNum = 0;
			preferences.tmpProfile.products.forEach(function(product){
				product.review_score = parseInt(product.review_score);
				product.got_review = parseInt(product.got_review);
				if (product.got_review && product.review_score ) {
					reviewSum += product.review_score;
					reviewNum += 1;
					var d = new Date(parseInt("" + product.got_review + "000"));
					product.reviewDate = d.toLocaleDateString();
				} else {
					preferences.availableOrders -= 1;
				}
				var d = new Date(parseInt("" + product.inserted + "000"));
				product.insertedDate = d.toLocaleDateString();

				buyerproducts.forEach(function(bp) {
					if (bp.id == product.id) {
						if (product.got_review) {
							bp.state = "reviewed"
						} else {
							bp.state = "ordered";
						}
					}
				})

			});
			if (reviewNum > 0) {
				preferences.avgReview = parseInt(reviewSum / reviewNum * 10) / 10;
			}
		}
		preferences.profile = preferences.tmpProfile;
	}
	
	var init = function(){
		// first thing we do on load is get the products
		GetHeaderItems(function(data) {
			preferences.user = data.userdata;
	//		console.log(preferences.user);
			GetBuyerProducts("", function(data) {
				GetBuyerProfile("", function(profile) {
					//console.log(sellerData)
					trnb.tmpProfile = profile;
					trnb.backup = data;
					initData(data);
					trnb.BuyerProducts = data;
					//trnb.BuyerProducts = data;
					//trnb.backup = data;
				});
			});
		});
	}
	init();


	trnb.ConfirmReview = function(row){
		//console.log("...", row);

		if ($mdMedia('sm') || $mdMedia('xs'))
			var useFullScreen = true;
		else
			var useFullScreen = false;

		$mdDialog.show({
			controller: ConfirmReviewController,
			controllerAs: 'pc',
			templateUrl: templatepath + '/templates/ConfirmReviewDialog.html',
			parent: angular.element(document.body),
			locals: {
				product: row,
				onclose: function(a) {
					console.log("CLOSE", a)
				}
			},
			clickOutsideToClose: true,
			fullscreen: useFullScreen
		});


		/*ConfirmReview(row.id, function(data) {
			//pc.ConfirmReviewLoading = false;
			if (data) {
				trnb.RequestResults = 'We have found your review!  You can now request another product.';
				trnb.RequestResultsClass = 'success';
				init();
			} else {
				trnb.RequestResults = 'We could not find your review.  Please contact us if you think this is a mistake.';
				trnb.RequestResultsClass = 'error';
			}
		});*/
	}

	trnb.changePassword = function(){

		if (!preferences.new_password || (preferences.new_password != preferences.confirm_new_password)){
			showStatus(growl, "Passwords do not match", "error");
			return;
		}
		if (preferences.new_password.length < 8){
			showStatus(growl, "Password should be at least 8 characters", "error");
			return;
		}
		var post = jQuery.param({
			a: 'ChangePasswordBuyer',
			password: preferences.new_password
		});

		trnb.Loading = true;
		var callback = function(data) {
			trnb.Loading = false;
			if (!data.error) {
				showStatus(growl, "Successfully changed. You'll need to re-login with your new password in 5 seconds", "success");
				setTimeout(function(){
					window.location.href = basepath + "buyer-login/";
				},5000);
			} else {
				showStatus(growl, "Error: " +data.error, "error");
			}
			/*if (typeof data.error === 'undefined')
				window.location.href = basepath + "buyer-homepage/";
			else
				trnb.LoginError = data.error;*/
		}

		postfunction(post, callback);
	}
	

	trnb.SaveBuyer = function() {
		trnb.Loading = true;

		var buyerO = preferences.user;
		var buyer = {};
		
		var copy = ["id", "contact_email", "phone", "first_name", "last_name", "amazonid"];
		for (var key in buyerO) {
			if (copy.indexOf(key)>=0) buyer[key] = buyerO[key];
		}

		var post = jQuery.param({
			a: 'SaveBuyer',
			buyer: buyer
		});

		var callback = function(data) {
			trnb.Loading = false;
			if (typeof data.error === 'undefined') {
				showStatus(growl, "Successfully changed. ", "success");
			}
			else {
				showStatus(growl, "Error: " +data.error, "error");
			}
		}

		postfunction(post, callback);
	}

	trnb.NotificationMessage = false;
	trnb.getNotification = function() {
		if (parseFloat(getParameterByName('new')) > 0) {
			trnb.NotificationMessage = "Thank you for signing up with TRN. An email has been sent to you.  Check your email for email and follow the link to complete your account setup.  If you don’t see the email from us, check your spam folder.  Reach us at care@trustreviewnetwork.com with any questions you may have.";
		}

		console.log(trnb.NotificationMessage);
		return false;
	}

	trnb.ViewProductDialog = function(product) {
		// determine if we should use fullscreen
		if ($mdMedia('sm') || $mdMedia('xs'))
			var useFullScreen = true;
		else
			var useFullScreen = false;

		$mdDialog.show({
			controller: ProductController,
			controllerAs: 'pc',
			templateUrl: templatepath + '/templates/ProductDialog.html',
			parent: angular.element(document.body),
			locals: {
				product: product,
				onclose: function(a) {
					console.log("CLOSE", a)
				}
			},
			clickOutsideToClose: true,
			fullscreen: useFullScreen
		});
	}

	trnb.PercentSavings = function(product) {
		return PercentSavings(product);
	}

	trnb.FilterResults = function() {
		GetBuyerProducts(trnb.Search, function(data) {
			trnb.BuyerProducts = data;
		});
	}
}

function SellerController(postfunction, $mdDialog, $mdMedia, PercentSavings, getParameterByName, Countdown, $timeout) {
	var trns = this;

	trns.ASINSearch = function(asin) {
		trns.ASINError = false;
		trns.ASINLoading = true;

		var post = jQuery.param({
			a: 'ASINSearch',
			asin: asin
		});

		var callback = function(data) {
			trns.ASINLoading = false;
			if (data.product)
				trns.product = data.product;
			else
				trns.ASINError = true;
		}

		postfunction(post, callback);
	}

	trns.ProcessProduct = function(product) {
		trns.AddingError = false;
		trns.AddingLoading = true;

		var post = jQuery.param({
			a: 'ProcessProduct',
			product: product
		});

		var callback = function(data) {
			trns.AddingLoading = false;
			if (data.success)
				window.location.href = basepath + "seller-products/";
			else
				trns.AddingError = data.error;
		}

		postfunction(post, callback);
	}

	trns.GetProducts = function() {
		trns.LoadingProducts = true;

		var post = jQuery.param({
			a: 'GetProducts'
		});

		var callback = function(data) {
			trns.LoadingProducts = false;
			trns.products = data;
		}

		postfunction(post, callback);
	}

	trns.GetProductEdit = function() {
		var productid = getParameterByName('productid');

		if (typeof parseFloat(productid) !== 'number' || parseFloat(productid) < 1)
			window.location.href = basepath + "seller-products/";

		var post = jQuery.param({
			a: 'GetProduct',
			productid: productid
		});

		var callback = function(data) {
			// we have to edit the dates a little bit
			if (data.startdate != '')
				data.startdate = new Date(data.startdate);

			if (data.enddate != '')
				data.enddate = new Date(data.enddate);


			trns.product = data;
		}

		postfunction(post, callback);

	}

	trns.PercentSavings = function(product) {
		return PercentSavings(product);
	}

	trns.CountDown = function(endtime, idx) {
		var countdown = function(endtime, idx) {
			$timeout(function() {
				endtime = endtime - 1;
				trns.products[idx].time_remaining_show = Countdown(endtime);
				countdown(endtime, idx);
			}, 1000);
		}

		console.log('endtime', endtime);

		if (endtime > 0)
			countdown(endtime, idx);
		else if (endtime == 0)
			trns.products[idx].time_remaining_show = 'No Expiry';
		else
			trns.products[idx].time_remaining_show = 'EXPIRED!';
	}

	trns.ChangeActivity = function(product) {
		var post = jQuery.param({
			a: 'ChangeActivity',
			product: product
		});

		var callback = function(data) {
			product.active = data.active;
		}

		postfunction(post, callback);
	}

	trns.countCoupons = function() {
		if (typeof trns.product.SUCC === 'undefined' || trns.product.SUCC == "")
			return 0;

		var count = trns.product.SUCC.split("\n");

		return count.length;
	}
}
/**
 *	Product Controller
 *	For the product related dialogs
 *
 */
function ProductController($mdDialog, product, $mdMedia, RequestProduct, PercentSavings, ConfirmReview) {
	var pc = this;

	pc.product = product;

	pc.RequestProduct = function() {
		pc.RequestResults = false;
		pc.RequestResultsClass = false;
		pc.RequestLoading = true;
		RequestProduct(pc.product, function(data) {
			pc.RequestLoading = false;
			if (data.response) {
				pc.RequestResults = 'Your coupon code has been sent!';
				pc.RequestResultsClass = 'success';
			} else {
				pc.RequestResults = data.message;
				pc.RequestResultsClass = 'error';
			}
		});
	}

	pc.ConfirmReview = function(row){
		//console.log("...", row);

		if ($mdMedia('sm') || $mdMedia('xs'))
			var useFullScreen = true;
		else
			var useFullScreen = false;

		$mdDialog.show({
			controller: ConfirmReviewController,
			controllerAs: 'pc',
			templateUrl: templatepath + '/templates/ConfirmReviewDialog.html',
			parent: angular.element(document.body),
			locals: {
				product: product,
				onclose: function(a) {
					console.log("CLOSE", a)
				}
			},
			clickOutsideToClose: true,
			fullscreen: useFullScreen
		});
	}

	pc.generateLink = function(product) {
		// link structure = http://www.amazon.com/{{ name }}/dp/{{ asin }}/?ie=UTF8&qid={{ timestamp }}&keyword={{ keywords }}
		var cleaned_name = product.product_name.replace(/[^A-Za-z0-9]/g, '-');
		var timestamp = Math.round((new Date().getTime())/1000);
		var keywords = product.keywords.replace(/\,/g, '+');

		return "http://www.amazon.com/"+cleaned_name+"/dp/"+product.asin+"/?ie=UTF8&qid="+timestamp+"&keyword="+keywords+"&associate="+product.associatecode;
	}

	pc.PercentSavings = function(product) {
		return PercentSavings(product);
	}

	pc.hide = function() {
		$mdDialog.hide();
	}

	pc.cancel = function() {
		$mdDialog.cancel();
	}

	pc.answer = function(answer) {
		$mdDialog.hide(answer);
	}
}

/**
 *	Product Controller
 *	For the product related dialogs
 *
 */
function ConfirmReviewController($mdDialog, $mdMedia, product, onclose, ConfirmReview, growl) {
	var pc = this;

	pc.product = product;

	pc.link = "";
	pc.showManual = false;


	pc.ConfirmReview = function(isManual) {
		if (isManual && !pc.link) {
			showStatus(growl, "Please paste your link", "error");
			return;
		}
		pc.RequestResults = false;
		pc.RequestResultsClass = false;
		pc.ConfirmReviewLoading = true;

		ConfirmReview(pc.product, pc.link, function(data) {
			pc.ConfirmReviewLoading = false;
			if (data) {
				pc.RequestResults = 'We have found your review!  You can now request another product.';
				pc.RequestResultsClass = 'success';
				pc.showManual = false;
			} else {
				pc.showManual = true;
				if (isManual) {
					pc.RequestResults = 'We could not find your review. Please try manual confirmation.';
				} else {
					pc.RequestResults = 'We could not find your review. Please contact us if you think this is a mistake.';
				}
				pc.RequestResultsClass = 'error';
			}
		});
	}

	pc.ConfirmReviewOK = function(){
		// onbutton click
		pc.ConfirmReview(true);
	}

	pc.ConfirmReview(false);

	pc.hide = function() {
		$mdDialog.hide();
	}

	pc.cancel = function() {
		$mdDialog.cancel();
	}

	pc.answer = function(answer) {
		$mdDialog.hide(answer);
	}
}