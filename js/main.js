$(document).ready(function(){	
	
    // Disable href="#" links
    $('a').click(function(){if ($(this).attr('href') == '#') {return false;}});
    $('a').live('click', function(){if ($(this).attr('href') == '#') {return false;}});

    // tooltip
    $("a[rel^='tooltip']").tooltip();


    // To Top Button
    $(function(){
        $().UItoTop({ easingType: 'easeOutQuart' });
    });

});

function go(url) {
	window.location = url;
}

function travel(url,gid) {
    //pageTracker._trackPageview(gid);
    //$('#traveldepart').show();
    var load = window.open(url,'','');
}
