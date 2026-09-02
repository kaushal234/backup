{literal}
<script type="text/javascript">
if (!$.isFunction($.htmlentities)) {
	$.htmlentities = function ( str ) {
		return $('<div/>').text(str).html();
	};
}

$.fn.kpireview = function(){
	return this.each(function(){
		var	self = $(this),
			el = {box:{}},
			fn = {},
			pr = {};

		// Drag functions
		fn.startMove = function(){
			pr.drag = true;
			$el = $(this);
			$el.addClass('kpireview-box-header-move');
			$('.kpireview-box-header-move').mousemove(function (e) {
				if (!pr.drag) return;
				if (!pr.offset) {
					pr.offset = {
						x : e.pageX - $el.offset().left,
						y : e.pageY - $el.offset().top
					};
				}
				var	x = e.pageX - pr.offset.x,
					y = e.pageY - pr.offset.y;
				$(el.box.container).offset({
					left : x,
					top : y
				});
			});
		};
		fn.endMove = function(){
			pr.drag = false;
			pr.offset = false;
			$(this).removeClass('kpireview-box-header-move')
		};

		// Get content functions
		fn.getContent = function(){
			if (pr.hasLoaded) return;
			var url = self.attr('src').split('?', 2);
			$.post(url[0], url[1]+'&json=true', function(data){
				if (!data || data.length == 0) return;
				pr.hasLoaded = true;
				el.box.header.find('span').text(data.options.graphTitle);
				el.box.content.append('<div class="kpireview-box-content-sectiontitle">Current:</div>');
				fn.insertRows(data.rows);
				$('<a href="#" class="kpireview-box-content-addhistory"/>')
					.text('Load history archive')
					.click(function(e){
						e.preventDefault();
						// Load history data
						$.post('/en/private/common/index.php', {
							m : {0:'kpireview',1:'ajax',2:'list',3:'getHistory'},
							name_id : self.attr('src')
						}, function(xtra){
							// Remove data already in current dataset
							var del, ck, rows = {};
							for ( x in xtra ) {
								del = true;
								if (!rows.hasOwnProperty(x)){
									rows[x] = {};
								}
								for ( z in xtra[x] ) {
									ck = (function(cur, ns){
										while (cur && ns[0]){
											cur = cur[ns.shift()] || false;
										}
										return (typeof cur == 'object');
									})(data.rows, [x,z]);
									if (!ck){
										rows[x][z] = xtra[x][z];
										del = false;
									}
								}
								if (del){
									delete rows[x];
								}
							}
							fn.insertRows(rows);
						}, 'json');
						$(this).remove();
						el.box.content.append('<div class="kpireview-box-content-sectiontitle">History Archive:</div>');
					})
					.appendTo(el.box.content);
				
				// lock height
				el.box.container.css({
					height : el.box.container.css('height')
				});
				el.box.content.css({
					height : el.box.content.css('height')
				});
			}, 'json');
		};

		/***
		
		Example data object:
		{
			"2012-04":{			// This is X value
				"TLD SHE":{		// This is Z value
					"0":"11"	// This is Y value
				},
				"TLD WIN":{
					"0":"27"
				}
			},
			"2012-05":{
				"TLD SHE":{
					"0":"33"
				},
				"TLD WIN":{
					"0":"18"
				}
			}
		}
		
		***/
		fn.insertRows = function(data){
			for ( x in data ) {
				// Create X container
				var $x = fn.appendContainer( el.box.content, 'x' );
				
				// Append X value
				var $xval = fn.appendValue( x, $x, 'x' );
				
				// Iterate Z values
				for ( z in data[x] ) {
					// Create Z container
					var $z = fn.appendContainer( $x, 'z' );
					
					// Append Z value
					var $zval = fn.appendValue( z, $z, 'z' );
					
					// Iterate Y values
					for ( y in data[x][z] ) {
						// Create Y container
						var $y = fn.appendContainer( $z, 'y' );

						// Apply XYZ data
						$y.data({
							xval : x,
							yval : data[x][z][y],
							zval : z
						});
						
						// Append Y value
						var $yval = fn.appendValue( data[x][z][y], $y, 'y' );

						// Apply yval functions

						// Add comments
						$('<a href="#" class="kpireview-box-comment-add"/>')
							.text('add new comment')
							.click(function(e){
								e.preventDefault();
								e.stopPropagation();
								var $el = fn.appendMsg( $(this).parent(), null, $xval, $yval, $zval );
								$el.find('.kpireview-box-comment-tools-edit').trigger('click');
							})
							.appendTo($y);
						var comments = fn.getComments( x, data[x][z][y], z );
						for ( i in comments ) {
							fn.appendMsg( $y, comments[i], $xval, $yval, $zval );
							fn.countMsg([$xval,$yval,$zval], +1);
						}
					}
				}
			}
		};

		// Add/Update Comment Count
		fn.countMsg = function(containers, cnt){
			$(containers).each(function(){
				$c = $(this);
				var cur_cnt = $c.data('comment_cnt') | 0;
				var num = cur_cnt + cnt;
				if (num <= 0) num = 0;
				$c.data('comment_cnt', num);
				var $display_cnt = $c.find('.kpireview-box-row-count');
				if (num <= 0){
					$display_cnt.remove();
				}else{
					if (!$display_cnt.length){
						$div = $('<div class="kpireview-box-row-count"/>').text(' comments').appendTo($c);
						$span = $('<span/>').text(num).prependTo($div);
					}else{
						$display_cnt.find('span').text(num);
					}
				}
			});
		};
		
		// Append comment to XYZ (yval) parent
		fn.appendMsg = function(parent, data, $x, $y, $z){
			var
			data = data || {},
			fn_info = function(){
				var ary = [];
				if ( data.dt ) ary.push( '<span class="kpireview-created"><span>Created:</span> ' + $.htmlentities( data.dt ) + '</span>' );
				if ( data.fullname ) ary.push( '<span class="kpireview-by"><span>By:</span> ' + $.htmlentities( data.fullname ) + '</span>' );
				if ( data.updated ) ary.push( '<span class="kpireview-updated"><span>Updated:</span> ' + $.htmlentities( data.updated ) + '</span>' );
				return ary.length ? ary.join(' ') : '<span class="kpireview-na">N/A</span>';
			},
			$parent = $(parent),
			$container = $('<div class="kpireview-box-comment"/>')
				.appendTo(parent),
			$info = $('<div class="kpireview-box-comment-info"/>')
				.html(fn_info)
				.appendTo($container),
			$msg = $('<div class="kpireview-box-comment-msg"/>')
				.text(function(){
					return $.trim(data.comment);
				})
				.appendTo($container),
			$tools = $('<div class="kpireview-box-comment-tools"/>')
				.appendTo($container),
			$edit = $('<a href="#" class="kpireview-box-comment-tools-edit"/>')
				.text('edit')
				.click(function(e){
					e.preventDefault();
					e.stopPropagation();
					$tools.hide();
					$msg.hide();
					var
					$textarea = $('<textarea class="kpireview-box-comment-msg-edit"/>')
						.val($msg.text())
						.insertAfter($msg),
					$save = $('<button class="kpireview-box-comment-msg-save"/>')
						.text('save')
						.click(function(e){
							e.stopPropagation();
							$save.attr('disabled', 'disabled');
							$msg.text($.trim($textarea.val()));
							$.post('/en/private/common/index.php', {
								m : {0:'kpireview',1:'ajax',2:'comments',3:(typeof data.id == 'undefined')?'insert':'update'},
								name_id : self.attr('src'),
								id : data.id,
								msg : $msg.text(),
								xval : $parent.data('xval'),
								yval : $parent.data('yval'),
								zval : $parent.data('zval')
							}, function(res){
								if (typeof res.error == 'string') {
									alert(res.error);
									return;
								}
								data = res;
								$info.html(fn_info);
							}, 'json');
							$textarea.remove();
							$save.remove();
							$cancel.remove();
							$tools.show();
							$msg.show();
							fn.countMsg([$x,$y,$z], +1);
						})
						.insertAfter($textarea),
					$cancel = $('<a href="#" class="kpireview-box-comment-msg-cancel"/>')
						.text('cancel')
						.click(function(e){
							e.preventDefault();
							e.stopPropagation();
							if (!$msg.text()) {
								$container.remove();
							}
							$textarea.remove();
							$save.remove();
							$cancel.remove();
							$tools.show();
							$msg.show();
						})
						.insertAfter($textarea);
					$textarea.focus();
				})
				.appendTo($tools),
			$delete = $('<a href="#" class="kpireview-box-comment-tools-delete"/>')
				.text('delete')
				.click(function(e){
					e.preventDefault();
					e.stopPropagation();
					if ( confirm('Are you sure you want to delete this comment?\nThis action cannot be undone!') ) {
						$.post('/en/private/common/index.php', {
							m : {0:'kpireview',1:'ajax',2:'comments',3:'delete'},
							name_id : self.attr('src'),
							id : data.id
						}, function(res){
							if (typeof res.error == 'string') {
								alert(res.error);
								return;
							}
							$container.remove();
							fn.countMsg([$x,$y,$z], -1);
						}, 'json');
					}
				})
				.appendTo($tools);
			return $container;
		};
		
		// Get post data for comments
		fn.getComments = function(xval, yval, zval){
			var comments = {};
			$.ajax({
				url : '/en/private/common/index.php',
				type : 'POST',
				dataType : 'json',
				async : false,
				cache : false,
				data : {
					m : {0:'kpireview',1:'ajax',2:'comments',3:'read'},
					name_id : self.attr('src'),
					xval : xval,
					yval : yval,
					zval : zval
				},
				success : function(res){
					if (typeof res.error == 'string') {
						alert(res.error);
						return;
					}
					$.extend(comments, res);
				}
			});
			return comments;
		};

		fn.appendValue = function(value, $container, position){
			var
			$el = $('<div class="kpireview-box-val-' + position + '"/>')
				.text(value)
				.click(function(e){
					e.stopPropagation();
					$container.slideToggle();
				})
				.insertBefore($container);
			if (position == 'y'){
				$el.text('Last Recorded Value: '+value);
			}
			return $el;
		};

		fn.appendContainer = function(parent, position){
			var
			$el = $('<div class="kpireview-box-row-' + position + '"/>')
				.appendTo(parent);
			return $el;
		};

		// Build container
		el.container = $('<div class="kpireview-container"/>')
			.insertBefore(self)
			.append(self);

		// Build button
		el.button = $('<button class="kpireview-button"/>')
			.text('KPI Review')
			.click(function(){
				el.box.container.show();
				$(this).hide();
				fn.getContent();
			});

		// Build float box container
		el.box.container = $('<div class="kpireview-box"/>')
			.appendTo(el.container)
			.hide();

		// Box draggable header
		el.box.header = $('<div class="kpireview-box-header"/>')
			.html('<span>Loading...</span>')
			.appendTo(el.box.container)
			.mousedown(fn.startMove)
			.mouseup(fn.endMove);
		el.box.close = $('<a href="#" class="kpireview-box-close"/>')
			.text('X')
			.attr('title', 'close')
			.mousedown(function(e){
				e.preventDefault();
				e.stopPropagation();
				pr.drag = false;
			})
			.click(function(e){
				e.preventDefault();
				e.stopPropagation();
				el.box.container.hide();
				el.button.show();
			})
			.appendTo(el.box.header);

		// Box main content
		el.box.content = $('<div class="kpireview-box-content"/>')
			.bind('mousewheel DOMMouseScroll', function(e){
				var delta = e.wheelDelta || -e.detail;
				this.scrollTop += (delta < 0 ? 1 : -1) * 30;
				e.preventDefault();
			})
			.appendTo(el.box.container);

		// Wait until image loads before executing
		self.load(function(){
			el.button.appendTo(el.container);
		});
	});
}
</script>
<style type="text/css">
.kpireview-container { position:relative; }
.kpireview-button { position:absolute; bottom:0; right:0; display:block; }
.kpireview-box { position:absolute; top:40px; left:25px; width:375px; min-width:300px; min-height:100px; background-color: #fff; border:#000 1px solid; resize:both; z-index:100; }
.kpireview-box-header { padding:10px 0 10px 10px; cursor:move; background-color:#009; color:#fff; font-weight:bold; font-size:8pt; min-height:25px; }
.kpireview-box-header-move { background-color:#66f; color:#000; cursor:move; }
.kpireview-box-header span { width:325px; display:inline-block; }
.kpireview-box-close { padding-right:10px; float:right; display:inline-block; font-weight:bold; cursor:pointer; cursor:hand; text-shadow: 0px 1px 1px #b8b8b8; color:#8e8e8e; font:25px "Arial Black", Gadget, sans-serif; }
a.kpireview-box-close { text-decoration:none; }
a.kpireview-box-close:hover, a.kpireview-box-close:active { color:#f00; }
.kpireview-box-content { padding:0px; overflow:auto; background-color:#cecece; }
.kpireview-box-content-addhistory {}
.kpireview-box-content-sectiontitle { padding:5px 0 0 12px; color:#666; font-weight:bold; font-style:italic; }
.kpireview-box-row-x { background-color:#fff; border:2px solid #898989; display:none; padding:1px; }
.kpireview-box-val-x { color:#000; font-weight:bold; padding:10px; cursor:pointer; cursor:hand; border-bottom:1px solid #707070; border-top:1px solid #fff;
background: rgb(224,224,224); /* Old browsers */
/* IE9 SVG, needs conditional override of 'filter' to 'none' */
background: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pgo8c3ZnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDEgMSIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSI+CiAgPGxpbmVhckdyYWRpZW50IGlkPSJncmFkLXVjZ2ctZ2VuZXJhdGVkIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9IjAlIiB5MT0iMCUiIHgyPSIwJSIgeTI9IjEwMCUiPgogICAgPHN0b3Agb2Zmc2V0PSIwJSIgc3RvcC1jb2xvcj0iI2UwZTBlMCIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgICA8c3RvcCBvZmZzZXQ9IjEwMCUiIHN0b3AtY29sb3I9IiM4OTg5ODkiIHN0b3Atb3BhY2l0eT0iMSIvPgogIDwvbGluZWFyR3JhZGllbnQ+CiAgPHJlY3QgeD0iMCIgeT0iMCIgd2lkdGg9IjEiIGhlaWdodD0iMSIgZmlsbD0idXJsKCNncmFkLXVjZ2ctZ2VuZXJhdGVkKSIgLz4KPC9zdmc+);
background: -moz-linear-gradient(top,  rgba(224,224,224,1) 0%, rgba(137,137,137,1) 100%); /* FF3.6+ */
background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(224,224,224,1)), color-stop(100%,rgba(137,137,137,1))); /* Chrome,Safari4+ */
background: -webkit-linear-gradient(top,  rgba(224,224,224,1) 0%,rgba(137,137,137,1) 100%); /* Chrome10+,Safari5.1+ */
background: -o-linear-gradient(top,  rgba(224,224,224,1) 0%,rgba(137,137,137,1) 100%); /* Opera 11.10+ */
background: -ms-linear-gradient(top,  rgba(224,224,224,1) 0%,rgba(137,137,137,1) 100%); /* IE10+ */
background: linear-gradient(to bottom,  rgba(224,224,224,1) 0%,rgba(137,137,137,1) 100%); /* W3C */
filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#e0e0e0', endColorstr='#898989',GradientType=0 ); /* IE6-8 */ }
.kpireview-box-row-z { background-color:#fff; border:2px solid #898989; display:none; }
.kpireview-box-val-z { background-color:#666; color:#fff; font-weight:bold; padding:8px; cursor:pointer; cursor:hand; border-bottom:1px solid #464646; border-top:1px solid #8b8b8b; }
.kpireview-box-row-y { background-color:#fff; border:2px solid #666; display:none; padding:8px; }
.kpireview-box-val-y { background-color:#ffc; color:#000; padding:8px; cursor:pointer; cursor:hand; }
.kpireview-box-comment { background-color:#fff; padding-top:8px; }
.kpireview-box-comment-info { background-color:#737373; color:#c3c3c3; font-size:8pt; padding:5px; }
.kpireview-box-comment-msg { padding:8px 0px; white-space:pre-wrap; }
.kpireview-box-comment-tools { padding:5px; }
.kpireview-box-comment-add, .kpireview-box-comment-add:link, .kpireview-box-comment-add:visited { font-size:8pt; color:#666; font-weight:bold; padding-left:15px; }
.kpireview-box-comment-tools-edit, .kpireview-box-comment-tools-edit:link, .kpireview-box-comment-tools-edit:visited { font-size:8pt; color:#666; font-weight:bold; padding-left:15px; }
.kpireview-box-comment-tools-delete, .kpireview-box-comment-tools-delete:link, .kpireview-box-comment-tools-delete:visited { font-size:8pt; color:#666; font-weight:bold; padding-left:15px; }
.kpireview-box-comment-msg-edit { padding:0px; width:300px; height:100px; display:block; }
.kpireview-box-comment-msg-edit, .kpireview-box-comment-msg-edit:link, .kpireview-box-comment-msg-edit:visited { font-size:8pt; color:#666; font-weight:bold; padding-left:15px; }
.kpireview-box-comment-msg-save {}
.kpireview-box-comment-msg-cancel {}
.kpireview-box-comment-msg-cancel, .kpireview-box-comment-msg-cancel:link, .kpireview-box-comment-msg-cancel:visited { font-size:8pt; color:#666; font-weight:bold; padding:0px 15px; }
.kpireview-box-comment-tools-delete {}
.kpireview-created { white-space:nowrap }
.kpireview-created span { font-weight:bold; }
.kpireview-updated { white-space:nowrap }
.kpireview-updated span { font-weight:bold; }
.kpireview-by { white-space:nowrap }
.kpireview-by span { font-weight:bold; }
.kpireview-na { font-weight:bold; }
</style>
<!--[if gte IE 9]>
  <style type="text/css">
    .kpireview-box-val-x {
       filter: none;
    }
  </style>
<![endif]-->
{/literal}
