/*
	(jQuery InVision) SlideRotator
	Developed by Kristijan Burnik
	February 2010
*/
$.fn.sliderotator = function(options) {
	
	var options = options;
	
	var defaults = {
		animDuration:400,
		swapDuration:5000,
		initDelay:5000,
		itemClass:'.slideItem'
	}
	
	if (!options) {
		options=defaults;
	} else {
		for (property in defaults) {
			if (options[property]==null) options[property]=defaults[property];
		}
	}
	
	$(this).each(function() {
		if (typeof this.sliderotator == 'object') return this.sliderotator;
		
		var $container = $(this);
		
		var o = options;
		var t = {
			options:options,
			$container:$container,
			$items:{},
			$status: {},
			current:0,
			paused:false, // todo: implement
			allowContinue:false,
			allowPause:true,
			count:0,
			showInProgress:false,
			displayStatus:function() {
				// t.$status.html((t.current+1)+' / '+(t.count));
			},
			show:function(index,anim) {
				if (t.showInProgress) return false;
				t.showInProgress = true;
				// show status nav link current
				t.$status.find("a:eq("+index+")").addClass("current").siblings().removeClass("current");
				
				var $current = t.$items.filter(":visible");
				var $next = $(t.$items[index]);
				
				$next.css({zIndex:1});
				$current.css({zIndex:10});
				//t.$container.prepend($next);
				//t.$container.prepend($current);
				
				$next.show();	
				var anim = (anim) ? o.animDuration : 0;
				
				t.displayStatus();
				$current.fadeOut(anim,function() {
					t.showInProgress = false;
				});
			},
			showNext:function() { 	
				if (t.showInProgress) return false;
				if (t.count==1) return true;
				t.current = (t.current+1)%t.count;
				t.show(t.current,true);
			},
			showPrev:function() {
				if (t.showInProgress) return false;
				if (t.count==1) return true;
				t.current = (t.current-1)%t.count;
				if (t.current<0) t.current = t.count-1;
				t.show(t.current,true);
			},
			slideShow:function() {
			
				if (!t.paused) {
					t.showNext();
				} 
				
				setTimeout(function() {
					t.slideShow();
				},o.swapDuration);
			},
			pauseSlideShow:function() {
				t.paused = true;
			},
			continueSlideShow:function() {			
				t.paused = false; 

				/*setTimeout(function() {
						t.slideShow();
				},o.swapDuration);*/
			},			
			prepareSlides:function() {
				t.$container.css({position:"relative"});
				t.$items.css({position:'absolute',left:'0px',top:'0px'});
				t.$items.filter(":gt(0)").hide();
				t.displayStatus();
				
			},

			construct:function() {
				t.$container
					.mouseenter(function(e){
						t.pauseSlideShow();	
					}).mouseleave(function(e){
						t.continueSlideShow();
					});
			
				t.$items = t.$container.find(o.itemClass);
				t.$status = $(o.statusAgent);
				t.$next = $(o.nextAgent);
				t.$prev = $(o.prevAgent);
				t.count = t.$items.length;
				t.prepareSlides();
				
				// show first item
				$(t.$items[0]).show();
				
				// enable quick links
				t.$status.html("");
				for (var i = 0; i < t.count; i++) {
					t.$status.append(t.constructNavLink(i));
				}
				
				if (t.$next.length > 0) t.$next.click(function() {t.showNext(); $(this).blur();});
				if (t.$prev.length > 0) t.$prev.click(function() {t.showPrev(); $(this).blur();});
				
				if (o.slideShow) setTimeout(t.slideShow,o.initDelay);
				return t;
			},
			skipTo:function(index,obj) {
				if (t.current==index) return true;
				t.current = index%t.count;
				t.show(t.current,true);
				return true;
 			},
			constructNavLink:function(index) {
				var $link = $("<a>").attr({
					"href":'javascript:',
					"title":index
				}).addClass("navSkipTo").html("0"+(index+1)).click(function() {
					t.skipTo($(this).attr("title"));
					$(this).blur();
				});
				
				
				// set first to be the current one
				if (index==0) $link.addClass("current"); 
				
				return $link;
			}
		}
		
		t.construct();
		this.sliderotator = t;
	});

}