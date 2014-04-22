// Checkable jquery plugin by Kristijan Burnik


// checkable item
$.fn.extend({
	checkable:function(options,selector) {
		
		var $t = $(this);
		
		var methods = {
		
		
		}
		
		var default_options = {
			  checkbox:'input[type=checkbox]:first'
			, checkedClass:'checked'
			, uncheckedClass:''
			, checked:function(){ console.log('checkable_checked event fired;',$(this)); }
			, unchecked:function(){ console.log('checkable_unchecked event fired;',$(this)); }
			, buttons: {
				 check: false
				, uncheck: false
				, invert: false
			}
			
		};
		
		
		var setCheckStateClass = function(el,state) {
			if (state) {
				$(el).addClass(o.checkedClass).removeClass(o.uncheckedClass);
			} else {
				$(el).removeClass(o.checkedClass).addClass(o.uncheckedClass);
			}
		}
	
		if (typeof options == 'string') {
			// console.log(["checkable action",options,this,$t]);
			var o = $.extend(default_options,$t.__checkable_options);
			var $c = $t.find(o.checkbox);
			
			switch(options) {
			
				case 'check': // check current context
					$c.attr('checked','checked');
					setCheckStateClass($t,true);
					$t.trigger("checkable_checked",$t);
					
					return $t;				
				break; 
				
				case 'uncheck': // uncheck current context
					
					$c.removeAttr('checked');
					setCheckStateClass($t,false);					
					$t.trigger("checkable_unchecked",$t);
					
					return $t;
				break;
				
				case 'invert': // invert current context
					$t.each(function(){
						if ( $(this).hasClass(o.checkedClass) ) {
							$(this).checkable('uncheck');
						} else {
							$(this).checkable('check');
						}
					});
					return $t;
				break;
				
				case 'checked': // get checked elements or set checked event
					if (typeof selector == 'function') {
					
						$t.bind("checkable_checked",selector);
						return $t;
						
					} else {
						return $t.filter(function(){
							return $(this).hasClass(o.checkedClass);
						});
					}
				break;
				
				case 'unchecked': // get unchecked elements or set unchecked event
					if (typeof selector == 'function') {
					
						$t.bind("checkable_unchecked",selector);
						return $t;
						
					} else {
						return $t.filter(function(){
							return ! $(this).hasClass(o.checkedClass);
						});
					}
				break;
				
			}
			
			
		} else if (typeof options == 'boolean') {		
				return ($t.checkable('checked').length == $t.length) == options;			
		}
	
	
		var o = $.extend(default_options,options);
	
		
	
		
		
		$(o.buttons.check).click(function(){
			$t.checkable('check');
		});
		
		$(o.buttons.uncheck).click(function(){
			$t.checkable('uncheck');
		});
		
		$(o.buttons.invert).click(function(){
			$t.checkable('invert');
		});
		
		
		$t.bind("checkable_checked",o.checked);
		$t.bind("checkable_unchecked",o.unchecked);
		
		$t.each(function() {
			$(this).__checkable_options = o;
			
			var t = this;
			
			
			
			$(this).find(o.checkbox).change(function(){
				
				console.log(['changed state!!',o]);
				
				//$(this)-
				// setCheckStateClass(t,$(this).is(':checked'));
				//return true;
			});
			
			
			$(this).click(function(){
				$(this).checkable('invert');	
			});
			
		});
		
		
		return $t;
	}
});
