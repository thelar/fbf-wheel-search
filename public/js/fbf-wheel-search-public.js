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
		let $manufacturer_select = $('#fbf-wheel-search-manufacturer-select, #fbf-package-search-manufacturer-select');
		$manufacturer_select.val($manufacturer_select.find('option:first').val());
		let $chasis_select;
		let is_packages_page;
		if($('body').hasClass('tyre-wheel-packages')){
			is_packages_page = true;
		}else{
			is_packages_page = false;
		}

		$manufacturer_select.on('change', function(e) {
			let id = $(this).attr('id');
			//console.log('id:' + id);
			if(id==='fbf-wheel-search-manufacturer-select'){
				$chasis_select = $('#fbf-wheel-search-chasis-select');
			}else if(id==='fbf-package-search-manufacturer-select'){
				$chasis_select = $('#fbf-package-search-chasis-select');
			}
			window.populate_chasis($chasis_select, $(this).val(), is_packages_page, false);

		});

		window.populate_chasis = function ($chasis_select, manufacturer_id, is_packages_page, selected){
			$chasis_select.empty();
			$chasis_select.append('<option>Please wait...</option>');
			let data = {
				action: 'fbf_wheel_search_get_chasis',
				manufacturer_id: manufacturer_id,
				ajax_nonce: fbf_wheel_search_ajax_object.ajax_nonce,
			};
			$.ajax({
				url: fbf_wheel_search_ajax_object.ajax_url,
				type: 'POST',
				data: data,
				dataType: 'json',
				success: function(response){
					if(response.status==='success'){
						let option = '<option value="">Select chassis</option>';
						$.each(response.data, function(i, e){
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
							let sel = '';
							if(id==selected){
								sel = ' selected';
							}
							option+= '<option value="' + id + '"' + sel +'>' + text + '</option>';
						});
						$chasis_select.empty();
						$chasis_select.append(option);
					}else{
						console.log('There was an error: ' + response.error);
					}
				},
			});

			if(!is_packages_page){
				$chasis_select.unbind('change');
				$chasis_select.on('change', function(e){
					let $manu;
					if($(this).attr('id')==='fbf-wheel-search-chasis-select'){
						$manu = $('#fbf-wheel-search-manufacturer-select');
						//console.log($manufacturer_select.val());
						let url = '/wheel-search-results/chassis/' + $(this).val() + '/vehicle/' + encodeURIComponent($manu.find(':selected').text() + ' ' + $chasis_select.find(':selected').text()) + '/';
						//console.log(url);
						window.location.href = url;
					}else if($(this).attr('id')==='fbf-package-search-chasis-select'){
						$manu = $('#fbf-package-search-manufacturer-select');
						let url = '/tyre-wheel-packages/chassis/' + $(this).val() + '/vehicle/' + $manu.val() + '/name/' + encodeURIComponent($manu.find(':selected').text() + ' ' + $chasis_select.find(':selected').text()) + '/';
						//console.log(url);
						window.location.href = url;
					}
				});
			}
		};
	});

})( jQuery );
