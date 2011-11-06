//
// Widget opening/closing functionality
// Copyright (C) 2009 by mocapapa <mocapapa@g.pugpug.org>
//
//$(document).ready(function(){
    var hostroot = PARAMS['HTTPHOST']+'__'+PARAMS['BASEURL'].replace(/\//g,'_');
    var key = hostroot+'__widget_title_vals';
    var store = new Persist.Store('blog-enhanced');
    var titles = new Array();
    var title_vals = new Array();

    // load status
    store.get(key, function(ok, val) {
	    if (ok && val != null) {
		title_vals = val.toString().replace("\n","","g").split(",");
		// $("#debug").html('('+Persist.type + ')('+ key+')loaded('+val+')');
	    }
	});

    // collect titles of widgets
    var i=0;
    $(".portlet .header").each(function(){
	    titles[i] = $(this).html().replace("\n","","g");
	    if (title_vals[i] != 0) {
		title_vals[i] = 1;
	    }
	    
	    //      alert(titles[i]+'='+title_vals[i]);
	    if (title_vals[i++] == 0) {
		$(this).next().hide();
	    }
	});
    
    //    $("#status").html(title_vals.join());
    
    //    for (var i in titles) {
    //      alert(titles[i]+'=>'+title_vals[i]);
    //    }
    
    // on click
    $(".portlet .header").click(function(){
	    var th = $(this).html().replace("\n","","g");
	    
	    // invert val
	    for (var i in titles) {
		var ti = titles[i];
		var tv = title_vals[i];
		//        alert(ti+','+th+'<click,'+tv);
		if (ti == th) {
		    if (tv == 0) {
			$(this).next().slideDown();
			title_vals[i] = 1;
		    } else {
			$(this).next().slideUp();
			title_vals[i] = 0;
		    }
		} else {
		    if (tv != 0) {
			title_vals[i] = 1;
		    }
		}
	    }
	    
	    //$("#status").html(title_vals.join());
	    
	    // save status
	    store.set(key, title_vals.join());
	    // $("#debug").html('('+Persist.type + ')('+ key+')saved('+val+')');
	    
	});
    //    });
