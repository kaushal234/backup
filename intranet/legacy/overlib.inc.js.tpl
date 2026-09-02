{literal}
<script type="text/javascript">
if (typeof jQuery == 'undefined'){
	document.write('<'+'script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></'+'script>');
}
</script>
<script type="text/javascript">

$.fn.overlib = function(options){

	var parent = this;
	
	parent.config = {
		
		delay : 300,
		
		fade : 300
	};
	
	if (typeof options == 'object') $.extend(parent.config, options);
	
	return this.each(function(){
		
    	var self = this;
		
    	self.display = {};
		
    	self.delay = 0;
		
		// Build containers
    	self.container = $('<div/>').addClass('overlib-container').hide();
		
    	// Header
    	if ($(self).attr('data-header')) {
			
        	self.display.header = $('<div/>').addClass('overlib-header').html($(self).attr('data-header'));
			
        	$(self.container).append(self.display.header).hide();
    	}
		
    	// Body
    	if ($(self).attr('data-body')) {
			
        	self.display.body = $('<div/>').addClass('overlib-body').html($(self).attr('data-body'));
			
        	$(self.container).append(self.display.body);
    	}
		
    	// Footer
    	if ($(self).attr('data-footer')) {
			
        	self.display.footer = $('<div/>').addClass('overlib-footer').html($(self).attr('data-footer'));
			
        	$(self.container).append(self.display.footer);
    	}
		
    	// Modify behavior
		$(self).hover(
			
			// in
			function(){
				
				self.enableDisplay();
			},
			
			// out
			function(){
				
				self.delay = setTimeout(function(){
					
					self.disableDisplay();
					
				}, parent.config.delay);
			}
		);
		
    	// Functionality
    	self.enableDisplay = function(){
			
			if (!$(self.container).is(':hidden')) return;
			
        	$(self.container).show();
    	};
		
    	self.disableDisplay = function(){
			
    		if ($(self.container).is(':hidden')) return;
			
        	self.enabled = false;
			
        	$(self.container).fadeOut(parent.config.fade);
    	};
    	
    	// Assemble
    	$('body').append(self.container);
	});
};

</script>
<style type="text/css">
.overlib-container {
	position: fixed;
	top: 10px;
	left: 10px;
	width: 450px;
	padding: 10px;
	background-color: rgb(0, 0, 0) transparent;
	background-color: rgba(0, 0, 0, 0.5);
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#77000000, endColorstr=#77000000);
	-ms-filter: "progid:DXImageTransform.Microsoft.gradient(startColorstr=#77000000, endColorstr=#77000000)";
	-moz-border-radius: 8px;
	-webkit-border-radius: 8px;
	border-radius: 8px;
	font-size: 8pt;
}
.overlib-header {
	background-color: #333;
	color: #dedede;
	padding: 6px;
	font-weight: bold;
}
.overlib-body {
	background-color: #ffe;
	color: #000;
	border: 1px solid #000;
	padding: 6px;
}
.overlib-footer {
	background-color: #333;
	color: #dedede;
	padding: 6px;
	font-weight: bold;
}
</style>
{/literal}
