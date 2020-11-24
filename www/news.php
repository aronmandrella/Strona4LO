<?php
	session_start();
	
	//-------------------------------------------------------------//
	//SPRAWDZENIE CZY UŻYTKOWNIK TO ADMINISTRATOR-//
	//-------------------------------------------------------------//
	
	if (isset($_SESSION['id']))
		$admin_mode = true;
	else
		$admin_mode = false;
	
	//-------------------------------------------------------------//
	//-----------------------FUNKCJE----------------------------//
	//-------------------------------------------------------------//
	
	define("SQL_ERROR", "We wprowadzonym zapytaniu SQL był błąd.");
	define("NEWS_DATE_ERROR", "Wprowadzono złą datę.");	
	
	//db_getNewsByDate
	//Korzysta z bazy danych z którą połączenie jest zapisane w obiekcie $connection.
	//Z tabeli o określonej nazwie wyciaga 3 częściowe newsy (nagłówek, HTML, data)
	//i składa wkłada je do divów w taki sposób [post [header] [main] [footer] ].
	//Kolumny w bazie danych muszą mieć nazwy: name, content, date.
	//Parametry pozwalają określić zakres daty ilość pobieranych newsów i offset pobierania.
	//Zwraca dane za pomocą echo.
	//W nagłówku dodaje również dwa guziki fontello (pencil, trash) w których będą zapisane:
	//data-id (id newsa w bazie danych) i data-function = Usuń/Edytuj.
	//To czy te guziki będą widoczne określa $show_buttons.

	function db_getNewsByDate(
			$connection, $table,
			$year, $month, $day,
			$offset, $limit, $show_buttons=false)
	{		
		//Początek zapytania SQL
		$sql = sprintf('SELECT * FROM %s ',
			$connection->real_escape_string($table));
		
		//Część zapytania SQL dotycząca daty
		if	($year>0 && $month>0 && $day>0)
		{
			$sql = $sql.sprintf('WHERE year(date)=%s AND month(date)=%s AND day(date)=%s ',
				$connection->real_escape_string($year),
				$connection->real_escape_string($month),
				$connection->real_escape_string($day));
		}
		else if ($year>0 && $month>0 && $day==0)
		{
			$sql = $sql.sprintf('WHERE year(date)=%s AND month(date)=%s ',
				$connection->real_escape_string($year),
				$connection->real_escape_string($month));
		}
		else if	($year>0 && $month==0 && $day==0)
		{
			$sql = $sql.sprintf('WHERE year(date)=%s ',
				$connection->real_escape_string($year));
		}
		else if	($year==0 && $month==0 && $day==0)
		{
			//ok
		}
		else
		{
			echo NEWS_DATE_ERROR;
		}
		
		//Część zapytania SQL dotycząca sortowania i ogarniczenia ilości wyników
		$sql = $sql.sprintf('ORDER BY date DESC LIMIT %s OFFSET %s',
				$connection->real_escape_string($limit),
				$connection->real_escape_string($offset));		
		
		//To z jakiegoś powodu naprawia polskie znaki.
		$connection -> query("SET NAMES 'utf8'");
							
		if( $result = @$connection->query($sql) )
		{		
			while($row = $result->fetch_assoc())
			{
				echo '<div class="post" data-id="'.$row['id'].'" style="display: none;">';
				echo 	'<div class="header">';
				
				//DOSTĘPNE TYLKO DLA ADMININISTRATORA
				if($show_buttons)
				{
					echo			'<button data-id="'.$row['id'].'" data-function="Edytuj" title="Edytuj"><i class="icon-pencil"></i></button>';
					echo			'<button data-id="'.$row['id'].'" data-function="Usuń" title="Usuń"><i class="icon-trash"></i></button>';	
				}			
				
				echo			'<span data-id="'.$row['id'].'" data-post_part="name" class="title">'.$row['name'].'</span>';
				echo		'</div>';
				echo 		'<div data-post_part="content" data-id="'.$row['id'].'" class="main">'.$row['content'].'</div>';
				echo 	'<div class="footer">'.$row['date'].'</div>';
				echo '</div>';
				
				echo '<script>$(\'.post[data-id="'.$row['id'].'"]\').fadeIn();</script>';
			}
			$result->free();								
		}
		
		else
			echo SQL_ERROR;
	}
	
	//numToMonth :: UTILITY
	//Zamienia numer miesiąca na jego nazwę po polsku.
	
	function numToMonth($month_number)
	{
		switch ($month_number)
		{
			case 1:   return "Styczeń";
			case 2:   return "Luty";
			case 3:   return "Marzec";
			case 4:   return "Kwiecień";
			case 5:   return "Maj";
			case 6:   return "Czerwiec";
			case 7:   return "Lipiec";
			case 8:   return "Sierpień";
			case 9:   return "Wrzesień";
			case 10: return "Październik";
			case 11: return "Listopad";
			case 12: return "Grudzień";
			default: return "Zły numer miesiąca.";
		}
	}
		
	//db_getListFromRowsByDate
	//Tworzy na podstawie wskazanej tabeli posiadającej kolumnę "date" listę lat, z podlistą miesięcy.
	//Lista bedzie miała określoną klasę. Na liście obok roku i miesiąca będzie cyfra określająca ilość
	//mającą datę pasującą do danego roku/miesiącu.
	//Każdy element listy będzie miał dane date-year, date-month i date-count.
	//count określa ilość pasujących rekordu dla miesiąca w roku, roku i ilość wszystkich rekordów (w głownym ol).
	//Dla elementu określającego rok data-month="0".
	
	function db_getListFromRowsByDate(
			$connection, $table, 
			$list_class)
	{	
		$number_of_rows = 0;
	
		//Zliczenie wszystkich rzędów.
		$sql = sprintf("SELECT COUNT(*) AS 'count' FROM %s",
				$connection->real_escape_string($table));
				
		if( $count = @$connection->query($sql) )
		{		
			$number_of_rows = $count->fetch_assoc()["count"];					
			$count->free();
		}		
		
		//Pytanie wyciągające pojawiące się w bazie danych różne daty wg roku, zliczające ilość wpisów w danym roku.
		$sql = sprintf("SELECT DISTINCT year(date) AS year, COUNT(*) AS 'count' FROM %s GROUP BY year(date) ORDER BY year DESC",
				$connection->real_escape_string($table));
		
		if( $years = @$connection->query($sql) )
		{	
			echo '<ol class="'.$list_class.'" data-count="'.$number_of_rows.'">';
		
			while($year = $years->fetch_assoc())
			{
				//Otwarcie lisy roku:
				//<li data-month="0" data-year="2017">  2017(20)<ol>
				echo '<li data-month="0" data-year="'.$year['year'].'" data-count="'.$year['count'].'">&nbsp;&nbsp;'.$year['year']." (".$year['count'].")<ol>";
				
				//Pytanie wyciągające pojawiące się w bazie danych miesiące w danym roku, zliczające ilość wpisów w danym miesiącu.
				$sql = sprintf("SELECT month(date) AS month, COUNT(*) AS 'count' FROM %s WHERE year(date)=%s GROUP BY month(date) ORDER BY month DESC",
					$connection->real_escape_string($table),
					$year['year']);
									
				if( $months = @$connection->query($sql) )
				{		
					while($month = $months->fetch_assoc())
						//Dane miesiące:
						//<li data-month="2" data-year="2017">Luty (8)</li>
						echo '<li data-month="'.$month["month"].'" data-year="'.$year['year'].'" data-count="'.$year['count'].'">'.numToMonth($month["month"]).' ('.$month['count'].')</li>';				
													
					$months->free();
				}
				
				//Zamknięcie listy roku.
				echo "</ol></li>";
			}
			$years->free();
			
			echo '</ol>';
		}
		
		else
			echo SQL_ERROR;
	}
	
	
	//-------------------------------------------------------------//
	//--------------POŁĄCZENIE Z BAZĄ DANYCH--------------//
	//-------------------------------------------------------------//
		
	//Załączenie pliku z danymi do łączenia się z bazą danych.
	require_once "php/connect.php";
	
	//Nawiązywanie połączenia z bazą danych.
	$connection = @new mysqli($host, $db_user, $db_pass, $db_name);						
	if($connection->connect_errno != 0)
	{
		echo "Pojawił się błąd przy próbie połączenia się z bazą danych: ".$connection->connect_errno;
				//echo "Opis: ".$connection->connect_error;
		exit();
	}
	
	
	//-------------------------------------------------------------//
	//--------------OBSŁUGA FORMULARZY AJAX---------------//
	//-------------------------------------------------------------//	

	
	//Operacje na bazie danych.
	if(!empty($_POST))
	{
		if(isset($_POST["function"]))
		{
			switch($_POST["function"])
			{
				case "getNews":
					db_getNewsByDate($connection , "news",	$_POST['year'], $_POST['month'], $_POST['day'], $_POST['offset'], $_POST['limit'], $admin_mode); break;
				case "getNewsList":
					db_getListFromRowsByDate($connection, "news", "nav"); break;
				default:
					echo "Nie zdefiniowano funkcji o nazwie: ".$_POST["function"];
			}	
		}
		else
		{
			echo "Nie wybrano funkcji.";
		}
		
		$connection->close();
		exit();
	}
?>





<?php require_once "_commonHTML_head(meta+fonts+style).html"; ?>
<link rel="stylesheet" href="style/news.css" type="text/css"/>
<link rel="stylesheet" href="style/news_post.css" type="text/css"/>

<style>
.post
{
	transition: all ease-in-out 0.5s;
}
.selected
{
	position: fixed;
	top: 50%;
	left: 50%;
	transform: scale(1.05, 1.05) translate(-50%,-50%);
	z-index: 200;
	box-shadow: 0px 0px 200px 10px #000;
	height: 90vh;
}
.scroll
{
	height: 85%;
	overflow-y: scroll;
}
#info2 input
{
	background: none;
	font-size: 1.1em;
	border: none;
	padding: none;
	margin: none;
	color: #ddd;
}
</style>


<body>
	<?php require "header.html"; ?>
			
	<div id="columns">	
	
		<div class="sidebar"></div>
		
		<div class="container">
		<div id="info2">
		<?php if(!$admin_mode)
			echo 'Co u nas słychać?';
			else
			{
				echo 	'<form method="post" action="admin.php">';
				echo 	'<input type="hidden" name="page" 	 value="news"/>';
				echo 	'<input type="hidden" name="function" value="Stwórz nowy element"/>';
				echo 	'<input type="submit" value="Stwórz nowy news"/>';
				echo	'</form>';
			}
		?>
		</div>
				
			<form method="post" action="admin.php" id="edit_form" target="_blank">
				<input type="hidden" name="page" 	 value="news"/>
				<input type="hidden" name="function" value="Edytuj"/>
			</form>	

			<div class="posts"></div>	
		</div>
		
	</div>
	
	<?php require "footer.html"; ?>
</body>
</html>



<?php
	//-------------------------------------------------------------//
	//------ZAMKNIĘCIE POŁACZENIA Z BAZĄ DANYCH-------//
	//-------------------------------------------------------------//
	$connection->close();
?>



<script src="_commonJS_DynamicBG.js"></script>
<script src="javascript/UntilFocusout.js"></script>

	
<script>

	//Obsługa zdjęć na stronie
	function imgAction()
	{			
		$(".post img").off("click");
		$(".post img").on("click", function()
		{
			var link = $(this).attr("src");
			window.open("photo.php?photo="+link);
		});
	}

	
	
	//Parametry określające jakie newsy wczytać.
	//Przy pierwszym otwarciu strony wczutuje ostatnie 20 newsów.
	
	var setMonth = 0;
	var setYear	= 0;
	var setDay 	= 0;
	var setOffset = 0;
	var setLimit 	= 3;
	var setCount = 0;
	

	
	//Funkcja obsługi guzików administratora.	
	function adminButtonsAction()
	{
		if ( $(this).data("function")=="Edytuj" )
		{
			$('#edit_form').append('<input type="hidden" name="id" value="'+$(this).data("id")+'" />');
			$("#edit_form").submit();
		}
		if ( $(this).data("function")=="Usuń" )
		{
			var last_deleted_post = $(this).parent().parent();
			
			$.ajax(
			{
				url: 			"admin.php",
				type: 		"post",
				data: 		{ function: "Usuń", page: "news", id: $(this).data("id") },
				
				success: 	function(response)
				{
					//Zaktualizowanie listy
					LoadNewsList();
					
					//Ukrycie usuniętego newsa (bez przeładowania)
					last_deleted_post.css("overflow","hidden");
					last_deleted_post.animate({opacity: '0'}, 200, function(){ last_deleted_post.remove();});
				}
			});
		}
	}
	
	
	//synchronizacja z ajaxem.
	//domyślnie na początku aż się załadują pierwsze newsy.
	var wait_for_ajax = true;
	
	

	//Obsługa edytora newsów inline
	function HTMLToTextarea()
	{
		$(this).data("old_content", $(this).html());
	
		$(this).closest(".post").toggleClass("selected");
		$(this).closest(".post").children(".main").toggleClass("scroll");
		//$(this).html('<textarea style="font-family: inherit; border: none; padding: none; margin: none; outline: none; font-size: inherit; background: inherit; color: inherit; width: '+$(this).width()+'px; height: '+$(this).height()+'px; ">'+$(this).html()+'</textarea>');
		$(this).attr("contenteditable", true);
		//$(this).find('textarea').focus();
		$(this).focus();
	}
	function TextareaToHTML()
	{	
		if ( confirm("Zapisać zmiany?") )
		{
			var content = "";
			var name = "";
			
			switch($(this).data("post_part"))
			{
				case "content":
					//var content = $(this).find('textarea').val();
					var content = $(this).html();
					break;
				case "name":
					//var name = $(this).find('textarea').val();
					var name = $(this).html();
					break;
			}
			
			$.ajax(
			{
				url: 			"admin.php",
				type: 		"post",
				data: 		{ function: "quickedit", page: "news", id: $(this).data("id"), content : content, name : name},
				
				success: 	function(response)
				{
					
				}
			});
			
			//$(this).html($(this).find('textarea').val());
		}
		else
			$(this).html($(this).data("old_content"));
		
		$(this).attr("contenteditable", false);
		
		$(this).removeData("old_content");
		$(this).closest(".post").toggleClass("selected");
		$(this).closest(".post").children(".main").toggleClass("scroll");
	}
	
	
	
	//Funkcja wczytująca aktualności.
	//Wie jakie dzięki zmiennym globalnym.
	function LoadNews()
	{
		//Gdy coś jeszcze zostało.
		if((setOffset<setCount)==false)
			wait_for_ajax = false;
		else
		$.ajax(
		{
			url: 			"news.php",
			type: 		"post",
			data: 		{ function: "getNews", month:  setMonth, year: setYear, day: setDay, offset: setOffset, limit: setLimit},
				
			success: 	function(response)
			{
				//Zmieniono zakres czasowy.
				if(setOffset==0)
					$(".posts").html(response);
				else
					$(".posts").append(response);
				
				wait_for_ajax = false;
				
				//Aktualizacja offsetu.
				setOffset += setLimit;
				
				//Przypisanie akcji do guzików.
				$('.post .header button').click(adminButtonsAction);
				
				
				<?php
				//////////////////////////////////////////////////////////////
				
				//Dodaje event edycji tylko tam gdzie jeszcze go nie ma
				
				if($admin_mode)
				echo
					'$( ".post .main, .post .header .title").each(function()
					{
						if( ! $(this).data("onUntilFocusout_event") )
						{
							onUntilFocusout($(this), "dblclick", HTMLToTextarea, TextareaToHTML);
							$(this).data("onUntilFocusout_event", "true");
							$(this).on("truefocusout", function(){console.log("true out");});
							$(this).on("truefocusin", function(){console.log("true in");});
						}
					});';
				else
					echo 'imgAction();';
				
				?>
			}
		})
	}
	
	
	//facebookowy styl ładowania newsów.
	$(window).scroll(function()
	{
		if($(window).scrollTop() + $(window).height() > $(document).height() - 150)
		{
			if( !wait_for_ajax)
			{
				wait_for_ajax = true;
				LoadNews();
			}
		}
	});
		
	
	//Funkcja wczytująca listę newsów.
	function LoadNewsList()
	{
		$.ajax(
		{
			url: 			"news.php",
			type: 		"post",
			data: 		{ function: "getNewsList"},
				
			success: 	function(response)
			{
				//Wrzucenie listy do diva.
				$(".sidebar").html(response);
				
				//Oświerzenie strony
				//Zaktualizowanie informacji o wszystkich wiadomościach
				//I wczytanie dopiero po policzeniu.
				if(setYear==0 && setYear==0)
				{
					setCount = $('ol.nav').data("count");
					LoadNews();
				}
						
				//Przypisanie akcji do elementów listy.
				$("ol.nav li ol li").click(function()
				{	
					//Nie można przełączyć miesiaca gdy wczytywne są nowe newsy.
					if( !wait_for_ajax)
					{
						setMonth 	= $(this).data("month");
						setYear 	= $(this).data("year");
						setCount 	= $(this).data("count");
						
						//Wyzerowanie offsetu
						setOffset = 0;
						
						LoadNews();
					}
				});
			}
		});
	}
	
	//Pierwsze wczytanie newsów i listy po odwarciu strony.
	LoadNewsList();
	
</script>