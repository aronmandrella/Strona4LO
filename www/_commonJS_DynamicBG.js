//Funkcja obsługująca tło z paralaksą w logo, menu i klasie ".container"
	
function DynamicBG()
{
	var pos = $(window).scrollTop();
	var pos_text = "0px "+Math.round(pos/2)+"px";
	$(".container").css("background-position", pos_text);
	$("#info").css("background-position", pos_text);
		
	if($(window).width() < 768)
		var pos_text = "center "+Math.round(pos/2)+"px";
	else
		var pos_text = "center "+Math.round(-45 + pos/2)+"px";
			
	$("#main_header").css("background-position", pos_text);
		
	if($(window).width() < 768)
		var pos_text = "center "+Math.round(-35 + pos/6)+"px";
	else
		var pos_text = "center "+Math.round(-90 + pos/6)+"px";
			
	$("#school").css("background-position", pos_text);
};
	
$(window).scroll(DynamicBG);
$(window).resize(DynamicBG);