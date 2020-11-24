<?php	
	session_start();
	
	//-------------------------------------------------------------//
	//SPRAWDZENIE CZY UŻYTKOWNIK TO ADMINISTRATOR-//
	//-------------------------------------------------------------//
	
	if (isset($_SESSION['id']))
	{
		//ok
	}
	else
	{
		$_SESSION['not_logged_in'] = true;
		header('Location: login.php');
		exit();
	}
	
	//-------------------------------------------------------------//
	//--------------ZMIENNE I WARTOŚCI DOMYŚLNE----------//
	//-------------------------------------------------------------//
	
	//function  - Wykonywane fukcje: Zapisz, Usuń... itd
	//name 	- Pole edycyjne z tytułem newsa/informacji
	//content	- Treść newsa/informacji.
	//page		- Określa jaki tyb danych tworzy użytkownik (na jakiej tabeli pracuje).
	//				   Przyjmuje TAKIE SAME wartości jak nazwy tabel. To powala pisać ogólne zapytania sql.
	//id			- ID edytowanego elementu lub "" dla nowego elementu.
	//xxx_id	- Identyfikatory elementów w tabelach nadrzędnych info_type, class itd...
	
	
	//Domyślny panel na admin.php.
	if(!isset($_POST['page'])) 			$_POST['page']				="";
	//Pierwsze wejście na stronę.
	if(!isset($_POST['function'])) 		$_POST['function']			="";
	//Domyślnie tworzy się nowy element.
	if(!isset($_POST['id']))					$_POST['id']					="";
	//Domyślnie paski edycyjne są puste.
	if(!isset($_POST['name'])) 			$_POST['name']				="";
	if(!isset($_POST['content'])) 			$_POST['content']			="";
	//Domyślnie pola select są puste.
	if(!isset($_POST['info_type_id'])) 	$_POST['info_type_id']	="";
	if(!isset($_POST['lesson_id'])) 		$_POST['lesson_id']		="";
	if(!isset($_POST['teacher_id'])) 	$_POST['teacher_id']		="";
	if(!isset($_POST['class_id'])) 		$_POST['class_id']			="";
	if(!isset($_POST['room_id'])) 		$_POST['room_id']			="";
	if(!isset($_POST['hour_id'])) 		$_POST['hour_id']			="";
	if(!isset($_POST['day_id'])) 			$_POST['day_id']			="";
	//Kolumna z nazwą w tabelach typu: lesson, teacher...
	if(!isset($_POST['value'])) 			$_POST['value']				="";
	//Komunikaty.
	if(!isset($_POST['error'])) 				$_POST['error']				="";
	if(!isset($_POST['success'])) 		$_POST['success']			="";
	if(!isset($_POST['warning'])) 		$_POST['warning']			="";

	//-------------------------------------------------------------//
	//--------------PARAMETR TRYBU DEBUGOWANIA----------//
	//-------------------------------------------------------------//
	
	if(isset($_GET['debug']))
		define("_DEBUG", true);
	else
		define("_DEBUG", false);
	
	//id_deleted = do niczego nie używane. Tylko do celów debugingu.
	if(_DEBUG) $_POST['id_deleted']="";
	
	
	//-------------------------------------------------------------//
	//--------------------------FUNKCJE PHP--------------------//
	//-------------------------------------------------------------//
	
	//db_getSelectTag
	//Stworzy znacznik select na podstawie tabeli mającej kolumny: id i value.
	//Znacznik będzie nalerzeć do formlularza ($parent_form_id)
	//Jako widoczny opcje będzie wyświetlona wartość z kolumny value.
	//Wybrana opcja zostanie wysłana pod nazwą $name z wartością z kolumny id.
	//Znacznik będzie miał określoną klasę.
	//$selected_id pozwala określić id domyślnie wybranego elementu.
	//$order_by_id pozwala zmienić sortowanie. Domyślnie stortuje po value.
											
	function db_getSelectTag(
		$connection, $table,
		$name, $parent_form_id,
		$class,
		$selected_id,
		$placeholder,
		$order_by_id = false)
	{	
		$sql = sprintf("SELECT id, value FROM %s ORDER BY ".($order_by_id ? 'id' : 'value')." ASC",
			$connection->real_escape_string($table));				
											
		//To z jakiegoś powodu naprawia polskie znaki.
		$connection -> query("SET NAMES 'utf8'");
											
		if( $result = @$connection->query($sql) )
		{	
			echo '<select name="'.$name.'" form="'.$parent_form_id.'" class="'.$class.'" required>';
												
			echo '<option value="" disabled selected>'."".'</option>';		
								
			while($row = $result->fetch_assoc())
			{
				echo	'<option value="'.$row['id'].'" '.($selected_id==$row['id'] ? 'selected' : "").'>'.$row['value'].'</option>';
			}
			echo "</select>";
												
			$result->free();
		}
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
?>





<?php require_once "_commonHTML_head(meta+fonts+style).html"; ?>




<link rel="stylesheet" href="style/admin.css" type="text/css"/>
<link rel="stylesheet" href="style/news_post.css" type="text/css"/>
<link rel="stylesheet" href="style/info.css" type="text/css"/>




<script type="text/javascript">
	//-------------------------------------------------------------//
	//----------ZAZNACZENIE W POLU TEKSTOWYM-----------//
	//-------------------------------------------------------------//

	//SetSelection
	//Zaznacza w danym inpucie (input_id) tekst od start_pos do end_pos (który znak).
	function SetSelection(input_id, start_pos, end_pos)
	{
		var input = document.getElementById (input_id);
		
		if ('selectionStart' in input)
		{
			input.selectionStart = start_pos;
			input.selectionEnd = end_pos;
			input.focus ();
		}
		else
		{
			// Internet Explorer before version 9
			var inputRange = input.createTextRange ();
			
			inputRange.moveStart ("character", start_pos);
			inputRange.collapse ();
			inputRange.moveEnd ("character", end_pos);
			inputRange.select ();
		}
	}


	//SetSelectionSL
	//Zaznacza w danym inpucie (input_id) tekst od start_pos (który znak) o długości length.
	function SetSelectionSL(input_id, start_pos, length)
	{
		var input = document.getElementById (input_id);
		
		if ('selectionStart' in input)
		{
			input.selectionStart = start_pos;
			input.selectionEnd = start_pos + length;
			input.focus ();
		}
		else
		{
			// Internet Explorer before version 9
			var inputRange = input.createTextRange ();
			
			inputRange.moveStart ("character", start_pos);
			inputRange.collapse ();
			inputRange.moveEnd ("character", start_pos + start_pos);
			inputRange.select ();
		}
	}


	//GetSelection
	//Pobiera zaznaczony tekst w danym inpucie (input_id)
	function GetSelection(input_id)
	{
		var selection = "";
		var textarea = document.getElementById(input_id);
		
		if ('selectionStart' in textarea)
		{
			// check whether some text is selected in the textarea
			if (textarea.selectionStart != textarea.selectionEnd)
				selection = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);
		}
		else
		{
			// Internet Explorer before version 9 - create a range from the current selection
			var textRange = document.selection.createRange ();
			
			// check whether the selection is within the textarea
			var rangeParent = textRange.parentElement ();
			
			if (rangeParent === textarea)
				selection = textRange.text;
		}
		
		return selection;
	}


	//WrapTextSelection
	//Pozwala dodać coś przed (put_before) i po (put_after) zaznaczonego
	//w danym inpucie (input_id) tekstu.
	function WrapTextSelection(input_id, put_before = "", put_after = "")
	{
		if(GetSelection(input_id)=="")
			{alert("Musisz najpierw zaznaczyć jakiś tekst!"); return;}
	
		var textarea = document.getElementById(input_id);
		
		if ('selectionStart' in textarea)
		{
			// check whether some text is selected in the textarea
			if (textarea.selectionStart != textarea.selectionEnd)
			{
				var newText = 	textarea.value.substring(0, textarea.selectionStart) +
										put_before + textarea.value.substring(textarea.selectionStart, textarea.selectionEnd) + put_after +
										textarea.value.substring(textarea.selectionEnd);
				textarea.value = newText;
			}
		}
		else
		{
			// Internet Explorer before version 9 - create a range from the current selection
			var textRange = document.selection.createRange ();
			// check whether the selection is within the textarea
			var rangeParent = textRange.parentElement ();
			
			if (rangeParent === textarea)
				textRange.text = put_before + textRange.text + put_after;
		}
	}
	
	//-------------------------------------------------------------//
	//-------------------------OBSLUGA-------------------------//
	//--------------------COLOR PICKEROW---------------------//
	//-------------------------------------------------------------//
	
	//getColorFromColorPicker
	//Obsługuje slidery o klasach red_slider, green_slider i blue_slider.
	//Wyświetlacz wewnątrz klasy color kolor.
	//Aktualnie wybrany kolor zapisze w divie o klasie color_slider jako data-color
	function getColorFromColorPicker()
	{
		var color_picker = $(this).parent();
									
		var color = "#" + 	Number( color_picker.children(".red_slider").val() ).toString(16) +
						Number( color_picker.children(".green_slider").val() ).toString(16) +
						Number( color_picker.children(".blue_slider").val() ).toString(16);
		
		color_picker.data("color", color);
		color_picker.children(".color").css("background-color", color);
	}
	
	$(document).ready(function()
	{
		//Ustawienie koloru w color pickerach przy odświerzeniu strony	
		$(".color_picker input").each(getColorFromColorPicker);
		//Obsługa eventu zmiany pozycji slidera w color pickerach
		$(".color_picker input").on("input",getColorFromColorPicker);
	});
	
	
	//-------------------------------------------------------------//
	//-----------------TRYB PEŁNOEKRANOWY------------------//
	//-------------------------------------------------------------//
	
	function changeDisplayMode(mode)
	{
		switch(mode)
		{
			case 0:
				//TRYB NORMALNY
				$('.left').css({"display" : "block", "width": "55%", "border-right" : "4px dashed #576f81"});
				$('.right').css({"display" : "block", "width": "45%"});
				break;
			case 1:
				//TRYB EDYTORA
				$('.left').css({"display" : "block", "width": "100%", "border": "none"});
				$('.right').css({"display": "none"});
				break;
			case 2:	
				//TRYB PRZEGLĄDANIA BAZY DANYCH
				$('.left').css({"display": "none"});
				$('.right').css({"display" : "block", "width": "100%"});
				break;
		}
	}
	
	$(document).ready(function()
	{
		//Obsługa eventu zmiany pozycji slidera w color pickerach
		$(".top button").click(function(){changeDisplayMode($(this).data("mode"));});
	});

</script>





<style>
	.container
	{
		background: none;
		padding: 40px 15px;
		min-height: none;
		box-shadow: inset 0px 0px 50px 0px rgba(0,0,0,0.4);
	}
	.content
	{
		padding: none;
		
		border: none;
	}
</style>





<body>
		<div class="top" <?php if(!_DEBUG) echo 'style="display: none;"';?>>
			<button data-mode="1">Edytor</button>
			<button data-mode="2">Baza</button>
			<button data-mode="0">Oba</button>
		</div>
	
		<div class="right">
		
			<div class="navigation">			
				<form method="post" id="navigation"></form>

								
				<a href="profile.php"><button style="font-size: 300%; padding: 0; margin: 0;"><i class="icon-user"></i></button></a>
				<a href="index.php"><button style="font-size: 300%; padding: 0; margin: 0;"><i class="icon-home"></i></button></a>
				<a href="logout.php"><button style="font-size: 300%; padding: 0; margin: 0;"><i class="demo-icon icon-logout"></i></button></a>
				<div style="width: 100%;"><div class="separator"></div></div>
				
				<button type="submit" form="navigation" name="page" value="news">Aktualności</button>
				<button type="submit" form="navigation" name="page" value="info">Informacje</button>
				<button type="submit" form="navigation" name="page" value="timetable">Plan Lekcji</button>
				
				<div style="width: 100%;"><div class="separator"></div></div>
				
				<button type="submit" form="navigation" name="page" value="class">Klasy</button>
				<button type="submit" form="navigation" name="page" value="register_code">Kody Rejestracyjne</button>
				<button type="submit" form="navigation" name="page" value="teacher">Nauczyciele</button>
				<button type="submit" form="navigation" name="page" value="lesson">Przedmioty</button>
				<button type="submit" form="navigation" name="page" value="room">Sale</button>
				<button type="submit" form="navigation" name="page" value="info_type">Typy Informacji</button>
				
				<script>
					//Podkreślenie aktywnej opcji
					$('.navigation :button[value="<?php echo $_POST['page']; ?>"]').css("text-decoration", "underline");
					$('.navigation :button[value="<?php echo $_POST['page']; ?>"]').attr("form", "");
				</script>

			</div>
			
			<?php			
				//-------------------------------------------------------------//
				//--------------FUNKCJE WPŁWAJĄCE NA ELEMENTY------//
				//------------------------USUWANIE------------------------//
				//-------------------------------------------------------------//
			
				//USUNIĘTO NIEZAPISANY POST
				
				if($_POST['function']=="Usuń" && $_POST['id']=="")
				{
					$_POST['function']="Wyczyść";
				}
			
				//USUNIĘCIE WPISOW NA PLANIE LEKCJI POWIĄZANYCH Z DANYM ELEMENTEM
			
				if($_POST['function']=="Usuń" && ($_POST['page']=="class" || $_POST['page']=="room" || $_POST['page']=="teacher" || $_POST['page']=="lesson"))
				{
					$sql = sprintf("DELETE FROM timetable WHERE ".$_POST['page']."_id=%s",
						$_POST['id']);
							
					if( @$connection->query($sql) )
						$_POST['warning']="Usunięto wszystkie powiązane elementy.";
						
					//DEBUG//
					if(_DEBUG) echo 'if: function == Usuń<br/>$sql == '.$sql.'<br/><br/>';
				}
				
				if($_POST['function']=="Usuń" && $_POST['page']=="info_type")
				{
					$sql = sprintf("DELETE FROM info WHERE ".$_POST['page']."_id=%s",
						$_POST['id']);
							
					if( @$connection->query($sql) )
						$_POST['warning']="Usunięto też wszystkie powiązane elementy.";
						
					//DEBUG//
					if(_DEBUG) echo 'if: function == Usuń<br/>$sql == '.$sql.'<br/><br/>';
				}
			
				//-------------------------------------------------------------------------------------------------------------------
			
				if($_POST['function']=="Usuń")
				{					
					$sql = sprintf("DELETE FROM ".$_POST['page']." WHERE id=%s",
						$_POST['id']);
							
					if( @$connection->query($sql) )
						$_POST['success']='<i class="icon-trash"></i> Usunięto element.';
					
					//Info do debugowania.
					if(_DEBUG) $_POST['id_deleted'] = $_POST['id'];
					
					//By nie obrabiać usuniętego.
					$_POST['id'] = "";
						
					//DEBUG//
					if(_DEBUG) echo 'if: function == Usuń<br/>$sql == '.$sql.'<br/><br/>';
				}
				
				//-------------------------------------------------------------//
				//--------------FUNKCJE WPŁWAJĄCE NA ELEMENTY------//
				//--------------------ZAPISYWANIE------------------------//
				//-------------------------------------------------------------//
				
				//OBSŁUGA ALTERNATYWNEGO SPOSOBU ZAPISU
				
				if($_POST['function']=="Dodaj Nowy")
				{
					//Zawsze doda nowy nawet jeśli edytował.
					$_POST['id']="";
					$_POST['function']="Zapisz";
				}
				
				//-------------------------------------------------------------------------------------------------------------------
				
				//SPRAWDZENIE CZY WPISANO WSZYSTKIE DANE
				
				if($_POST['function']=="Zapisz")
				{
					if
					(	
						($_POST['page']=="info" && $_POST['info_type_id']=="") ||
						
						( $_POST['page']=="timetable" 	&& 
								( 	$_POST['class_id']=="" || $_POST['teacher_id']=="" || $_POST['day_id']=="" || 
									$_POST['hour_id']=="" || $_POST['room_id']=="" || $_POST['lesson_id']=="" ))
					)
					{
						$_POST['error'] 		= "By zapiać wybierz wszystkie pola.";
						$_POST['function']	= "Zapisz_failed"; //tylko w celu dubugowania
					}	
				}
				
				//-------------------------------------------------------------------------------------------------------------------

				//SPRAWDZENIE CZY SPEŁNIONE SĄ WARUNKI DLA PLANU LEKCJI
				
				if($_POST['function']=="Zapisz" && $_POST['page']=="timetable" && $_POST['id']=="")
				{
					$sql = sprintf("SELECT id FROM timetable WHERE class_id=%s and hour_id=%s and day_id=%s", $_POST['class_id'], $_POST['hour_id'], $_POST['day_id']);
						
					if( $result = @$connection->query($sql) ){								
						if($result->num_rows > 0){
							$_POST['error']="Ta klasa ma już w tym czasie inne zajęcia.";
							$_POST['function'] = "Zapisz_failed";}					
						$result->free();}			
					
					if(_DEBUG) echo 'if: function == Edytuj<br/>$sql == '.$sql.'<br/><br/>';
					
					$sql = sprintf("SELECT id FROM timetable WHERE teacher_id=%s and hour_id=%s and day_id=%s", $_POST['teacher_id'], $_POST['hour_id'], $_POST['day_id']);
						
					if( $result = @$connection->query($sql) ){								
						if($result->num_rows > 0){
							$_POST['error']="Ta nauczyciel prowadzi już w tym czasie inne zajęcia.";
							$_POST['function'] = "Zapisz_failed";}					
						$result->free();}			
					
					if(_DEBUG) echo 'if: function == Edytuj<br/>$sql == '.$sql.'<br/><br/>';
					
					$sql = sprintf("SELECT id FROM timetable WHERE room_id=%s and hour_id=%s and day_id=%s", $_POST['room_id'], $_POST['hour_id'], $_POST['day_id']);
						
					if( $result = @$connection->query($sql) ){								
						if($result->num_rows > 0){
							$_POST['error']="Ta sala jest już w tym czasie zajęta.";
							$_POST['function'] = "Zapisz_failed";}					
						$result->free();}			
					
					if(_DEBUG) echo 'if: function == Edytuj<br/>$sql == '.$sql.'<br/><br/>';
				}
				
				//-------------------------------------------------------------------------------------------------------------------
				
				if($_POST['function']=="Zapisz")
				{
					switch($_POST['page'])
					{
						case "news":
						{
							if($_POST['id']=="")	 //Nowy element.
								$sql = sprintf("INSERT INTO news (date, name, content) VALUES ('%s-%s-%s', '%s', '%s')",
									getdate()['year'],
									getdate()['mon'],
									getdate()['mday'],
									$connection->real_escape_string($_POST['name']),
									$connection->real_escape_string($_POST['content']));
							else //Edycja
								$sql = sprintf("UPDATE news SET name='%s', content='%s' WHERE id=%s",
									$connection->real_escape_string($_POST['name']),
									$connection->real_escape_string($_POST['content']),
									$_POST['id']);
							break;
						}
						case "info":
						{
							if($_POST['id']=="")	 //Nowy element					
								$sql = sprintf("INSERT INTO info (name, content, info_type_id) VALUES ('%s', '%s', '%s')",
									$connection->real_escape_string($_POST['name']),
									$connection->real_escape_string($_POST['content']),
									$_POST['info_type_id']);
							else	//Edycja
								$sql = sprintf("UPDATE info SET name='%s', content='%s', info_type_id='%s' WHERE id=%s",
									$connection->real_escape_string($_POST['name']),
									$connection->real_escape_string($_POST['content']),
									$_POST['info_type_id'],
									$_POST['id']);
							break;
						}
						case "timetable":
						{
							if($_POST['id']=="")	 //Nowy element					
								$sql = sprintf("INSERT INTO timetable (lesson_id, teacher_id, class_id, room_id, hour_id, day_id) VALUES ('%s', '%s', '%s', '%s', '%s', '%s')",
									$_POST['lesson_id'],
									$_POST['teacher_id'],
									$_POST['class_id'],
									$_POST['room_id'],
									$_POST['hour_id'],
									$_POST['day_id']);
							else	//Edycja
								$sql = sprintf("UPDATE timetable SET lesson_id='%s', teacher_id='%s', class_id='%s', room_id='%s', hour_id='%s', day_id='%s' WHERE id=%s",
									$_POST['lesson_id'],
									$_POST['teacher_id'],
									$_POST['class_id'],
									$_POST['room_id'],
									$_POST['hour_id'],
									$_POST['day_id'],
									$_POST['id']);
							break;
						}
						case "lesson":
						case "teacher":
						case "info_type":
						{
							if($_POST['id']=="")	 //Nowy element.
								$sql = sprintf("INSERT INTO %s (value) VALUES ('%s')",
									$connection->real_escape_string($_POST['page']),
									$connection->real_escape_string($_POST['value']));
							else //Edycja
								$sql = sprintf("UPDATE %s SET value='%s' WHERE id=%s",
									$connection->real_escape_string($_POST['page']),
									$connection->real_escape_string($_POST['value']),
									$_POST['id']);
							break;
						}
						case "class": //Dla tych zapisuje dużymi literami.
						case "room":
						{
							if($_POST['id']=="")	 //Nowy element.
								$sql = sprintf("INSERT INTO %s (value) VALUES (UPPER('%s'))",
									$connection->real_escape_string($_POST['page']),
									$connection->real_escape_string($_POST['value']));
							else //Edycja
								$sql = sprintf("UPDATE %s SET value=UPPER('%s') WHERE id=%s",
									$connection->real_escape_string($_POST['page']),
									$connection->real_escape_string($_POST['value']),
									$_POST['id']);
							break;
						}
						case "register_code":
						{	//Nowy element.
							$sql = sprintf("INSERT INTO %s (value) VALUES (UPPER('%s'))",
								$connection->real_escape_string($_POST['page']),
								bin2hex(openssl_random_pseudo_bytes(4)));
							break;
						}
					}
					
					//To z jakiegoś powodu naprawia polskie znaki.
					$connection -> query("SET NAMES 'utf8'");
			
					if( @$connection->query($sql) )
					{
						$_POST['success']='<i class="icon-floppy"></i> Zapisano element.';
						
						//Umożliwia dalszą pracę na zapisanym elemencie.
						if($_POST['id']=="") $_POST['id'] = $connection->insert_id;
					}
						
					//DEBUG//
					if(_DEBUG) echo 'if: function == Zapisz<br/>$sql == '.$sql.'<br/><br/>';
				}
				
				if($_POST['function']=="quickedit" && $_POST['page']=="news")
				{
					if($_POST['content']!="")	 //Nowy element.
						$sql = sprintf("UPDATE news SET content='%s' WHERE id=%s",
							$connection->real_escape_string($_POST['content']),
							$_POST['id']);
					else if ($_POST['name']!="") //Edycja
						$sql = sprintf("UPDATE news SET name='%s' WHERE id=%s",
							$connection->real_escape_string($_POST['name']),
							$_POST['id']);
					
					//To z jakiegoś powodu naprawia polskie znaki.
					$connection -> query("SET NAMES 'utf8'");
			
					if( @$connection->query($sql) )
					{
						$_POST['success']='<i class="icon-floppy"></i> Zapisano element.';
						
						//Umożliwia dalszą pracę na zapisanym elemencie.
						if($_POST['id']=="") $_POST['id'] = $connection->insert_id;
					}
						
					//DEBUG//
					if(_DEBUG) echo 'if: function == quickedit<br/>$sql == '.$sql.'<br/><br/>';
				}
				
				if($_POST['function']=="quickedit" && $_POST['page']=="info")
				{
					$sql = sprintf("UPDATE info SET content='%s' WHERE id=%s",
						$connection->real_escape_string($_POST['content']),
						$_POST['id']);
					
					//To z jakiegoś powodu naprawia polskie znaki.
					$connection -> query("SET NAMES 'utf8'");
			
					if( @$connection->query($sql) )
					{
						$_POST['success']='<i class="icon-floppy"></i> Zapisano element.';
						
						//Umożliwia dalszą pracę na zapisanym elemencie.
						if($_POST['id']=="") $_POST['id'] = $connection->insert_id;
					}
						
					//DEBUG//
					if(_DEBUG) echo 'if: function == quickedit<br/>$sql == '.$sql.'<br/><br/>';
				}
				
				//-------------------------------------------------------------//
				//---------POBRANIE INFORMACJI DO EDYTORA-----------//
				//------------DLA EDYTOWANYCH ELEMENTOW------------//
				//-------------------------------------------------------------//
				
				if($_POST['function']=="Edytuj")
				{					
					//Edycja po wysłaniu id.
					if($_POST['id']!="")
					{
						$sql = "SELECT * FROM ".$_POST['page']." WHERE id=".$_POST['id'];
					}
					
					//Edycja po wysłaniu współrzędnych na planie (i jednym z parametrów jeśli są)
					if($_POST['day_id']!="" && $_POST['day_id']!="" && $_POST['day_id']!="")
					{
						$sql = "SELECT * FROM timetable WHERE hour_id=".$_POST['hour_id']." and day_id=".$_POST['day_id'];
							
						if ($_POST['teacher_id']!="") $sql = $sql." and teacher_id=".$_POST['teacher_id'];
						else if ($_POST['class_id']!="") $sql = $sql." and class_id=".$_POST['class_id'];
						else if ($_POST['room_id']!="") $sql = $sql." and room_id=".$_POST['room_id'];
					}
					
					//To z jakiegoś powodu naprawia polskie znaki.
					$connection -> query("SET NAMES 'utf8'");
							
					if( $result = @$connection->query($sql) )
					{								
						if($row = $result->fetch_assoc())
							//Przepisuje dane z row do POST
							foreach ($row as $index => $value)
								$_POST[$index] = $value;
													
						$result->free();
					}	
					
					//DEBUG//
					if(_DEBUG) echo 'if: function == Edytuj<br/>$sql == '.$sql.'<br/><br/>';
				}

				//-------------------------------------------------------------//
				//------RACZEJ NIC NIE TRZEBA WIĘCEJ ROBIĆ----------//
				//-------------------------------------------------------------//
				
				if($_POST['function']=="Wyczyść")
				{
					//Ignoruje dane z formularza.
				}
				
				if($_POST['function']=="Stwórz nowy element")
				{
					//Wyzerowanie strony.
				}
				
				if($_POST['function']=="Podgląd")
				{
					//Dane domyślnie przekazywane więc nic nie trzeba tu robić.
				}
			?>
			
			<form method="post" id="delete">
				<input type="hidden" name="page" value="<?php echo $_POST['page']; ?>"/>
				<input type="hidden" name="function" value="Usuń"/>
			</form>
			<form method="post" id="edit">
				<input type="hidden" name="page" value="<?php echo $_POST['page']; ?>"/>
				<input type="hidden" name="function" value="Edytuj"/>		
			</form>
			
			<?php
				//-------------------------------------------------------------//
				//----ZAŁADOWANIE BAZY ISTNIEJĄCYCH ELEMNTOW----//
				//-------------------------------------------------------------//
			
				if($_POST['page']=="")
				{
					echo '<div class="database_placeholder"><i class="demo-icon icon-database"></i></div>';
					echo '<div class="database_placeholder">Baza Danych</div>';
				}
				else
				{
					//Wrzystkie upycha w jeden 2 elementowy format + id.
					switch($_POST['page'])
					{
						case "news":
							$sql = "SELECT date as info, name, id FROM news ORDER BY date DESC"; break;
						case "info":
							$sql ='SELECT info_type.value as info, info.name, info.id FROM info, info_type WHERE info.info_type_id=info_type.id ORDER BY info_type.value ASC, info.name'; break;
						case "timetable":
							$sql =	'SELECT tt.id as id, CONCAT(d.value, " : ", h.value) as info, CONCAT("Klasa: ", UPPER(c.value),", ",l.value," (",t.value,", s ",r.value,")") as name
										FROM timetable tt, class c, day d, hour h, lesson l, teacher t, room r
										WHERE tt.class_id=c.id and tt.day_id=d.id and tt.hour_id=h.id and tt.lesson_id=l.id and tt.teacher_id=t.id and tt.room_id=r.id
										ORDER BY c.value ASC, d.id, h.id'; break;
						case "lesson":
						case "teacher":
						case "class":
						case "room":
						case "info_type":
							$sql = sprintf('SELECT "Nazwa" as info, value as name, id FROM %s ORDER BY value ASC',
										$connection->real_escape_string($_POST['page'])); break;
						case "register_code":
							$sql = sprintf('SELECT CONCAT("Kod rejestracyjny #",id) as info, value as name, id FROM %s ORDER BY id ASC',
										$connection->real_escape_string($_POST['page'])); break;
					}
					
					//DEBUG//
					if(_DEBUG) echo 'Pobieranie elementów<br/>$sql == '.$sql.'<br/><br/>';	
						
					//To z jakiegoś powodu naprawia polskie znaki.
					$connection -> query("SET NAMES 'utf8'");
						
					if( $result = @$connection->query($sql) )
					{	
						echo '<ol class="database">';
							
						while($row = $result->fetch_assoc())
						{
							echo	'<li>';
							echo		'<div class="info">'.$row['info'].'</div>';
							echo		'<div class="name">'.$row['name'].'</div>';
							echo		'<div class="modify">';
							
							if($_POST['page']!="register_code")
							echo 		'<button type="submit" form="edit" 		name="id" value="'.$row['id'].'">Edytuj</button>';
						
							echo 		'<button type="submit" form="delete" 	name="id" value="'.$row['id'].'">Usuń</button>';
							echo		'</div>';
							echo	'</li>';
						}
							
						echo "</ol>";
							
						$result->free();
					}
				}
			?>
			
		</div>
		
		<div class="left">
		
			<?php
				//-------------------------------------------------------------//
				//-----DEBUG: WYPISANIE ZMIENNYCH W $_POS---------//
				//-------------------------------------------------------------//

				if(_DEBUG)
				{
					echo '<h1>ZMIENNE W $_POST</h1><br/><table style="direction:ltr; width: 100%; padding: 0px;">';
					foreach ($_POST as $key => $value)
					{
						echo '<tr style="padding: 0px; margin: 0px;">';
						echo '<td style="color: #999; padding: 0px; margin: 0px;">';
						echo htmlentities($key, ENT_QUOTES, "UTF-8");
						echo "</td>";
						echo "<td>";
						echo  htmlentities($value, ENT_QUOTES, "UTF-8");
						echo "</td>";
						echo "</tr>";
					}
					echo '</table>';
				}
			?>
		
			<form class="new_element" method="post" <?php if($_POST['page']=="") echo 'style="visibility:hidden;"'?>>
				<button type="submit" name="function" value="Stwórz nowy element">Stwórz nowy element</button>
				<input type="hidden" name="page" value="<?php echo $_POST['page']; ?>"/>
			</form>
			
			<?php
				if($_POST['function']=="")
				{
					echo '<div class="header" style="padding: 80px;"><u>Witaj w panelu administracyjnym!</u><br>
					<i class="icon-wrench" style="font-size: 120px; margin: 20px;"></i><br>
					<span style="font-size: 80%;">Wybierz interesujący cię typ danych w polu nawigacyjnym w prawym górnym rogu
					a następnie stwórz nowy element lub wybierz jeden z istniejących na liście.</span></div>';
				
				}
			?>
			
			<div class="editor" <?php if($_POST['function']=="") echo 'style="display: none;"'?>>
			
			<div class="header">
				<?php
					switch($_POST['page'])
					{
						case "news":			{echo 'Edytor newsów <i class="icon-newspaper"></i>'; 								break;}
						case "info":			{echo 'Edytor informacji <i class="icon-info-circled-1"></i>';							break;}
						case "timetable":	{echo 'Edytor planu lekcji <i class="icon-menu"></i>';									break;}
						case "lesson":		{echo '<i class="icon-math"></i> Edytor lekcji <i class="icon-music"></i>'; 	break;}
						case "teacher":		{echo 'Edytor nauczycieli <i class="icon-user"></i>'; 									break;}
						case "class":			{echo 'Edytor klas <i class="icon-group"></i>'; 											break;}
						case "room":			{echo 'Edytor sal <i class="icon-building"></i>'; 											break;}
						case "info_type":	{echo 'Edytor typów informacji <i class="icon-info"></i>';								break; }
						case "register_code":	{echo 'Generator kodów rejestracyjnych <i class="icon-user"></i>';								break;}
					}
					
					echo '<div style="font-size: 50%;">';
					switch($_POST['page'])
					{
						case "news":			{echo 'Dodane wpisy pojawią się na stronie z <a href="news.php" target="_blank">aktualnościami</a>.'; 		break;}
						case "info":			{echo 'Dodane wpisy pojawią się na stronie z <a href="info.php" target="_blank">informacjami</a>.';			break;}
						case "timetable":	{echo 'Wprowadzone zajęcia pojawią się na stronie z <a href="timetable.php" target="_blank">planem lekcji</a>.';break;}
						case "lesson":		{echo 'Wprowadzone zajęcia będą dostępne w edytorze planu lekcji.';
														echo  '<div>Usunięcie lekcji spowoduje usunięcie wszystkich wpisów na planie lekcji w których się pojawiała.</div>'; 		break;}
						case "teacher":		{echo 'Wprowadzeni nauczyciele będą dostępni w edytorze planu lekcji.';
														echo  '<div>Usunięcie nauczyciela spowoduje usunięcie wszystkich wpisów na planie lekcji w których się pojawiał.</div>'; 	break;}
						case "class":			{echo 'Wprowadzone klasy będą dostępne w edytorze planu lekcji.';
														echo  '<div>Usunięcie klasy spowoduje usunięcie wszystkich wpisów na planie lekcji w których się pojawiała.</div>'; 		break;}
						case "room":			{echo 'Wprowadzone sale będą dostępne w edytorze planu lekcji.';
														echo  '<div>Usunięcie sali spowoduje usunięcie wszystkich wpisów na planie lekcji w których się pojawiała.</div>'; 			break;}
						case "info_type":	{echo 'Wprowadzone typy informacji będą dostępne w edytorze informacji.';
														echo  '<div>Usunięcie typu informacji spowoduje usunięcie wszystkich informacji danego typu.</div>'; 							break;}
						case "register_code":	{echo 'By zarejestrować się na stronie wymagany jest jeden z generowanych tu kodów.';
													echo  '<div>Każdy kod pozwala zarejestrować się tylko jednemu użytkownikowi.</div>'; break;}
					}
					echo '</div>';
				?>
			</div>
			

			
			
			<div class="messagebar">
				<?php
					if($_POST['error']!="") 		echo '<div class="error"><i class="icon-cancel"></i> '.$_POST['error'].'</div>';
					if($_POST['success']!="")	echo '<div class="success">'.$_POST['success'].'</div>';
					if($_POST['warning']!="") 	echo '<div class="success"><i class=" icon-info"></i> '.$_POST['warning'].'</div>';
					if($_POST['id'] != "")	 		echo '<div class="information"><i class="icon-pencil"></i> Edytujesz istniejący element.</div>';	
				?>
			</div>
			
			
			<div class="toolbar" <?php if(!($_POST['page']=="news" || $_POST['page']=="info")) echo 'style="display: none;"' ?>>
				<button onclick='WrapTextSelection("text_input","<b>","</b>")'><b>b</b></button>
				<button onclick='WrapTextSelection("text_input","<i>","</i>")'><i>i</i></button>
				<button onclick='WrapTextSelection("text_input","<u>","</u>")'><u>u</u></button>
				
				<?php
					function tagStyleSpan($tag, $css, $name)
						{echo '<'.$tag.' onclick=\'WrapTextSelection("text_input","<span style=\"'.$css.'\">","</span>")\' style="'.$css.'">'.$name.'</'.$tag.'>'; echo "\n";}
						
					tagStyleSpan("button", "text-decoration: overline;",			"up");
					tagStyleSpan("button", "text-decoration: line-through;",	"abc");
				?>

				<div class="separator"></div>
				
				<button onclick='WrapTextSelection("text_input","","<br>")'>br</button>
				
				<div class="separator"></div>
				
				<button onclick='WrapTextSelection("text_input","<img src=\"","\"/>")'>img</button>
				<button onclick='WrapTextSelection("text_input","<a href=\"","\" target=\"_blank\"/>WPISZ NAZWĘ  LINKU TUTAJ</a>")'><u><i>url</i></u></button>
				
				<div class="separator"></div>
				
				<button onclick='WrapTextSelection("text_input","<p>","</p>")'>p</button>
				<button onclick='WrapTextSelection("text_input","<center>","</center>")'>center</button>
				
				<br/>
				
				<div class="tool">
					<button>Nagłówki</button>
					<ol>
						<li onclick='WrapTextSelection("text_input","<h1>","</h1>")'>h1</li>
						<li onclick='WrapTextSelection("text_input","<h2>","</h2>")'>h2</li>
						<li onclick='WrapTextSelection("text_input","<h3>","</h3>")'>h3</li>
						<div class="separator"></div>
						<li onclick='WrapTextSelection("text_input","<h4>","</h4>")'>h4</li>
						<li onclick='WrapTextSelection("text_input","<h5>","</h6>")'>h5</li>
						<li onclick='WrapTextSelection("text_input","<h6>","</h6>")'>h6</li>
						<div class="separator"></div>
						<li onclick='WrapTextSelection("text_input","<h7>","</h7>")'>h7</li>
						<li onclick='WrapTextSelection("text_input","<h8>","</h8>")'>h8</li>
						<li onclick='WrapTextSelection("text_input","<h9>","</h9>")'>h9</li>
					</ol>
				</div>
				
				<div class="tool">
					<button>Inne fonty</button>
					<ol>
						<?php
							function liStyleSpan($css, $name)
								{echo '<li onclick=\'WrapTextSelection("text_input","<span style=\"'.$css.'\">","</span>")\' style="'.$css.'">'.$name.'</li>'; echo "\n";}
							
							liStyleSpan("font-family: Georgia, serif;",						"Georgia");
							
							echo '<span style="font-size: 12px;">';
							liStyleSpan("font-family: Libre Baskerville, serif;",			"Libre Baskerville"); echo '</span>';
							
							echo '<div class="separator"></div>';
							
							liStyleSpan("font-family: Arial, Helvetica, sans-serif;",		"Arial");
							liStyleSpan("font-family: Cuprum, sans-serif;",						"Cuprum");
							liStyleSpan("font-family: Tahoma, Geneva, sans-serif;",	"Tahoma");
							liStyleSpan("font-family: Verdana, Geneva, sans-serif;",	"Verdana");
							
							echo '<div class="separator"></div>';
							
							liStyleSpan("font-family: Impact, Charcoal, sans-serif;",	"Impact");
							
							echo '<div class="separator"></div>';
							
							liStyleSpan("font-family: Lucida Console,  monospace;",	"Lucida Console");
						?>
					</ol>
				</div>
				
				<div class="tool">
					<button>Rozmiar fonta</button>
					<ol class="scroll">
						<?php
							function liFontSize($px)
								{echo '<li onclick=\'WrapTextSelection("text_input","<span style=\"font-size: '.$px.'px;\">","</span>")\' style="font-size: '.$px.'px;">'.$px.'</li>'; echo"\n"; }
							
							liFontSize(5); liFontSize(6); liFontSize(8); liFontSize(9); liFontSize(10); liFontSize(11); liFontSize(12); liFontSize(14); liFontSize(16); liFontSize(18);
							liFontSize(20); liFontSize(22); liFontSize(24); liFontSize(26); liFontSize(28); liFontSize(36); liFontSize(48); liFontSize(72);
						?>
					</ol>
				</div>
				
				<div class="tool">
					<button>Rozstrzał</button>
					<ol>
						<?php
							function liLetterSpacing($px)
								{echo '<li onclick=\'WrapTextSelection("text_input","<span style=\"letter-spacing: '.$px.'px;\">","</span>")\' style="letter-spacing: '.$px.'px;">'.$px.'px</li>'; }
							
							for($i=1;$i<=10;$i++) liLetterSpacing($i);
						?>
					</ol>
				</div>
				
				<div class="tool">
					<button>Kolor fonta</button>
					<ol>
						<?php
							//Buduje color picker RGB o podanym id.
							function ColorPicker($color_picker_id)
							{
								echo		'<div class="color_picker" id="'.$color_picker_id.'">';
								echo			'<input type="range" class="red_slider" 		min="0" max="255" value="128">';
								echo			'<input type="range" class="green_slider" 	min="0" max="255" value="128">';
								echo			'<input type="range" class="blue_slider" 		min="0" max="255" value="128">	';
								echo			'<div class="color" id="color"></div>';
								echo		'</div>';
							}
							
							ColorPicker("font_color_picker");
						?>

						<script>
							function setSelectedText_color(input_id, color_picker_id){
								WrapTextSelection(input_id, '<span style="color: '+$("#"+color_picker_id).data("color")+';">','</span>');}								
						</script>
							
						<li onclick='setSelectedText_color("text_input", "font_color_picker")'>Zastosuj</li>
					</ol>
				</div>
				
				<div class="tool">
					<button>Zakreślenie</button>
					<ol>
						<?php ColorPicker("bg_color_picker"); ?>
						
						<script>
							function setSelectedText_bgcolor(input_id, color_picker_id){
								WrapTextSelection(input_id, '<span style="background-color: '+$(color_picker_id).data("color")+';">','</span>');}								
						</script>
						
						<li onclick='setSelectedText_bgcolor("text_input", "#bg_color_picker")'>Zastosuj</li>
					</ol>
				</div>
				
				
				<div class="tool">
					<button>Strukturalne</button>
					<ol>
						<?php
							function liStyleDiv($css, $name)
								{echo '<li onclick=\'WrapTextSelection("text_input","<div style=\"'.$css.'\">","</div>")\' >'.$name.'</li>'; echo "\n";}
						
							liStyleDiv("text-align: left;",		"left");
							liStyleDiv("text-align: right;",		"right");
							liStyleDiv("text-align: center;",	"center");
							liStyleDiv("text-align: justify;",	"justify");
						?>
					</ol>
				</div>

			</div>
			
			
			<form method="post" id="send_id_only">
				<input type="hidden" name="id" 	value="<?php echo $_POST['id']; ?>" />
				<input type="hidden" name="page" value="<?php echo $_POST['page']; ?>"/>
			</form>
			
			<form method="post" id="data_input" class="data_input">
				<input type="hidden" name="id" 	value="<?php echo $_POST['id']; ?>" />
				<input type="hidden" name="page" value="<?php echo $_POST['page']; ?>"/>
				<?php
					//-------------------------------------------------------------//
					//----------------BUDOWANIE FORMULARZA----------------//
					//----------------POLA TEKSTOWE I SELECT----------------//
					//-------------------------------------------------------------//
					
					switch($_POST['page'])
					{
						case "info":
						{
							db_getSelectTag($connection, "info_type", "info_type_id", 	"data_input", "info_page", $_POST['info_type_id'], "Kategoria");
						}	
						case "info":
						case "news":
						{
							echo '<textarea class="title_input" name="name" placeholder="Wprowadź tytuł newsa tutaj..." required>'.$_POST['name'].'</textarea>';
							echo '<textarea id="text_input" class="text_input" name="content" placeholder="Wprowadź treść newsa i HTML tutaj..." required>'.$_POST['content'].'</textarea>';
							break;
						}
						case "timetable":
						{
							echo '<div class="timetable_input_info">Czas odbywania zajęć:</div>';
							db_getSelectTag($connection, "day", 		"day_id",		"data_input", "timetable_page", $_POST['day_id'], 		"Dzień",	true);
							db_getSelectTag($connection, "hour",		"hour_id", 	"data_input", "timetable_page", $_POST['hour_id'], 		"Godzina",	true);
							echo '<div class="timetable_input_info">Przedmiot i klasa:</div>';
							db_getSelectTag($connection, "lesson", 	"lesson_id", 	"data_input", "timetable_page", $_POST['lesson_id'],		"Przedmiot");
							db_getSelectTag($connection, "class",		"class_id", 	"data_input", "timetable_page", $_POST['class_id'],		"Klasa");
							echo '<div class="timetable_input_info">Prowadzący i sala:</div>';
							db_getSelectTag($connection, "teacher", "teacher_id", "data_input", "timetable_page", $_POST['teacher_id'],	"Nauczyciel");
							db_getSelectTag($connection, "room", 	"room_id", 	"data_input", "timetable_page", $_POST['room_id'],		"Sala");
							echo '<br/><br/>';
							break;
						}
						case "class":
						case "lesson":
						case "teacher":
						case "room":
						case "info_type":
						{
							echo '<textarea class="title_input_single" name="value" placeholder="Wprowadź nazwę..." required>'.$_POST['value'].'</textarea>';
							break;
						}	
					}
				?>
			</form>
					
				
			<div class="taskbar">			
				<?php
					if($_POST['page']=="register_code")
						echo '<button type="submit" style="width: 70%;" form="data_input" name="function" value="Dodaj Nowy">Wygeneruj nowy kod rejestracyjny <i class="icon-doc-new"></i></button>';
					else
					{
						echo '<button type="submit" form="data_input" name="function" value="Zapisz">Zapisz <i class="icon-floppy"></i></button>';
						echo '<button type="submit" form="data_input" name="function" value="Dodaj Nowy">Zapisz Kopię <i class="icon-doc-new"></i></button>';
						switch ($_POST['page']){case "news": case "info":
								echo '<button type="submit" form="data_input" name="function" value="Podgląd">Podgląd <i class="icon-link-ext"></i></button>';}
						echo '<button type="submit" form="send_id_only" name="function" value="Wyczyść">Wyczyść <i class="icon-cancel"></i></button>';
						echo '<button type="submit" form="send_id_only" name="function" value="Usuń">Usuń <i class="icon-trash"></i></button>';
					}
				?>
			</div>
			
			
			<script>	
				//-------------------------------------------------------------//
				//-------------------------OBSLUGA-------------------------//
				//-------------------CTRL + S---CTRL + P------------------//
				//-------------------------------------------------------------//
				
				$(window).bind('keydown', function(event)
				{
					if (event.ctrlKey || event.metaKey)
					{
						switch (String.fromCharCode(event.which).toLowerCase())
						{
							case 's':
								event.preventDefault();
								$('#data_input').append('<input type="hidden" name="function" value="Zapisz" />');
								$("#data_input").submit();
								break;
							case 'p':
								event.preventDefault();
								$('#data_input').append('<input type="hidden" name="function" value="Podgląd" />');
								$("#data_input").submit();
								break;
							case 'k':
								event.preventDefault();
								$('#data_input').append('<input type="hidden" name="function" value="Dodaj Nowy" />');
								$("#data_input").submit();
								break;
						}
					}
				});
			</script>
			
			
			<div class="preview">
					<?php
						if($_POST['page']=="news" || $_POST['page']=="info")
						if($_POST['function']=="Podgląd" || $_POST['function']=="Edytuj" || $_POST['function']=="Zapisz")
						{						
							echo '<div class="header">Podgląd:</div>';
							
							switch($_POST['page'])
							{
								case "news":
								{
									echo '<div class="posts">';
									echo 	'<div class="post">';
									echo 		'<div class="header">'.$_POST['name'].'</div>';
									echo 			'<div class="main">'.$_POST['content'].'</div>';
									echo 		'<div class="footer">0000/00/00</div>';
									echo	 '</div>';
									echo '</div>';
							
									break;
								}
								case "info":
								{
									echo '<div class="posts">';
									echo 	'<div class="container">';
									echo			'<div class="content">';
									echo				$_POST['content'];
									echo 		'</div>';			
									echo 	'</div>';
									echo '</div>';	
							
									break;
								}
								case "timetable":
								{
									echo "tu będzie plan";
									//TODO
									
									break;
								}
							}
						}
				?>
			</div>
		
		</div>
		</div>
</body>	
</html>



<?php
	//-------------------------------------------------------------//
	//--------------POŁĄCZENIE Z BAZĄ DANYCH--------------//
	//-------------------------------------------------------------//

	$connection->close();
?>

<!-- TROLOLOLO CHCIAŁO CI SIĘ TAK NISKO JECHAĆ?-->