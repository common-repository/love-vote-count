jQuery(document).ready(function($) {
	
		
		$(".vote-button").click(function(){
			
			
			post_id = jQuery(this).attr("data-post_id");
			nonce = jQuery(this).attr("data-nonce");
			user_ip = jQuery("meta[name=ip]").attr("content")
			
			var itemName = user_ip + post_id;
			
			if (!localStorage.getItem(itemName)){ 
			
			 	localStorage.setItem(itemName, true);
			
				$.ajax({
					
					type: "post",
					dataType: "json",
					url: lvc_voting_ajax.ajaxurl,
					data: { 
						action: 'lvc_voting_request',
						post_id: post_id,
						user_ip: user_ip,
						nonce: nonce,
						
					},
					success: function(response) {
						
					if(response.type == "success") {
							$('button[data-post_id="' + post_id +'"] > span.vote-count').html(response.vote_count);
						} else if (response.type == "no-vote") {
							$('span.already-voted').fadeIn(500).delay(2000).fadeOut(500);	
						} else {
						   $('span.already-voted').html("Error").delay(2000).fadeOut(500)
						}
					 },
	
				}); // ajax
				
			} else {
				
				$('span.already-voted').fadeIn().delay(2000).fadeOut(500);
					
			}; // local storage check
	
	}); // button.click function
	
}); // doc.ready

