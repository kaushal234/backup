{literal}
<script type="text/javascript">
if (typeof jQuery == 'undefined'){
	document.write('<'+'script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></'+'script>');
}
</script>
<script type="text/javascript">

$.fn.autosuggest = function(options){

    if (!$(this).is('select')) return;
	
    var self = this;

    self.config = {
		
		message : '',
		onChange : null			// Function: onChange ( self )
	};

    if (typeof options == 'object') $.extend(self.config, options);

    self.name = $(this).attr('name');
   	
    self.isActive = false;
   	
    self.options = {};
   	
    self.input = {};
   	
    self.container = {};
   	
    self.delay = 0;
   	
    self.properties = {
   	    
       	top : $(self).position().top,
       	
        left : $(self).position().left,
   	    
       	width : $(self).width(),
       	
        height : $(self).height()
   	};
   	
   	
    // Build elements
   	self.input.display = $('<input type="text"/>').addClass('autosuggest-input').css({
       	
        width : self.properties.width + 'px'
   	    
    }).blur(function(){
   	    
       	if (!self.isActive) self.asHide();
    });
   	
    self.input.value = $('<input/>').attr({
   	    
       	'type' : 'hidden',
       	
        'name' : self.name
   	});
   	
    $(self).children('option').each(function(){
   	    
       	if ($(this).attr('value') != ''){
           	
            self.options[$(this).attr('value')] = $(this).text();
   	    }
   	});
   	
    self.container.parent = $('<span/>').hover(
   	    
       	function(){
           	
            self.isActive = true;
   	    },
       	
        function(){
   	        
       	    self.isActive = false;
       	}
    );
   	
    self.container.options = $('<div/>').addClass('autosuggest-container').css({
   	    
       	width : self.properties.width + 'px'
   	});
   	
    $(self).removeAttr('name').hide();
   	
   	
    // Attache elements
   	$(self.container.parent).insertBefore(self);
   	
    $(self.container.parent).append(self.input.display);
   	
    $(self.container.parent).append(self.container.options);
   	
    $(self.container.parent).append(self.input.value);
   	
   	
    // Outside events
   	$(document).mouseup(function(){
       	
        if (!self.isActive) self.asHide();
   	});
   	
   	
    $(self.input.display).blur(function(){
   	    
       	if (!self.isActive) self.asHide();

        self.asMsg();
   	    
    }).keyup(function(){
   	    
       	self.lookup();
       	
    }).focus(function(){

   	    if ($(this).val() == self.config.message) $(this).val('');
   	});
   	
    self.lookup = function() {

       	self.asEmpty();
       	
        clearTimeout(self.delay);
   	    
       	var inputText = $(self.input.display).val();
       	
        if(inputText == ''){
   	        
       	    self.asHide();
           	return;
       	}
        
   	    if (self.config.message.length > 0 && inputText != self.config.message) $(self.input.value).val(inputText);
       	
        var delay = 100;
   	    
       	if (inputText.length <= 4) delay = 300;
       	
        self.delay = setTimeout(function(){
   	        
       	    self.asShow();
           	
            var cnt = 0;
   	        
       	    for(i in self.options){
           	    
               	if(self.options[i].toLowerCase().indexOf($(self.input.display).val().toLowerCase()) >= 0){
                   	
                    var el = self.newElement(i, self.options[i]);
   	                
       	            $(self.container.options).append(el);
           	        
               	    cnt++;
               	}
           	}
           	
            if(cnt == 0) self.asHide();
   	        
       	}, delay);

        if (typeof self.config.onChange == 'function') self.config.onChange(self);

        self.asMsg();
   	};
   	
    self.newElement = function(key, text) {
   	    
       	var div = $('<div/>').text(text).addClass('autosuggest-element').attr({
           	
            'title' : 'Click to select ' + text,
   	        
       	    'data-value-id' : key
           	
        }).hover(
   	        
       	    function(){
           	    
               	$(div).addClass('autosuggest-active');
            },
   	        
       	    function(){
           	    
               	$(div).removeClass('autosuggest-active');
            }
   	        
       	).click(function(){

            $(self.input.value).val($(div).attr('data-value-id'));
   	        
       	    $(self.input.display).val($(div).text());
           	
            self.asHide();

            if (typeof self.config.onChange == 'function') self.config.onChange(self);
   	    });
       	
        return div;
   	};
   	
    self.asHide = function() {
   	    
       	$(self.container.options).hide();
   	};
   	
    self.asShow = function() {
   	    
       	$(self.container.options).show();
   	};
    
   	self.asEmpty = function() {
       	
        $(self.container.options).empty();
   	};

   	self.asMsg = function() {

        if (self.config.message == '') return;

   	    if ($(self.input.display).val() == ''){

   	        $(self.input.display).addClass('autosuggest-message').val(self.config.message);
            $(self.input.value).val("");
       	    
        }else{

   	        $(self.input.display).removeClass('autosuggest-message');
       	}
    }
	
    self.asMsg();

    return self;
};

</script>
<style type="text/css">
.autosuggest-container {
  display: none;
  font-size: 10pt;
  background: #eee;
  padding: 0px;
  border: 1px solid #000;
  max-height: 150px;
  overflow: auto;
  font-family: Arial, Helvetica, sans-serif;
  clear: both;
}

.autosuggest-element {
  font-size: 10pt;
  padding: 3px;
}

.autosuggest-active {
  background: #ffe;
  cursor: hand;
  cursor: pointer;
}

.autosuggest-input {}

.autosuggest-message {
  color: #999;
}

</style>
{/literal}
