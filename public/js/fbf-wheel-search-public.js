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
		window.populate_chasis = function ($chasis_select, manufacturer_id, is_packages_page, selected){
			let is_landing_page = false;
			let update_session = true;
			let is_widget = false;
			let is_accessories = false;
			if($('body').hasClass('single-landing-pages')){
				is_landing_page = true;
			}
			if($('body').hasClass('single-product')){
				update_session = false;
			}
			if($chasis_select.hasClass('fbf-wheel-search-chassis-select-v2')){
				is_widget = true;
			}
			if($chasis_select.hasClass('fbf-accessories-search-chassis-select')){
				is_accessories = true;
			}
			$chasis_select.empty();
			$chasis_select.append('<option value="">Please wait...</option>');
			let data = {
				action: 'fbf_wheel_search_get_chasis',
				manufacturer_id: manufacturer_id,
				ajax_nonce: fbf_wheel_search_ajax_object.ajax_nonce,
				update_session: update_session,
			};
			$.ajax({
				url: fbf_wheel_search_ajax_object.ajax_url,
				type: 'POST',
				data: data,
				dataType: 'json',
				success: function(response){
					if(response.status==='success'){
						let option;
						if(is_landing_page){
							option = '<option value="">Select Model</option>';
						}else{
							option = '<option value="">Select chassis</option>';
						}

						$.each(response.data, function(i, e){

							console.log(e.chassis.display_name);

							/*let start_year;
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
							}*/
							let id = e.chassis.id;
							//let text = e.model.name + ' ' + e.generation.display_name;
							let text = e.chassis.display_name;
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

			if(!is_packages_page && !is_landing_page && !is_widget && !is_accessories){
				$chasis_select.unbind('change');
				$chasis_select.on('change', function(e){
					let $manu;
					if($(this).attr('id')==='fbf-wheel-search-chasis-select'){
						$manu = $('#fbf-wheel-search-manufacturer-select');
						//console.log($manufacturer_select.val());
						let url = '/wheel-search-results/chassis/' + $(this).val() + '/vehicle/' + encodeURIComponent($chasis_select.find(':selected').text()) + '/';
						//console.log(url);
						mixpanel_track($chasis_select.parents('.homepage__box__content'), 'homepage');
						window.location.href = url;
					}else if($(this).attr('id')==='fbf-package-search-chasis-select'){
						$manu = $('#fbf-package-search-manufacturer-select');
						let url = '/tyre-wheel-packages/chassis/' + $(this).val() + '/vehicle/' + $manu.val() + '/name/' + encodeURIComponent($chasis_select.find(':selected').text()) + '/';
						//console.log(url);
						window.location.href = url;
					}else if($(this).attr('id')==='fbf-fitment-chasis-select'){
						let $info = $('#fitment-info');
						$info.empty();
						let data = {
							action: 'fbf_wheel_fitment',
							product_id: $(this).attr('data-product_id'),
							chassis_id: $(this).val(),
							ajax_nonce: fbf_wheel_search_ajax_object.ajax_nonce,
							vehicle: $(this).find('option:selected').text(),
						}
						$.ajax({
							url: fbf_wheel_search_ajax_object.ajax_url,
							type: 'POST',
							data: data,
							dataType: 'json',
							success: function (response) {
								if(response.status==='success'){
									let $fitting;
									if(response.fits){
										$fitting = $(`<p class="single-product__fitment-info success mt-4 mb-0"><i class="fas fa-check-circle fa-lg mr-2"></i> These wheels fit a <strong>${response.vehicle}</strong></p>`);
										$('.single-product__add-basket-btn, .single-product__qty-select').prop('disabled', false);
									}else{
										let link = `/wheel-search-results/chassis/${response.id}/vehicle/${encodeURIComponent(response.vehicle)}/`
										$fitting = $(`<p class="single-product__fitment-info fail mt-4 mb-0"><i class="fas fa-times-circle fa-lg mr-2"></i> These wheels do not fit a <strong>${response.vehicle}</strong><br/><a href="${link}" class="d-inline-block mt-2">Find wheels that fit your vehicle &gt;</a></p>`);
										$('.single-product__add-basket-btn, .single-product__qty-select').prop('disabled', true);
									}
									$info.append($fitting);
								}
							}
						});
					}
				});
			}else if(is_landing_page){
				$chasis_select.unbind('change');
				$chasis_select.on('change', function(e){
					console.log('landing page change');
					//let $btn = $chasis_select.parents('.form-group').next();
					let $btn = $('.single-landing-pages__btn');
					$btn.unbind('click'); // Removes the click event
					let url = '/wheel-search-results/chassis/' + $(this).val() + '/vehicle/' + encodeURIComponent($chasis_select.find(':selected').text()) + '/';
					$btn.attr('data-id', $(this).val());
					$btn.attr('data-vehicle', encodeURIComponent($chasis_select.find(':selected').text()));
					$btn.attr('data-url', url);
					$btn.bind('click', function(){
						console.log('button clicked');
						window.landing_page_search();
						/*if($(this).attr('data-brand')!==''){
							url+= '#pa_brand-name=' + $(this).attr('data-brand');
						}
						window.location.href = url;*/
					});

					if($(this).val().length > 0){
						$btn.prop('disabled', false);
					}else{
						$btn.prop('disabled', true);

					}
				});
			}else if(is_accessories){
				$chasis_select.unbind('change');
				$chasis_select.on('change', function(e){
					console.log('accessories');
					//console.log($manufacturer_select.val());
					let url = '/accessories-search-results/chassis/' + $(this).val() + '/vehicle/' + encodeURIComponent($chasis_select.find(':selected').text()) + '/';
					if(!$chasis_select.parents('.accessory-search-widget-v2').length){
						window.location.href = url;
					}else{
						console.log('it is the widget');
						console.log('make button go to: ' + url);
					}
				});
			}
		};

		let $manufacturer_select = $('#fbf-wheel-search-manufacturer-select, #fbf-package-search-manufacturer-select, #fbf-fitment-manufacturer-select, .fbf-wheel-search-manufacturer-select-v2, .fbf-accessories-search-manufacturer-select');
		console.log('manu select:');
		console.log($manufacturer_select);
		if(!$manufacturer_select.attr('data-init_id')){
			$manufacturer_select.val($manufacturer_select.find('option:first').val());
		}else{
			window.populate_chasis($('#fbf-fitment-chasis-select'), $manufacturer_select.attr('data-init_id'), false, $manufacturer_select.attr('data-chassis_id'));
		}
		let $chasis_select;
		let is_packages_page;
		if($('body').hasClass('tyre-wheel-packages')){
			is_packages_page = true;
		}else{
			is_packages_page = false;
		}

		$manufacturer_select.on('change', function(e) {
			let id = $(this).attr('id');
			let cl = 'fbf-wheel-search-manufacturer-select-v2';
			let acl = 'fbf-accessories-search-manufacturer-select';
			console.log('id:' + id);
			console.log('class:' + cl);
			if(id==='fbf-wheel-search-manufacturer-select'){
				$chasis_select = $('#fbf-wheel-search-chasis-select');
			}else if(id==='fbf-package-search-manufacturer-select'){
				$chasis_select = $('#fbf-package-search-chasis-select');
			}else if(id==='fbf-fitment-manufacturer-select'){
				$chasis_select = $('#fbf-fitment-chasis-select');
			}else if($(this).hasClass(cl)){
				$chasis_select = $('.fbf-wheel-search-chassis-select-v2');
			}else if($(this).hasClass(acl)){
				$chasis_select = $('.fbf-accessories-search-chassis-select');
			}
			window.populate_chasis($chasis_select, $(this).val(), is_packages_page, false);
		});

		// Size search fields
		$('.wheel-search-widget-v2').find('input, select').bind('blur focus keyup change', function(){
			console.log('wheel widget field');
			wheel_widget_form_check($(this));
		});

		// Size search fields
		$('.accessory-search-widget-v2').find('input, select').bind('blur focus keyup change', function(){
			console.log('wheel widget field');
			accessory_widget_form_check($(this));
		});

		let $wheel_search_form = $('.wheel-search-widget-v2__form');
		$wheel_search_form.on('submit', function(){
			console.log('wheel search form submit');
			let $btn = $wheel_search_form.find('.wheel-search-widget-v2__button');
			mixpanel_track($(this), 'widget');
			$btn.trigger('click');
			return false;
		});

		let $accessory_search_form = $('.accessory-search-widget-v2__form');
		$accessory_search_form.on('submit', function(){
			console.log('accessory search form submit');
			let $btn = $accessory_search_form.find('.accessory-search-widget-v2__button');
			$btn.trigger('click');
			return false;
		});

		function wheel_widget_form_check($elem){
			let $form = $elem.parents('.wheel-search-widget-v2');
			let $button = $form.find('.wheel-search-widget-v2__button');
			let $manu_select = $form.find('.fbf-wheel-search-manufacturer-select-v2');
			let $chassis_select = $form.find('.fbf-wheel-search-chassis-select-v2');
			let $postcode = $form.find('.fbf-wheel-search-postcode-v2');

			if($manu_select.val()!==''&&$chassis_select.val()!==''&&$postcode.val()!==''){
				$button.prop('disabled', false);
				let url = '/wheel-search-results/chassis/' + $chassis_select.val() + '/vehicle/' + encodeURIComponent($chasis_select.find(':selected').text()) + '/';
				$button.unbind('click');
				$button.bind('click', function(){
					window.location.href = url;
				});
			}else{
				$button.prop('disabled', true);
			}
		}

		function accessory_widget_form_check($elem){
			console.log('accessory widget form check');
			let $form = $elem.parents('.accessory-search-widget-v2');
			let $button = $form.find('.accessory-search-widget-v2__button');
			let $manu_select = $form.find('.fbf-accessories-search-manufacturer-select');
			let $chassis_select = $form.find('.fbf-accessories-search-chassis-select');
			let $postcode = $form.find('.fbf-accessory-search-postcode-v2');

			console.log('postcode value:');
			console.log($postcode.val());
			console.log($button);

			if($manu_select.val()!==''&&$chassis_select.val()!==''&&$postcode.val()!==''){
				$button.prop('disabled', false);
				let url = '/accessories-search-results/chassis/' + $chassis_select.val() + '/vehicle/' + encodeURIComponent($chasis_select.find(':selected').text()) + '/';
				$button.unbind('click');
				$button.bind('click', function(){
					window.location.href = url;
					return false;
				});
			}else{
				$button.prop('disabled', true);
			}
		}

		function mixpanel_track($elem, origin){
			let event = 'wheel-search';
			if(origin==='widget'){
				let $form = $elem;
				let $manu_select = $form.find('.fbf-wheel-search-manufacturer-select-v2');
				let $chassis_select = $form.find('.fbf-wheel-search-chassis-select-v2');
				let $postcode = $form.find('.fbf-wheel-search-postcode-v2');
				let props = {
					manufacturer_id: $manu_select.val(),
					manufacturer: decodeURIComponent($manu_select.find(':selected').text()),
					chassis_id: $chassis_select.val(),
					chassis: decodeURIComponent($chasis_select.find(':selected').text()),
					postcode: $postcode.val(),
					origin: origin,
				};
				console.log('wheel search mixpanel track from ' + origin);
				window.mixpanel_track(event, props);
			}else if(origin==='homepage'){
				let $form = $elem;
				let $manu_select = $form.find('#fbf-wheel-search-manufacturer-select');
				let $chassis_select = $form.find('#fbf-wheel-search-chasis-select');
				let props = {
					manufacturer_id: $manu_select.val(),
					manufacturer: decodeURIComponent($manu_select.find(':selected').text()),
					chassis_id: $chassis_select.val(),
					chassis: decodeURIComponent($chasis_select.find(':selected').text()),
					origin: origin,
				};
				console.log('wheel search mixpanel track from ' + origin);
				window.mixpanel_track(event, props);
			}
		}
	});

})( jQuery );
