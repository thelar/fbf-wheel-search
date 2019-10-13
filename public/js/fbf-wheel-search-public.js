(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	$(function() {
		let $manufacturer_select = $('#fbf-wheel-search-manufacturer-select');
		let $chasis_select = $('#fbf-wheel-search-chasis-select');
		$manufacturer_select.val($manufacturer_select.find('option:first').val());

		$manufacturer_select.on('change', function(e) {
			console.log('manufacturer change');
			console.log('Value: ' + $(this).val());

			$chasis_select.empty();
			$chasis_select.append('<option>Please wait...</option>');

			let data = {
				action: 'fbf_wheel_search_get_chasis',
				manufacturer_id: $(this).val(),
				ajax_nonce: fbf_wheel_search_ajax_object.ajax_nonce,
			}
			$.ajax({
				url: fbf_wheel_search_ajax_object.ajax_url,
				type: 'POST',
				data: data,
				dataType: 'json',
				success: function(response){
					if(response.status=='success'){
						let option = '<option value="">Select Chasis</option>';
						$.each(response.data, function(i, e){
							console.log(e);
							let start_year;
							let end_year;
							let start;
							let end;
							if(e.year_end){
								end = new Date(e.year_end);
								end_year = end.getFullYear();
							}else{
								end_year = '';
							}
							if(e.year_start){
								start = new Date(e.year_start);
								start_year = start.getFullYear();
							}else{
								start_year = '';
							}
							let id = e.id;
							let text = e.name + ' ' + start_year + ' - ' + end_year;
							option+= '<option value="' + id + '">' + text + '</option>';
						});
						$chasis_select.empty();
						$chasis_select.append(option);
					}else{
						alert('There was an error: ' + response.error);
					}
				},
			})
		});

		$chasis_select.on('change', function(e){
			let url = '/wheel-search/chassis/' + $(this).val() + '/vehicle/' + encodeURIComponent($manufacturer_select.find(':selected').text() + ' ' + $chasis_select.find(':selected').text());
			console.log(url);
			window.location.href = url;
		});

	});

})( jQuery );
