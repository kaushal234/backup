
$(document).ready(function() {

	var decodeEntities = (function() {
		var element = document.createElement('div');
		function decodeHTMLEntities (str) {
			if(str && typeof str === 'string') {
				// strip script/html tags
				str = str.replace(/<script[^>]*>([\S\s]*?)<\/script>/gmi, '');
				str = str.replace(/<\/?\w(?:[^"'>]|"[^"]*"|'[^']*')*>/gmi, '');
				element.innerHTML = str;
				str = element.textContent;
				element.textContent = '';
			}
			return str;
		}
		return decodeHTMLEntities;
	})();

	$(document).ready(function() {
		$('.select2-customer').select2({
			ajax: {
				url: '/en/private/product_support/index.ps.php?id=83388&m[0]=equipment&m[1]=customers_ajax',
				dataType: 'json',
				delay: 250,
				data: function (params) {
					return {
						query: params.term
					};
				},
				processResults: function (data, params) {
					const results = [];
					$.forEach(data, function(item, index) {
						results.push({
							id: item.id,
							text:  decodeEntities(item.customer_name)
						});
					});
					return {
						results: results
					};
				}
			},
			placeholder: "Select a customer",
			allowClear: true
		});

		$('.select2-customer-name').select2({
			ajax: {
				url: '/en/private/product_support/index.ps.php?id=83388&m[0]=equipment&m[1]=customers_ajax',
				dataType: 'json',
				delay: 250,
				data: function (params) {
					return {
						query: params.term
					};
				},
				processResults: function (data, params) {
					const results = [];
					$.forEach(data, function(item, index) {
						results.push({
							id: decodeEntities(item.customer_name),
							text:  decodeEntities(item.customer_name)
						});
					});
					return {
						results: results
					};
				}
			},
			placeholder: "Select a customer",
			allowClear: true
		});

		$('.select2-airports').select2({
			ajax: {
				url: '/en/private/product_support/index.ps.php?id=83388&m[0]=equipment&m[1]=airports_ajax',
				dataType: 'json',
				delay: 250,
				data: function (params) {
					return {
						query: params.term
					};
				},
				processResults: function (data, params) {
					const results = [];
					$.forEach(data, function(item, index) {
						const countryName = item.country_name || ' - No Country - ';
						results.push({
							id: item.airport_code,
							airport_name: item.airport_name,
							airport_code: item.airport_code,
							country_name: item.country_name,
							text: item.airport_code + ' ' + countryName + ' ' + item.airport_name,
						});
					});
					return {
						results: results
					};
				},
			},
			placeholder: "Select an airport",
			allowClear: true,
			templateResult: function (state) {
				if (!state.id) {
					return state.airport_code;
				}

				const countryName = state.country_name || 'No Country';

				return $('<div><strong>'+state.airport_code+'</strong> '+state.airport_name+'<br /><span style="color: #555555">'+countryName+'</span></div>');
			}
		});
	});

	$(".datepicker").each(function(){
	    var format = $(this).data('dateformat');
	    if(format == null) format = 'yy-mm-dd';
	    $(this).datepicker({
	       dateFormat: format,
	       changeMonth: true,
	       changeYear: true
	    });
	});

	$(".disablesubmit").on("click", function(event){
		$(this).hide().after('<button disabled>"Submitted, please wait"</button>');
    	$(this).parents('form:first').submit();

	});

    function JSON_escape(val){
		if(typeof val == 'undefined' || val == null) return '';
		var valEscaped = new String();
		for(var i=0; i<=val.length; i++){
			charVal = val.charAt(i);
			switch(charVal){
			case '"':
				valEscaped += '\\"';
			break;
			default:
				valEscaped += charVal;
			break;
			}
		}
		return valEscaped;
	}

	$(".autocomplete").each(function(){
		// Get data from form
		var dataForm = jQuery.parseJSON($(this).attr('autocomplete-data'));
		// Parse it to be an array of objects with label and value properties
		var dataSource = '[';
		$.each(dataForm, function(key, value){
			key = JSON_escape(key);
			value = JSON_escape(value);
			dataSource = dataSource+'{"value":"'+key+'","label":"'+value+'"},'
		});
		dataSource = dataSource.substr(0,dataSource.length-1)+']';
		// PARSE in JSON
		dataSource = jQuery.parseJSON(dataSource);
		// Makes the element AUTOCOMPLETE
		$(this).autocomplete({
			source: dataSource
		});
		// display update
		$(this).css('width','250px');
	});

	tinymce.init({
    	selector: "textarea.richtextbox",
    	menubar: false,
    	toolbar_items_size: 'small',
    	force_br_newlines : false,
    	force_p_newlines : false,
    	forced_root_block : "",
    	paste_data_images: false,
    	plugins: ["advlist autolink autosave link lists preview paste"],
    	toolbar1: "undo redo | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist | link unlink | preview",
    });

});
