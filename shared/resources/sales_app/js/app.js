
var tldSalesApp = {

	init : function(){
	    this.bindEvents();
	},

	bindEvents : function(){
		
		// My Customers listing - Submit form upon ahref click
		$(".submit-form").click(function() {
			$(this).closest('form').submit();
	    });
		
		// SFR Search - Special form action based on field entered
		$('#sfr_search').submit(function(){
			var pathname = window.location.pathname;
			if($('#sfr_id').val()){
				var url = "?page=sfr&action=view";
			}
			else if($('#sfr_by_customer').val())
			{
				var url = "?page=sfr&action=listing";
			}
			else
			{
				var url = "";
			}
			$(this).attr('action', pathname + url);
		}); 
		
		// Confidential button feature in MIM Module
		$('#mim-btn-confidential').click(function (currentClass) {
			var input = $('#mim-input-confidential');
			if($(this).hasClass("btn-success"))
			{
				$(this).removeClass("btn-success glyphicon-eye-open")
					.addClass("btn-danger glyphicon-eye-close")
					.text(" Unset Confidential");
				input.val('Y');
			}
			else
			{
				$(this).removeClass("btn-danger glyphicon-eye-close")
					.addClass("btn-success glyphicon-eye-open")
					.text(" Set Confidential");
				input.val('N');
			};
					
		});		
		
		// Restricted Communication button feature in SFR Module
		$('#sfr-btn-shortcom').click(function (currentClass) {
			var input = $('#sfr-input-shortcom');
			if($(this).hasClass("btn-success"))
			{
				$(this).removeClass("btn-success glyphicon-eye-open")
					.addClass("btn-danger glyphicon-eye-close")
					.text(" Unset Restricted Communication");
				input.val('Y');
			}
			else
			{
				$(this).removeClass("btn-danger glyphicon-eye-close")
					.addClass("btn-success glyphicon-eye-open")
					.html(" Set Restricted Communication</br>(Not for SFR update)");
				input.val('N');
			};
					
		});
		
		// Disable TOC Notification button
		$('#toc-btn-not').click(function (currentClass) {
			var input = $('#toc-disable-not');
			if($(this).hasClass("btn-success"))
			{
				$(this).removeClass("btn-success glyphicon-eye-open")
					.addClass("btn-danger glyphicon-eye-close")
					.text(" Unset Disabled TOC Notification");
				input.val('Y');
			}
			else
			{
				$(this).removeClass("btn-danger glyphicon-eye-close")
					.addClass("btn-success glyphicon-eye-open")
					.text(" Set Disabled TOC Notification");
				input.val('N');
			};
					
		});	
		
		// Clickable Row SFR listing
		$(".clickable-row-sfr").click(function() {
			$('#myModalLabel').text("SFR#" + $(this).data('url'));
			$('.hiddenId').val($(this).data('url'));
	    	$('#myModal').modal('show');
	    });
		
		// Clickable Row TOC listing
		$(".clickable-row-toc").click(function() {
			$('#myModalLabel').text("TOC#" + $(this).data('url'));
			$('.hiddenId').val($(this).data('url'));
	    	$('#myModal').modal('show');
	    });
		
		// Clickable Row ODP listing
		$(".clickable-row-odp").click(function() {
			$('#myModalLabel').text("ER#" + $(this).data('url'));
			$('.hiddenId').val($(this).data('url'));
	    	$('#myModal').modal('show');
	    });

		// Clickable Row SFR TOC and ODP listing for customers by SSO
		$(".clickable-row-sfr-toc-odp").click(function() {
			var idSelected = $(this).data("id");
			var module = $(this).data("module");
			var phpSelf = $(this).data("url");
			var moduleWithId = module+'_id';
			if (module === "odp") {
				moduleWithId = 'er_id';
 			}

			$('#myModalLabel').text(module+"#"+idSelected);
			$('#link-sfr-toc-odp').attr('action', phpSelf+'?page='+module+'&action=view');
			$('.hiddenId')
				.val(idSelected)
				.attr('name',moduleWithId);
			$('#myModal').modal('show');
		});

		// Multi Select
		$(document).ready(function(){
			$('#multi-select').multiSelect();
		});
		
		// Scrollable table with Fix Headers
		$(document).ready(function(){
		    var table = $('.scrollable').DataTable( {
		        "bPaginate": false,
		        "bFilter": false, 
		        "bInfo": false
		      } );
		    new $.fn.dataTable.FixedHeader( table );
		});
		
	    // Incremental buttons for Quantity
	    $(".btn-form-control-1").each(function(){
	    	$(this).on("click", function() {
	    		var button = $(this);    		
		    	var fieldInput = button.parent().parent().find(".btn-form-input");  
	    	    if (button.text() == "+") {
	    	    	var newVal = parseFloat(fieldInput.val()) + 1;
		    		} else {
		    	   // Don't allow decrementing below zero
		    	    if (fieldInput.val() > 0) {
		    	      var newVal = parseFloat(fieldInput.val()) - 1;
		    	    } else {
		    	      newVal = 0;
		    	    }
		    	  }
	    	    fieldInput.val(newVal);	
	    	});
	    });
	    
	    // Incremental buttons for Percentage
	    $(".btn-form-control-5").each(function(){
		    $(this).on("click", function() {
		    	var button = $(this);    		
			    var fieldInput = button.parent().parent().find(".btn-form-input");  
		    	if (button.text() == "+") {
		    	// Don't allow incrementing over 100
		    	    if (fieldInput.val() < 96) {
					  var newVal = parseFloat(fieldInput.val()) + 5;
					} else {
					  newVal = 100;
					}
			    } else {
			    //Don't allow decrementing below zero
			    	if (fieldInput.val() > 4) {
			    	  var newVal = parseFloat(fieldInput.val()) - 5;
			    	} else {
			    	  newVal = 0;
			    	}
			      }
		    	fieldInput.val(newVal);	
		    });
	    });
	    	
	    // Datepicker (YYYY-MM)
    	$('#yyyymmDate').datepicker({
    		viewMode: 2,
    		minViewMode: 1,
    		format: 'yyyy-mm'
		});
		$('#yyyymmDate').on('change', function() {
		   alert($('#yyyymmDate').val());
		});

		// Search
        $('#searchFilter').keyup( function(){
            var search = $('#searchFilter').val().toLowerCase();
            $('#searchFilterContent div').each(function() {
            	var $this = $(this)
                $this.text().toLowerCase().indexOf(search) === -1 ? $this.hide() : $this.show();
            })
		});
	}
};

$(document).ready(function(){
	tldSalesApp.init();
});
