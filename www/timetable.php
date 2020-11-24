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
	
	
	
	//getTimetableData
	//$mode 	- określa dla kogo pobrać plan. Możliwe wartości: "room", "teacher", "class"
	//$mode_id 	- określa identyfikator elementu z danego trybu. Np id nauczyciela dla ktorego pobrac plan.
	
	function getTimetableData($connection, $html_table_id, $mode, $mode_id, $admin_mode, $x_offset = 1, $y_offset = 2)
	{
		$sql = "";
		
		//Podstawowe elementy potrzebne do wypełnienia tabeli (współrzedne, id) i rzeczy związane z planem lekcji.
		$sql = $sql.'SELECT tt.id as id, d.id as pos_x, h.id as pos_y, 
			c.value as class, d.value as day, h.value as hour, l.value as lesson, t.value as teacher, r.value as room';
		
		//Content - to co bd w komorce
		//-----------------------------------------------------------------------------------
		switch($mode)
		{
			case "class":
				//Zawartość komorki dla planu klasy.
				$sql = $sql.', CONCAT("<div class=\"lesson\">", l.value, 
					"</div><div class=\"timetable_info\"><span data-timetable_type=\"teacher\"<span data-value=\"" , t.value , "\" data-id=\"" , t.id , "\">" ,
					t.value ,
					"</span></div><div class=\"timetable_info\"><span data-timetable_type=\"room\"<span data-value=\"" , r.value , "\" data-id=\"" , r.id , "\">" ,
					r.value , "</span></div>") as content ';
				break;
			case "room":
				//Zawartość komorki dla planu sali.
				$sql = $sql.', CONCAT("<div class=\"lesson\">", l.value, 
					"</div><div class=\"timetable_info\"><span data-timetable_type=\"teacher\"<span data-value=\"" , t.value , "\" data-id=\"" , t.id , "\">" ,
					t.value ,
					"</span></div><div class=\"timetable_info\"><span data-timetable_type=\"class\" data-value=\"" , c.value , "\" data-id=\"" , c.id , "\">" ,
					c.value , "</span></div>") as content ';
				break;
			case "teacher":
				//Zawartosc komorki dla planu nauczyciela.
				$sql = $sql.', CONCAT("<div class=\"lesson\">", l.value, 
					"</div><div class=\"timetable_info\"><span data-timetable_type=\"class\" data-value=\"" , c.value , "\" data-id=\"" , c.id , "\">" ,
					c.value ,
					"</span></div><div class=\"timetable_info\"><span data-timetable_type=\"room\" data-value=\"" , r.value , "\" data-id=\"" , r.id , "\">" ,
					r.value , "</span></div>") as content ';
				break;
		}
		//-----------------------------------------------------------------------------------
		
		//Tabele + warunki łaczenia tabeli.
		$sql = $sql.'FROM timetable tt, class c, day d, hour h, lesson l, teacher t, room r
				WHERE tt.class_id=c.id and tt.day_id=d.id and tt.hour_id=h.id and tt.lesson_id=l.id and tt.teacher_id=t.id and tt.room_id=r.id and ';
				
		//Warunek selekcji planu
		$sql =	$sql.$mode.'_id='.$mode_id;
		
		
		//echo $sql;
			
		//To z jakiegoś powodu naprawia polskie znaki.
		$connection -> query("SET NAMES 'utf8'");
							
		$name = "";					
							
		if( $result = @$connection->query($sql) )
		{		
			echo '<script>';
			while($row = $result->fetch_assoc())
			{
				echo 'FillTable("'.$html_table_id.'", '.($row['pos_x'] + $x_offset).', '.($row['pos_y'] + $y_offset).', \'';
				
					//NIE UŻYWAĆ TUTAJ W TEKŚCIE '' tylko ""!!! BO INACZEJ SIĘ WSZYSTKO ZJEBIE
					echo "<div class=\"taken\">".$row['content']."</div>";
					
					if($admin_mode)
						echo '<button data-pos_x="'.$row['pos_x'].'" data-pos_y="'.$row['pos_y'].'" data-id="'.$row['id'].'"><i class="icon-trash"></i></button>';	
					
				echo '\');';
			}
			
			echo '</script>';
		}
		
		else
			echo SQL_ERROR;
	}
	
	
	
	function getTimetableCaption($connection, $html_table_id)
	{
		$sql = "SELECT id+1 as pos_x,value as content FROM day WHERE 1";
		$pos_y=1;
		//To z jakiegoś powodu naprawia polskie znaki.
		$connection -> query("SET NAMES 'utf8'");
							
		if( $result = @$connection->query($sql) )
		{		
			echo '<script>';
			while($row = $result->fetch_assoc())
			{
				echo 'FillTable("'.$html_table_id.'", '.$row['pos_x'].', '.$pos_y.' , \'';
				
				//WSZYSTKIE INFO O NAZWIE LEKCJI NAUCZYCIELACH ITD IDĄ W TYM ECHO
				echo $row['content'];
				////////////////////////////////////////////////////////////////////////////////////////
				
				echo '\');';
			}
			echo '</script>';
		}
		
		else
		{
			echo "We wprowadzonym zapytaniu sql był błąd.";
		}

		
		$sql = "SELECT id+2 as pos_y, value as content FROM hour WHERE 1";
		$pos_x=1;	
		//To z jakiegoś powodu naprawia polskie znaki.
		$connection -> query("SET NAMES 'utf8'");
							
		if( $result = @$connection->query($sql) )
		{		
			echo '<script>';
			while($row = $result->fetch_assoc())
			{
				echo 'FillTable("'.$html_table_id.'", '.$pos_x.', '.($row['pos_y']).' ,\'';
				
				//WSZYSTKIE INFO O NAZWIE LEKCJI NAUCZYCIELACH ITD IDĄ W TYM ECHO
				echo $row['content'];
				////////////////////////////////////////////////////////////////////////////////////////
				
				echo '\');';
			}
			echo '</script>';
		}
		else
		{
			echo "We wprowadzonym zapytaniu sql był błąd.";
		}
	}
	
	//-------------------------------------------------------------------------------------------
	
	
	//Pobieranie listy z typami planów
	
		function db_getListOfEntriesByTypeID(
			$connection,  $entries_table,
			$list_class)
	{
		//Pobranie informaji o typach wpisów.
		$sql = "SELECT * FROM timetable_type";
					
		//To z jakiegoś powodu naprawia polskie znaki.
		$connection -> query("SET NAMES 'utf8'");
			
		if( $types = @$connection->query($sql) )
		{	
			echo '<ol class="'.$list_class.'">';
				
			while($type = $types->fetch_assoc())
			{
				echo '<li><div class="ol_head">'.$type["name"]."</div><ol>";
						
				//Pobranie informaji o wpisach danego typu.		
				$sql = sprintf("SELECT value, id FROM %s",
					$connection->real_escape_string($type["type_name"]));
		
				//To z jakiegoś powodu naprawia polskie znaki.
				$connection -> query("SET NAMES 'utf8'");
					
				if( $entries = @$connection->query($sql) )
				{		
					while($entry = $entries->fetch_assoc())
						echo '<li  data-timetable_type="'.$type["type_name"].'" data-value="'.$entry["value"].'" data-id="'.$entry["id"].'">'.$entry["value"].'</li>';	
						
					$entries->free();
				}
				
				echo "</ol></li>";
			}
			$types->free();
			
			echo '</ol>';
		}
		
		else
			echo SQL_ERROR;
	}
	
	
	
	//-------------------------------------------------------------//
	//--------------POŁĄCZENIE Z BAZĄ DANYCH--------------//
	//-------------------------------------------------------------//

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
				case "getTimetableData":
					getTimetableData($connection, $_POST["html_table_id"], $_POST["mode"], $_POST["mode_id"], $admin_mode); break;
				case "getTimetableCaption":
					getTimetableCaption($connection, $_POST["html_table_id"]); break;
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

<script>
	//-------------------------------------------------------------//
	//--------------FUNKCJE TRYBU ADMINISTRATORA--------//
	//-------------------------------------------------------------//
	
	//Ustawiać na true w każdej funkcji
	//obsługującej jakiś onclick event w komórce na planie lekcji.
	var timetable_action_flag = false;
	
	//Potrzebne do zarządzania funkcjami administratora.
	var timetable_mode 		= "";
	var timetable_mode_id 	= "";
	
	//Tryb administracyjny:
	//Funkcja obsługująca narzędzia na istniejących elementach planu.
	function clickActionTimetableTool()
	{
		//Zapobiega wykryciu clicka w drugiej funkcji.
		timetable_action_flag = true;
		
		last_deleted_element = $(this).parent();
			
		$.ajax(
		{
			url: 			"admin.php",
			type: 		"post",
			data: 		{ function: "Usuń", page: "timetable", id: $(this).data("id") },
				
			success: 	function(response)
			{
				//last_deleted_element.fadeOut();
				ClearTable("plan");
				loadTimetableData(timetable_mode, timetable_mode_id);
			}
		});
	}
	
	//Tryb administracyjny:
	//Funkcja obsługująca kliknięcie w komórkę w tabeli - edycja.
	function clickActionTimetable()
	{	
		//Zapobiega pojawieniu się drugiego eventu
		//przy obsłudze innej akcji w komórce.
		if(timetable_action_flag)
		{
			timetable_action_flag = false;
		}
		
		else
		{
			var day_id 	= $(this).data("pos_x");
			var hour_id = $(this).data("pos_y");
			
			//Wykluczenie ramek tabeli
			if((day_id >= 1) && (hour_id >= 0))
			{			
				$('#edit_form').append('<input type="hidden" name="day_id" 	value="'+day_id+'" />');
				$('#edit_form').append('<input type="hidden" name="hour_id" 	value="'+hour_id+'" />');
				$('#edit_form').append('<input type="hidden" name="'+timetable_mode+'_id" value="'+timetable_mode_id+'" />');
				$("#edit_form").submit();			
			}
		}
	}
	
	//-------------------------------------------------------------//
	//-------POBIERANIE DANYCH DO PLANU LEKCJI----------//
	//-------------------------------------------------------------//
	
	//Funkcja ładująca lekcje do planu lekcji.
	//tryb_planu 	- (class, room, teacher)
	//id_trybu 		- np id klasy, id nauczyciela
	function loadTimetableData(tryb_planu, id_trybu)
	{
		$.ajax(
		{
			url: 		"timetable.php",
			type: 		"post",
			data: 		{ function : "getTimetableData", html_table_id : "plan", mode : tryb_planu, mode_id : id_trybu},
				
			success: 	function(response)
			{
				//aktualizacja zmiennych globalnych:
				timetable_mode 		= tryb_planu;
				timetable_mode_id 	= id_trybu;
				
				ClearTable("plan");
				
				//Wrzucenie skryptu do diva.
				$("#script").html(response);
				
				$(".timetable_info span").on("click", loadTimetableDataEvent);
				
				<?php
					//Tryb administracyjny:
					//Podpięcie funkcji obsługującej narzędzia na istniejących elementach planu.
					if($admin_mode)
						echo '$(".timetable button").click(clickActionTimetableTool);'
				?>
				
			}
		});
	}
	
	
	function loadTimetableCaption()
	{	
		$.ajax(
		{
			url: 		"timetable.php",
			type: 		"post",
			data: 		{ function : "getTimetableCaption", html_table_id : "plan"},
				
			success: 	function(response)
			{
				//Wrzucenie skryptu do diva.
				$("#script").html(response);
			}
		});
	}
</script>




<?php require_once "_commonHTML_head(meta+fonts+style).html"; ?>

<link rel="stylesheet" href="style/timetable.css" type="text/css"/>
<link rel="stylesheet" href="style/info_nav.css" type="text/css"/>
<script src="_commonJS_DynamicBG.js"></script>

<style>

ol li ol
{
	height: 200px;

    overflow-y: scroll;
}

ol li ol::-webkit-scrollbar-track
{
	-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
	background-color: #422;
}

ol li ol::-webkit-scrollbar
{
	width: 12px;
	background-color: #F5F5F5;
}

ol li ol::-webkit-scrollbar-thumb
{
	border-radius: 10px;
	-webkit-box-shadow: inset 0 0 3px rgba(0,0,0,.1);
	background-color: #876;
}


</style>



<body>
	<?php require "header.html"; ?>

	<?php
		db_getListOfEntriesByTypeID($connection, "", "nav" );
	?>
	
	<div class="separator"></div>
	
	<div class="container">	
	
		<div class="timetable_title">
			Wybierz z menu powyżej interesujący cię plan...
		</div>
		
		<div class="timetable">
			<!--TUTAJ BD DYNAMICZNIE WRZUCANA TABELKA JS-->
		</div>
		
		<div id="script">
			<!--TUTAJ BD DYNAMICZNIE WRZUCANY KOD JS-->
		</div>
		
		
		<form method="post" action="admin.php" id="edit_form" target="_blank">
			<input type="hidden" name="page" 	 value="timetable"/>
			<input type="hidden" name="function" value="Edytuj"/>
		</form>
		
		
		<script>		
			//-------------------------------------------------------------//
			//--------------TWORZENIE TABELI JAVASCRIPT---------------//
			//-------------------------------------------------------------//
			
			//Generuje kod HTML tabeli o określonym rozmiarze i
			//wrzuca go we wskazane miejsce (div id lub class).
			//where 	- do jakiego diva wrzucić
			//idtabeli 	- identyfikator tabeli
			//komurki bd miały id=idtabeli_x_y (1 do n)
			//pos_x/y_offset - ustawia względną numerację komórek w atrybutach data.
			
			function CreateTable(where, idtabeli, width, height, pos_x_offset = 0, pos_y_offset = 0)
			{
				var html = '<table id="' + idtabeli + '" data-width="'+width+'" data-height="'+height+'" >';
				
				for(var i=1; i<=height; i++)
				{
					html += "<tr>";
					for(var j =1; j<=width; j++)
					{
						//html += "<td></td>";
						html += '<td id="'+idtabeli+"_"+j+'_'+i+'" data-pos_x="'+(j+pos_x_offset)+'" data-pos_y="'+(i+pos_y_offset)+'" ></td>';
					}
					html += "</tr>";
				}
				
				$(where).html( html + "</table>" );
			}
			
			function ClearTable(idtabeli)
			{
				var width 	 = $("#"+idtabeli).data("width");
				var height	 = $("#"+idtabeli).data("height");
				
				for(var i=2; i<=height; i++)
				{
					for(var j =2; j<=width; j++)
					{
						$( ("#"+idtabeli+ "_" + j + "_" + i) ).html('<div class="empty"></div>');
					}
				}	
			}
			
			//Wpisuje coś do tabeli o danym id na wskazanej pozycji.
			
			function FillTable(idtabeli, pos_x, pos_y, content)
			{
				$( ("#"+idtabeli+ "_" + pos_x + "_" + pos_y) ).html(content);
			}
			
			
			//Tworzenie tabelki.
			//CreateTable(".timetable", "plan", 8, 11, -1, -2);
			
			//Wersja bez weekendu
			CreateTable(".timetable", "plan", 6, 11, -1, -2);

			//Pobranie danych typu godzina, dzień.
			//Domyślnie pusty plan.
			loadTimetableCaption();
			
			<?php
				//Tryb administracyjny:
				//Podpięcie funkcji obsługi kliknięcia komórkę tabelki.
				if($admin_mode)
					echo '$("#plan td").on("dblclick", clickActionTimetable);'
			?>
		</script>
		
	</div>
	
	<?php require "footer.html"; ?>
</body>
</html>


<script>
//Obsługa wysuwanego menu.

	//Kod obsługujący rozwijane menu.
	//Skład menu tu lista w liście o klasie głównej listy: '.nav'. 
		
	var opened_menu = $(0);
		
	$("ol li").click(function()
	{
		//Wybrano znowu to samo menu (nawet jeśli w klasie potomnej).
		if( opened_menu.get(0)===this)
		{
			$(this).children("ol").slideUp();
			$(this).css({"text-decoration":"none", "box-shadow":"none"});
			opened_menu=$(0);
			
			if($(window).width() < 768)
				$(".content, .admin_taskbar").slideDown();
			else
				$(".content, .admin_taskbar").fadeIn(400);
		}
		//Wybrano inne menu (i nie chodzi o nic z klasy potomnej).
		else if( $(this).parent().get(0)===$('ol').get(0) )
		{
			opened_menu.children("ol").slideUp();
			opened_menu.css({"text-decoration":"none", "box-shadow":"none"});			
			opened_menu=$(this);
			$(this).children("ol").slideDown();
			$(this).css({"text-decoration":"underline", "box-shadow":"inset 0px 0px 50px 0px rgba(0,0,0,0.3), 0px 5px 10px 2px rgba(0,0,0,0.4)"});
			
			if($(window).width() < 768)
				$(".content, .admin_taskbar").slideUp();
			else
				$(".content, .admin_taskbar").fadeOut(400);
		}
		//To wywołają nadmiarowe kliknięcia pochodzące z klasy pochodnej.
		else
		{
		}
	});
	
</script>

<script>
	//Przeładowywanie planu po wybraniu opcji w menu
	function loadTimetableDataEvent()
	{
		loadTimetableData($(this).data("timetable_type"), $(this).data("id"));

		switch($(this).data("timetable_type"))
		{
			case "room":
				$(".timetable_title").html('Plan dla sali: "'+$(this).data("value")+'"');
				break;
			case "teacher":
				$(".timetable_title").html('Plan dla nauczyciela: "'+$(this).data("value")+'"');
				break;
			case "class":
				$(".timetable_title").html('Plan dla klasy: "'+$(this).data("value")+'"');
				break;
		}
	}	
	
	$('ol li ol li').on("click", loadTimetableDataEvent);

</script>

<?php
	
	//-------------------------------------------------------------//
	//--------------POŁĄCZENIE Z BAZĄ DANYCH--------------//
	//-------------------------------------------------------------//
getTimetableCaption($connection, "plan");
	$connection->close();
?>