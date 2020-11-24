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
	
	//db_getColumnByID
	//Korzysta z bazy danych z którą połączenie jest zapisane w obiekcie $connection.
	//Z wiersza o określonym 'id' bierze wskazaną kolumnę we wskaanej tabeli.
	//Zwraca dane za pomocą echo.
	
	function db_getColumnByID(
			$connection, $table, 
			$column,
			$id)
	{
		//Zabezpieczenie przed wstrzykiwaniem SQL'a.
		$sql = sprintf("SELECT %s FROM %s WHERE id=%s",
			$connection->real_escape_string($column),
			$connection->real_escape_string($table),
			$connection->real_escape_string($id)
		);
			
		//To z jakiegoś powodu naprawia polskie znaki.
		$connection -> query("SET NAMES 'utf8'");
							
		if( $result = @$connection->query($sql) )
		{		
			while($row = $result->fetch_assoc())
				echo $row[$column];
			$result->free();
		}
		
		else
			echo SQL_ERROR;
	}		
	
	
	//db_getListOfEntriesByTypeID
	//Tworzy na podstawie dwóch tabel listę w której elementy jednej tabeli są katalogowane wg typów w drugiej.
	//Połączenie następuje dzięki kolumnie id którą muszą posiadać obie table. To do kategori o jakim id należy dany wpis
	//w tabeli entries jest zapisane w $entries_type_id_column. Nazwa typów i wpisów jest określona w odpowiednich wskazanych kolumnach.
	//Każdy znacznik w liście będzie miał dodadkowe atrybuty data-type_id i data-id. Dla elementów listy zewnętrznej data-id="0".
	//Lista bedzie miała określoną klasę. 
	
	function db_getListOfEntriesByTypeID(
			$connection, $types_table, $entries_table, 
			$types_name_column, $entries_name_column, $entries_type_id_column,
			$list_class)
	{
		//Pobranie informaji o typach wpisów.
		$sql = sprintf("SELECT %s, id FROM %s",
			$connection->real_escape_string($types_name_column),
			$connection->real_escape_string($types_table));
					
		//To z jakiegoś powodu naprawia polskie znaki.
		$connection -> query("SET NAMES 'utf8'");
			
		if( $types = @$connection->query($sql) )
		{	
			echo '<ol class="'.$list_class.'">';
				
			while($type = $types->fetch_assoc())
			{
				echo '<li data-id="0" data-type_id="'.$type["id"].'"><div class="ol_head">'.$type[$types_name_column]."</div><ol>";
						
				//Pobranie informaji o wpisach danego typu.		
				$sql = sprintf("SELECT %s, id FROM %s WHERE %s=%s ORDER BY name ASC",
					$connection->real_escape_string($entries_name_column),
					$connection->real_escape_string($entries_table),
					$connection->real_escape_string($entries_type_id_column),
					$connection->real_escape_string($type['id']));
		
				//To z jakiegoś powodu naprawia polskie znaki.
				$connection -> query("SET NAMES 'utf8'");
					
				if( $entries = @$connection->query($sql) )
				{		
					while($entry = $entries->fetch_assoc())
						echo '<li data-id="'.$entry["id"].'" data-type_id="'.$type["id"].'">'.$entry[$entries_name_column].'</li>';	
						
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
				case "getContent":
					db_getColumnByID($connection , "info", "content", $_POST['id']); break;
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

<link rel="stylesheet" href="style/info.css" type="text/css"/>
<link rel="stylesheet" href="style/info_nav.css" type="text/css"/>




<body>
	<?php require "header.html"; ?>
	
	<?php
		db_getListOfEntriesByTypeID($connection, "info_type", "info", "value", "name", "info_type_id", "nav" );
	?>
				
	<div class="separator"></div>
	
	<div class="container">		
		<div class="content">
			<div style="text-align: center;"><p><h3>Witaj w kąciku informacyjnym!</h3>Wybierz interesujące cię zagadnienia z menu i góry.</p></div>
		</div>
		
		<form method="post" action="admin.php" id="edit_form">
			<input type="hidden" name="page" 	 value="info"/>
			<input type="hidden" name="function" value="Edytuj"/>
		</form>
		
		<?php			
			//-------------------------------------------------------------//
			//----NARZĘDZIA DOSTĘPNE DLA ADMINISTRATORA-----//
			//-------------------------------------------------------------//
			if($admin_mode)
			{
				echo '<div class="admin_taskbar">';
				echo		'<button data-function="Edytuj"><i class="icon-pencil"></i></button>';
				echo		'<button data-function="Usuń"><i class="icon-trash"></i></button>';		
				echo '</div>';
			}
		?>
		
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
	
	

	//Kod startujący zewnętrzny plik php który pozyskuje dane z bazy danych.
	//Później podmienia zawartość diva content.
	
	//Domyślnie nic nie jest otwarte.
	var content_id = "";
	
	$("ol li ol li").click(function()
	{	
		if(content_id == "")
		{
			<?php if($admin_mode)
			echo 'onUntilFocusout($(".content"), "dblclick", HTMLToTextarea, TextareaToHTML);'; ?>
		}
	
		content_id = $(this).data("id");
	
		$.ajax(
		{
				url: 			"info.php",
				type: 		"post",
				data: 		{ function: "getContent", id:  content_id, table: "content"},
				
				success: 	function(response){$(".content").html(response); }
		});
	});
	
	
	//Funkcja obsługi guzików administratora.
	
	function adminButtonsAction()
	{
		if ( $(this).data("function")=="Edytuj" )
		{
			$('#edit_form').append('<input type="hidden" name="id" value="'+content_id+'" />');
			$("#edit_form").submit();
		}
		if ( $(this).data("function")=="Usuń" )
		{
			$.ajax(
			{
				url: 			"admin.php",
				type: 		"post",
				data: 		{ function: "Usuń", page: "info", id: content_id },
				
				success: 	function(response)
				{
					//Przeładowanie strony
					location.reload(); 
				}
			});
		}
	}
	
	
	//Obsługa edytora inline
	function HTMLToTextarea()
	{
		$(this).data("old_content", $(this).html());
		//$(this).html('<textarea style="font-family: inherit; border: none; padding: none; margin: none; outline: none; font-size: inherit; background: inherit; color: inherit; width: '+$(this).width()+'px; height: '+$(this).height()+'px; ">'+$(this).html()+'</textarea>');
		$(this).attr("contenteditable", true);
		//$(this).find('textarea').focus();
		$(this).focus();
	}
	function TextareaToHTML()
	{	
		if ( confirm("Zapisać zmiany?") )
		{
			var content = $(this).html();
			
			$.ajax(
			{
				url: 			"admin.php",
				type: 		"post",
				data: 		{ function: "quickedit", page: "info", id: content_id, content : content},
				
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
	}
	
	
	//Przypisanie akcji do guzików administratora.
	$('.admin_taskbar button').click(adminButtonsAction);
	
</script>