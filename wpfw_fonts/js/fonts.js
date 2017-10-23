jQuery(document).ready(function($) {
	
	$(".select-box-container").click(function() {
		if($(this).hasClass("opened")) {
			$(this).parent().children(".select-box-dd").css({display: 'none'});
			$(this).removeClass("opened");
		} else {
			$(this).parent().children(".select-box-dd").css({display: 'block'});
			$(this).addClass("opened");
		}
	});

	var options = { 
  	success : function() {
   		$(".font-item-content span").fadeIn(200);		
    }
 	} 
	$('#FontType').ajaxForm(options); 	

	$("#TextType").change(function() {
		
		if ($(this).val() == 0) { testdrive = $("#CustomText").val(); }
		if ($(this).val() == 1) { testdrive = 'Ag'; }
		if ($(this).val() == 3) { testdrive = 'AaBbCcDdEeFfGg ...'; }
		if ($(this).val() == 4) { testdrive = '0123456789'; }
		if ($(this).val() == 5) { testdrive = 'ff fi fl ffi ffl st ct'; }
		if ($(this).val() == 6) { testdrive = '.,/\'][=-`<>?"|!@#$%^&*()_+{}'; }
		
		if ($(this).val() == 0) {
			$("#CustomText").fadeIn(200);
		}
		else {
			$("#CustomText").fadeOut(200);
		}
		$(".font-item-content span").fadeOut(200, function() { 
		if ($("#TextType").val() == 2) {
			
			$(".font-item-content span").each(function() {
				testdrive = $(this).parent().parent().children(".font-item-header").html();
				$(this).html(testdrive);
			});
		}
		else {
			$(".font-item-content span").html(testdrive);
		}
		});
		
		
		$('#FontType').submit();
	});	
	
	$("#CustomText").keyup(function() {
		
		$(".font-item-content span").html($(this).val());
		$('#FontType').submit();
		
	});	
		



	
	$(".check").click(function() {
		var id = $(this).attr("href").replace("#","");
		
		$(this).parent(".font-item").addClass("loading");
		$(this).html("");
		
		if ($("#check-"+id).parent(".font-item").hasClass("on")) {
			$("#installaction").val("-1");
		}
		else {
			$("#installaction").val("1");
		}
		
		var options = { 
   		success : function() {
   			if ($("#check-"+id).parent(".font-item").hasClass("on")) {
        	$("#check-"+id).parent(".font-item").removeClass("loading").removeClass("on");
        	$("#check-"+id).html("Add To Collection");
        }
        else {
        	$("#check-"+id).parent(".font-item").removeClass("loading").addClass("on");
        	$("#check-"+id).html("Remove From Collection");
        }
    	} 
		}
		$('#install-font').ajaxForm(options); 			
		
		$("#font_id").val(id);
		$('#install-font').submit();
		
		return false;
	});
	
	$( ".slider-line" ).slider({
		range: "min",
		min: 11,
		max: 110,
		slide: function( event, ui ) {
			$(this).parent("div").children(".slider-text").val(ui.value );
			$(".font-item-content span").css({fontSize: ui.value});
		},
		create: function(event, ui) {
			$(this).children(".ui-widget-header").html('<span></span>');
			$(this).children(".ui-slider-handle").html('<span><span></span></span>');
		},
		stop: function( event, ui ) {
			$('#FontType').submit();
		}, 
		change: function( event, ui ) {
			$('#FontType').submit();
			$(".font-item-content span").css({fontSize: ui.value});
		}
	});
	
	$( ".slider-line" ).each(function() {
		var sval = $(this).parent("div").children(".slider-text").val();
		$(this).slider("value", sval);
	});
	
	$(".slider-text").change(function() {
		if($(this).val() > 110) { $(this).val(110); }
		if($(this).val() < 11) { $(this).val(11); }
		$(this).parent("div").children(".slider-line").slider("value", $(this).val());
	});	
	
	
	$(".select-box-options").children("li").children("a").click(function() {
			
			var ival = $(this).attr("href").replace("#", "");
			$(this).parent().parent().parent().parent().children("input").val(ival).trigger('change');
			
			var tval = $(this).html();
			if (tval.length > 40) {
				tval = tval.substr(0,40)+'...';
			}
			var newval = '<span class="controler">&#8862;</span>'+tval;
			
			$(this).parent().parent().parent().parent().children(".select-box-container").removeClass("opened");
			$(this).parent().parent().parent().parent().children(".select-box-container").children(".select-box").html(newval);
			$(".select-box-dd").fadeOut(200);
			
		
	});	
	
});