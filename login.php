<?php
	session_start();
	require_once "php/parameters.php";
	
	//Gdy już zalogowano.
	if (isset($_SESSION['id']))
	{
		header('Location: index.php');
		exit();
	}
	
	//-------------------------------------------------------------//
	//----------OBSŁUGA FORMULARZA LOGOWANIA----------//
	//-------------------------------------------------------------//
		
	if (isset($_POST['login']) && isset($_POST['pass']) )
	{
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
		//----------SPRAWDZENIE DANYCH LOGOWANIA----------//
		//--------------Z HASHOWANIEM W BAZIE-----------------//
		//-------------------------------------------------------------//
		
		else
		{
			//Zastąpienie niebezpiecnzych znaków encjami (<>""...):
			$_POST['login'] = htmlentities($_POST['login'], ENT_QUOTES, "UTF-8");
					
			//mysqli_real_escape_string - dodatkowy skryt przeciw wstrzykiwaniu SQL'a			
			$sql = sprintf("SELECT * FROM user WHERE login='%s'",
						mysqli_real_escape_string($connection, $_POST['login']));
				
			if( $result = @$connection->query($sql) )
			{		
				//Znaleziono kogoś o podanym loginie.
				if($row = $result->fetch_assoc())
				{
					//Sprawdzanie hasła z hashowaniem.
					//password_verify hashuje hasło i porównuje z hashem.
					if(password_verify($_POST['pass'], $row['pass']))
					{
						//ZALOGOWANO
					
						//Wypełnienie danymi:
						$_SESSION['id'] 		= $row['id'];
						$_SESSION['login'] 	= $row['login'];
					}
					else
						$login_failed = "pass";
				}
				else
					$login_failed = "login";
			}
			$result->free();
			$connection->close();
		}
		
		//Udało się zalogować.
		
		if(!isset($login_failed))
		{
			header('Location: admin.php');
			exit();
		}
	}
?>



<?php require_once "_commonHTML_head(meta+fonts+style).html"; ?>
<link rel="stylesheet" href="style/login.css" type="text/css"/>
<style>
	.form_container form
	{
		max-width: 215px;
	}
</style>


<body>	

	<div class="viniete"></div>
	<div class="top_separation"></div>
	
	
	<div class="form_container">
		<form method="post">
			<input type="text" 		maxlength="20"	size="20" name="login" placeholder="Login" required autofocus/><br/>
			<input type="password" maxlength="20"	size="20" name="pass" placeholder="Hasło" required/><br/>	
			<button type="submit"><i class="demo-icon icon-login"></i>Zaloguj się</button>
		</form>
		
		<div class="error">
			<?php
				if(isset($login_failed))
				{
					if($login_failed == "login") echo "Wpisano zły login.";
					if($login_failed == "pass") echo "Wpisano złe hasło.";
				}
				
				if(isset($_SESSION['not_logged_in']))
				{	
					unset($_SESSION['not_logged_in']);
					echo "Musisz być zalogowany by przejść do tej strony!";
				}
			?>
		</div>
		
		<div class="message">
			<?php
				if(isset($_SESSION['account_deleted']))
				{
					unset($_SESSION['account_deleted']);
					echo 'Twoje konto zostało usiunięte.<br><br>Wróć do <a href="index.php">strony głównej,</a><br>lub <a href="register.php">stwórz nowe konto.</a>';
				}
				else if(isset($_SESSION['registration_success']))
				{
					unset($_SESSION['registration_success']);
					echo "Rejestracja się powiodła. Możesz się teraz zalogować.";
				}
				else
				{
					echo 'Nie posiadasz konta? <a href="register.php">Zarejestruj się.</a>';
				}
			?>
		</div>

	</div>
	
	<div class="footer">
	<div><a href="index.php">Wróć do Strony Głównej.<i class="demo-icon icon-home"></i></a></div>
	IV Liceum Ogólnokształcące &copy; Wszelkie prawa zastrzeżone.</div>
	
</body>
</html>