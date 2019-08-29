

/*
 * review.js 
 */


$(document).ready(function(){

	$("#rateYo").rateYo({
		 rating: 2,
		 fullStar: true
	});

	$('#review-btn').click(function(e) {
		e.preventDefault();
		$('#review-msg').html('');
		$('#review-error').html('');
		if (validateReview()) {
			var rating = $("#rateYo").rateYo("rating");
			var form = $('#review-form');

		    $.ajax( {
		      type: "POST",
		      url: '/review',
		      data: form.serialize()+'&review-rating='+rating+'&review-submitted=true',
		      success: function( response ) {
		    	if (response.status == 0)
		    	{
		    		$('#review-msg').html(response.msg);
		    		$('#review-add-form').hide();
		    	} else {
		    		$('#review-error').html(response.error);
		    	}
		      }
		    });
 
		}
	});

});

function validateReview()
{
	var arrError = [];

	if ($('#review-name').val() == '')
		arrError.push('Please enter your name');

	if ($('#review-email').val() == '')
		arrError.push('Please enter your email');

	if ($('#review-age').val() == 'NULL')
		arrError.push('Please enter your age');

	if ($('#review-gender').val() == 'NULL')
		arrError.push('Please enter your gender');

	if ($('#review-nationality').val() == '')
		arrError.push('Please enter your nationality');

	if ($('#review-title').val() == '')
		arrError.push('Please enter a review title');

	if ($('#review-review').val() == '')
		arrError.push('Please enter your review');

	if ($('#review-rating').val() == 'NULL')
		arrError.push('Please enter your age');

	var errorMsg = '';
	if (arrError.length >= 1)
	{
		errorMsg = "<ul>";
		for(var i =0; i< arrError.length; i++)
		{
			errorMsg += "<li>"+arrError[i]+"</li>";
		}
		errorMsg += "</ul>";

		$('#review-error').html(errorMsg);
		
		return false;
	}
	
	return true;
}
