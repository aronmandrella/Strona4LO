/*
PRZYDATNE INFO:

Focusowanie elementów na stronie:
	- $(this).focus()
		uwaga: by móc zfocusować diva musi mieć ustawiony tabindex (np na -1).
		
Sprawdzenie czy istnieje dany atryubut:
	- if( ! $(this).attr("tabindex") )
	
Wywołanie funkcji na rzecz jakiegoś obiektu (zostatnie do niej przekazany):
	- on_function.call(this)
	- lost_focus_function.call(this)
	
Zmiana kolejkowania funkcji:
	- id <-- setTimeout(function, 0) - sprawi że function wykona się na końcu obecnej kolejki
	- dzięki temu najpierw można np obsłużyć focusin a potem dopiero focusout jeśli się kliknie na coś w obiekcie który już ma focus.
	- można też takie zaplanowane wywołanie odwołać wewnątrz obsługi focusin clearTimeout( id ) [id np zapisać w this data] 
	
Przechwytywanie obiektu eventu:
	- function(event_object) {...}
	- Obiekty przechowują informację o klawiszu itp.
	- Można dzięki temu obiektowi anuluwać propagację eventu usunąć domyślną akcję itp.
	- więcej info https://api.jquery.com/category/events/event-object/	
	
	https://stackoverflow.com/questions/152975/how-do-i-detect-a-click-outside-an-element
	
Pętla foreach (na elementach jQuery):
	$( '.post .main, .post .header' ).each(function(index)
	{
		console.log( index + ": " + $( this ).text() ); 
	});
	
	- index, numer elementu w tablicy wewnętrznej jQuery
*/


//Na rzecz podanego obiektu jQ w chwili zajścia eventu określonego
//w on (click, dblclick...) wykona się funkcja on_function, i wskazane obiekty
//otrzymają focus. Po straceniu fokusu (wyjściu poza obiekt) zostanie wykonane
//lost_focus_function.
function onUntilFocusout(jQ, on_event, on_function, lost_focus_function)
{			
	function start()
	{
		$(this).off( on_event+".onUntilFocusout_"+on_event );
			
		if( ! $(this).attr("tabindex") ) $(this).attr("tabindex",-1);		
		$(this).focus();
		
		$(this).trigger(on_event+"_UntilFocusout_start");
		on_function.call(this);
	}
	
	$(jQ).on( on_event+".onUntilFocusout_"+on_event , start);

	
	function stop()
	{
		lost_focus_function.call(this);
		
		$(this).on( on_event+".onUntilFocusout_"+on_event , start);
	}
					
					
	function focusout(){
		$(this).data( 'truefocusout_timer' , setTimeout( function(){ stop.call(this); $(this).trigger("truefocusout"); }.bind(this), 0 ) ); }
			
	function keydown(e){
		if (e.which===27){ e.preventDefault(); $(this).blur(); }}
	
	function focusin(){
		if(!$(this).data('truefocusout_timer')) $(this).trigger("truefocusin");
		clearTimeout($(this).data('truefocusout_timer')); }	
	
	//Wykrycie utraty focusu.
	$(jQ).on( "focusout.onUntilFocusout_"+on_event , focusout );
	//Wykrycie kliknięcia ESC.
	$(jQ).on( "keydown.onUntilFocusout_"+on_event , keydown );
		//Wykluczenie kliknięcia wewnątrz diva.
	$(jQ).on( "focusin.onUntilFocusout_"+on_event , focusin );
}

//Usuwa z danego obiektu jQ event przypisany za pomocą onUntilFocusout.
function offUntilFocusout(jQ, on_event)
{		
	$(jQ).off(".onUntilFocusout_"+on_event);
}