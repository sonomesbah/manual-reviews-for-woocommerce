jQuery( function( $ ) {

    $("#mrfw_form_add").click(function(event) {
    	event.preventDefault();

		var name        = $( '#mrfw_form_name' ).val();
		var content     = $( '#mrfw_form_content' ).val();
		var date       	= $( '#mrfw_form_date' ).val();
		var rating      = $( '#mrfw_form_rating' ).val();
		var approved    = $( '#mrfw_form_approved' ).prop('checked');
		var verified    = $( '#mrfw_form_verified' ).prop('checked');

		var review_data  = {
			action: 'woocommerce_add_manual_review',
			post_id: mrfw_admin_meta_box.post_id,
			name: name,
			content: content,
			rating: rating,
			approved: Number(approved),
			verified: Number(verified),
			date: date,
			security: mrfw_admin_meta_box.add_manual_review_nonce
		}

		var $wrapper = $( '#mrfw_form' );

		$wrapper.block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});

		$.post( mrfw_admin_meta_box.ajax_url, review_data, function( response ) {
			if ( response == '-1' ) {
				$wrapper.stop().css("background-color", "#faa").animate( { backgroundColor: "#fff"} , 1500);
			} else {
				$wrapper.stop().css("background-color", "#ceb").animate({ backgroundColor: "#fff"}, 1500);
				$( '#mrfw_form_name' ).val( '' );
				$( '#mrfw_form_content' ).val( '' );
				$( '#mrfw_form_rating' ).val( '5' );
			}
			$wrapper.unblock();
		});

		return false;

    });

});
