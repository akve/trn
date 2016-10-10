/*
 * Seller Approval
*/	
jQuery(document).on( "click", ".aprove-seller", function() {
	var sellerID = jQuery(this).data('seller-id');
	
	jQuery.ajax({
		type:'POST',
		url: kniTrnAS.url,
		data: {
			security:kniTrnAS.nonce,
			action: 'kni_trn_approve_seller',
			sellerID: sellerID
		},
		success: function (data) {
			console.log('Data recive');
			console.log(data);
		},
		error: function (){
			console.log('Ошибка!')
		}
	})
	
})

/*
 * Pause seller
*/	
jQuery(document).on( "click", ".pause-seller", function() {
	var sellerID = jQuery(this).data('seller-id');
	
	jQuery.ajax({
		type:'POST',
		url: kniTrnAS.url,
		data: {
			security:kniTrnAS.nonce,
			action: 'kni_trn_pause_seller',
			sellerID: sellerID
		},
		success: function (data) {
			console.log('Data recive');
			console.log(data);
		},
		error: function (){
			console.log('Error!')
		}
	})
	
})

/*
 * Pause seller promotion
*/	
jQuery(document).on( "click", ".pause-seller-promotion", function() {
	var productID = jQuery(this).data('product-id');
	
	jQuery.ajax({
		type:'POST',
		url: kniTrnAS.url,
		data: {
			security:kniTrnAS.nonce,
			action: 'kni_trn_pause_seller_promotion',
			productID: productID
		},
		success: function (data) {
			console.log('Data recive');
			console.log(data);
		},
		error: function (){
			console.log('Error!')
		}
	})
	
})
	
	
