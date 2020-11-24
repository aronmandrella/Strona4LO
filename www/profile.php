<?php
	session_start();
	
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
	
	require_once "php/parameters.php";
	
	define("ERROR_LOGIN_WRONG_LENGTH", 			"Login musi posiadać od 3 do 20 znaków.");
	define("ERROR_LOGIN_NOT_ALPHANUMERIC", 	"Login może składać się tylko z liter i cyfr (bez polskich znaków).");
	define("ERROR_EMAIL_NOT_CORRECT", 				"Podaj poprawy adres email.");
	define("ERROR_EMAIL_IS_USED", 						"Ten email jest już zajęty.");
	define("ERROR_LOGIN_IS_USED", 						"Ten login jest już zajęty.");
	define("ERROR_PASSWORD_WRONG_LENGTH", 	"Hasło musi posiadać od 8 do 20 znaków.");
	define("ERROR_PASSWORD_DIFFERENT", 			"Podane hasła nie są identyczne.");
	define("ERROR_CHECKBOX_NOT_CHECKED", 		"Musisz przysiąc!");
	define("ERROR_RECAPTCHA_FAILED", 				"Potwierdź że nie jesteś botem.");
		
	define("reCAPTCHA_SiteKey", 				"6LeGKTcUAAAAAPmTf6T8U4iACCQIYfM_epN5It__");
	define("reCAPTCHA_SecretKey", 				"6LeGKTcUAAAAACvuQ0ytjNcBAiidNf5d5UPg_RIy");	
	
	$validation_succeed = false;
	

	
	if(!empty($_POST))
	{
		//-------------------------------------------------------------//
		//----------WALIDACJA DANYCH Z FORMULARZA---------//
		//-------------------------------------------------------------//

		$validation_succeed = true;
		
		if (isset($_POST['login']) && $_POST['function']=='login' && isset($_POST['old_pass']))
		{
			//Sprawdzenie długości nicku.
			if ((strlen($_POST['login'])<3) || (strlen($_POST['login'])>20))
			{
				$validation_succeed = false;
				$error_login = ERROR_LOGIN_WRONG_LENGTH;
			}
			
			
			//Sprawdzenie czy w nicku nie ma znaków specialnych
			//ctype_alnum() - nie przepuści np ą
			//preg_match()  - jakieś wyrażenia regularne?
			if (ctype_alnum($_POST['login']) == false)
			{
				$validation_succeed = false;
				$error_login = ERROR_LOGIN_NOT_ALPHANUMERIC;
			}
		}

		else if (isset($_POST['email']) && $_POST['function']=='email' && isset($_POST['old_pass']))
		{
			//Sprawdzenie poprawności emaila
			//filter_var(zmienna, filte)  - przefiltruje zmienną przez rodzaj flitru.
			//FILTER_SANITIZE_EMAIL - usunie wszystkie niebezpieczne znaki i zwróci nowy (sanityzacja)
			//FILTER_VALIDATE_EMAIL - sprawdzi budowę emiala.
			$email_safe = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
			if ( (filter_var($email_safe, FILTER_VALIDATE_EMAIL) == false) || ($email_safe != $_POST['email']))
			{
				$validation_succeed = false;
				$error_email = ERROR_EMAIL_NOT_CORRECT;
			}
		}
	
		else if (isset($_POST['pass1']) && isset($_POST['pass2']) && $_POST['function']=='pass' && isset($_POST['old_pass']))
		{
			//Sprawdzenie długości hasła.
			if ((strlen($_POST['pass1'])<3) || (strlen($_POST['pass1'])>20))
			{
				$validation_succeed = false;
				$error_pass = ERROR_PASSWORD_WRONG_LENGTH;
			}
			
			
			//Sprawdzenie czy oba hasła są takie same.
			if ($_POST['pass1'] != $_POST['pass2'])
			{
				$validation_succeed = false;
				$error_pass = ERROR_PASSWORD_DIFFERENT;
			}
				
				
			//Hashowanie hasła.
			//password_hash zamienia hasło na ciąg znaków który jest jednoznacznym jego
			//odpowiednikiem, ale jest baaardzo trudny do zamiany na hasło.
			//password_hash dodaje dodatkowo sól (losowe znaki na początku hasła)
			//PASSWORD_DEFAULT - użyj najsilniejszego algorytmu hashowania (bcrypt w php 5.5)
			//zalecany rozmiar kolumny na hasło w bazie danych to 255 znaków.
			//UWAGA!! zahashowanego hasła nie trzeba sanityzować.
			$pass_hash = password_hash($_POST['pass1'], PASSWORD_DEFAULT);
		}
		
		else if ($_POST['function']=='delete' && isset($_POST['old_pass']))
		{
		}
		
		else
		{
			header('Location: profile.php');
			exit();
		}
	
	}
	
	if($validation_succeed)	
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
			//-----------DALSZA WALIDACJA DANYCH----------------//
			//-------------------------------------------------------------//
			
			if(isset($_POST['email']))
			{
				//Sprawdzenie czy istnieje konto z takim emailem.
				$result = $connection->query("SELECT id FROM user WHERE email='".$_POST['email']."'");
				if (!$result)	{header('Location: profile.php'); exit();}
				if($result->num_rows > 0)
				{
					$validation_succeed = false;
					$error_email = ERROR_EMAIL_IS_USED;
				}
			}


			if(isset($_POST['login']))
			{
				//Sprawdzenie czy istnieje konto z takim nickiem.
				$result = $connection->query("SELECT id FROM user WHERE login='".$_POST['login']."'");
				if (!$result)	{header('Location: profile.php'); exit();}	
				if($result->num_rows > 0)
				{
					$validation_succeed = false;
					$error_login = ERROR_LOGIN_IS_USED;
				}
			}

		//-------------------------------------------------------------//
		//----------SPRAWDZENIE STAREGO HASŁA--------------//
		//--------------Z HASHOWANIEM W BAZIE-----------------//
		//-------------------------------------------------------------//
		
		if($validation_succeed)	
		{	
			//mysqli_real_escape_string - dodatkowy skryt przeciw wstrzykiwaniu SQL'a			
			$sql = "SELECT * FROM user WHERE login='".$_SESSION['login']."'";
				
			if( $result = @$connection->query($sql) )
			{		
				if($row = $result->fetch_assoc())
				{
					//Sprawdzanie hasła z hashowaniem.
					//password_verify hashuje hasło i porównuje z hashem.
					if(password_verify($_POST['old_pass'], $row['pass']))
					{
						//MOŻNA ZMIENIAĆ DANE:
					
						switch($_POST['function'])
						{
							case 'delete':
								$sql = "DELETE FROM user WHERE login='".$_SESSION['login']."'";
								break;
							case 'pass':
								$sql = "UPDATE user SET pass = '$pass_hash' WHERE login='".$_SESSION['login']."'";
								break;
							case 'login':
								$sql = "UPDATE user SET login = '".$_POST['login']."' WHERE login='".$_SESSION['login']."'";
								break;
							case 'email':
								$sql = "UPDATE user SET email = '".$_POST['email']."' WHERE login='".$_SESSION['login']."'";
								break;
						}
						
						$result = $connection->query($sql);
						if (!$result)	{header('Location: profile.php'); exit();}	

						switch($_POST['function'])
						{
							case 'delete':
							{
								session_unset();
								$_SESSION['account_deleted'] = true;
								header('Location: login.php'); exit();
								break;
							}	
							case 'pass':
							{
								$_POST['succsess'] = "Udało się zmienić hasło.";
								break;
							}
							case 'login':
							{
								$_POST['succsess'] = "Udało się zmienić login na: ".$_POST['login'].".";
								$_SESSION['login'] = $_POST['login'];
								break;
							}
							case 'email':
							{
								$_POST['succsess'] = "Udało się zmienić email na: ".$_POST['email'].".";
								break;
							}
						}
						
					}
					else
						$login_failed = "pass";
				}
			}
			$connection->close();
		}
	}
?>



<?php require_once "_commonHTML_head(meta+fonts+style).html"; ?>
<link rel="stylesheet" href="style/login.css" type="text/css"/>
<style>
	.form_container form
	{
		max-width: 300px;
	}
	button.nav_butt
	{
		font-size: 18px;
		margin: 6px;
		color: #aaa;
	}
	button.nav_butt:hover,
	button.nav_butt:focus
	{
		color: #bbb;
	}
	form
	{
		display: none;
	}
	#start i
	{
		color: #567;
	}
	#start
	{
		color: #ccc;
	}
	
	.viniete
{
	background: url("style/img/gray.jpg");
	background-size: auto;

	opacity: 0.5;
	
	box-shadow: inset 0px 0px 200px 0px rgba(0,0,0,1);
	 -webkit-filter: blur(2px);
	-moz-filter: blur(2px);
	-o-filter: blur(2px);
	-ms-filter: blur(2px);
	filter: blur(2px);
}

</style>


<body>	

	<div class="viniete"></div>
	<div class="top_separation"></div>
	
	<div class="form_container" style="padding: 0;">
		<div class="navigation">
			<button class="nav_butt" data-what="pass">Zmień hasło</button>
			<button class="nav_butt" data-what="login">Zmień login</button>
			<button class="nav_butt" data-what="email">Zmień e-mail</button>
			<button class="nav_butt" data-what="delete">Usuń konto</button>
		</div>
	</div>
	
	<div class="top_separation"></div>
	
	<div class="form_container">
		
		<div id="start" style="display: none;">
		Wybierz co chcesz zrobić w panelu u góry.
		<div class="top_separation"></div>
		<i class="icon-wrench" style="font-size: 50px; margin: 20px;"></i>
		<div class="top_separation"></div>
		</div>
		
		
		<!--USUWANIE KONTA-->
		<form method="post" id="delete">
			<input type="password" maxlength="20"	size="20" name="old_pass" placeholder="Wpisz hasło" required/><br/>
			<?php if(isset($login_failed))echo '<div class="error">Wpisano złe hasło.</div>'; ?>
			<input type="hidden" name="function" value="delete"><br/>
			

			<button type="submit"><i class="demo-icon icon-cancel"></i>Usuń konto</button>
		</form>
		
		
		<!--ZMIANA HASŁA-->
		<form method="post"  id="pass">
			<input type="password" maxlength="20"	size="20" name="pass1" placeholder="Nowe hasło" required/><br/>	
			<?php
				if(isset($error_pass))
				{
					echo '<div class="error">'.$error_pass.'</div>';
				}
			?>
			<input type="password" maxlength="20"	size="20" name="pass2" placeholder="Powtórz nowe hasło" required/><br/>
			<input type="password" maxlength="20"	size="20" name="old_pass" placeholder="Stare hasło" required/><br/>
			<?php if(isset($login_failed))echo '<div class="error">Wpisano złe hasło.</div>'; ?>
			<input type="hidden" name="function" value="pass"><br/>	

			
			<button type="submit"><i class="demo-icon icon-wrench"></i>Zmień hasło</button>
		</form>
		
		
		<!--ZMIANA LOGINU-->
		<form method="post"  id="login">
			<input type="text" maxlength="20"	size="20" name="login" placeholder="Nowy login"
				value="<?php if(isset($_POST['login'])) echo $_POST['login'];?>" required/><br/>	
			<?php
				if(isset($error_login)) 
				{
					echo '<div class="error">'.$error_login.'</div>';
					
					echo '<script>';
					echo 	'$("input[name=\'login\'").css({"border":"2px solid #a33", "background":"#b99"})';
					//RESET				
					echo 	'$("input[name=\'login\'").focus(function(){"color":"#977", "background":"#b99", "border":"2px solid #822"});});';
					echo 	'$("input[name=\'login\'").focus(function(){"color":"#142e3b", "background":"#dce6eb", "border":"2px solid #578"});});';
					echo '</script>';
				}			
			?>
			<input type="password" maxlength="20"	size="20" name="old_pass" placeholder="Wpisz hasło" required/><br/>
			<?php if(isset($login_failed))echo '<div class="error">Wpisano złe hasło.</div>'; ?>
			<input type="hidden" name="function" value="login"><br/>	

			
			<button type="submit"><i class="demo-icon icon-wrench"></i>Zmień login</button>
		</form>
		
		
		<!--ZMIANA MAILA-->
		<form method="post"  id="email">
			<input type="emal" size="20" name="email"  placeholder="Nowy e-mail"
				value="<?php if(isset($_POST['email'])) echo $_POST['email'];?>" required/><br/>
			<?php
				if(isset($error_email))
				{
					echo '<div class="error">'.$error_email.'</div>';
				}
			?>
			<input type="password" maxlength="20"	size="20" name="old_pass" placeholder="Wpisz hasło" required/><br/>
			<?php if(isset($login_failed))echo '<div class="error">Wpisano złe hasło.</div>'; ?>
			<input type="hidden" name="function" value="email"><br/>	

			<button type="submit"><i class="demo-icon icon-wrench"></i>Zmień e-mail</button>
		</form>
		
		<div class="message">
			<?php
					if(isset($_POST['succsess'])) echo '<div style="font-size: 20px">'.$_POST['succsess']."</div>"; echo '<br/>';
					echo 'Wróć do <a href="admin.php">Panelu Administracyjnego.</a>';
			?>
		</div>

	</div>
	
	<div class="footer">
	<div><a href="index.php">Wróć do Strony Głównej.<i class="demo-icon icon-home"></i></a></div>
	IV Liceum Ogólnokształcące &copy; Wszelkie prawa zastrzeżone.</div>
	
</body>
</html>

<script>

	var nav = ["delete", "login", "pass", "email", "start"];
	
	$('.navigation button').click(function()
	{
		for(var i=0; i<nav.length; i++)
		{
			if(nav[i]==$(this).data("what"))
			{
				$('#'+nav[i]).css("display", "block");
				$('[data-what="'+nav[i]+'"]').css("text-decoration","underline");
			}
			else
			{
				$('#'+nav[i]).css("display", "none");
				$('[data-what="'+nav[i]+'"]').css("text-decoration","none");
				
			}
		}
	});
	
		//DOMYŚLNIE
		var default_nav = "start";
		
		<?php if(isset($_POST['function'])) echo 'default_nav = "'.$_POST['function'].'"'?>
		
		for(var i=0; i<nav.length; i++)
		{
			if(nav[i]==default_nav)
			{
				$('#'+nav[i]).css("display", "block");
				$('[data-what="'+nav[i]+'"]').css("text-decoration","underline");
			}
			else
			{
				$('#'+nav[i]).css("display", "none");
				$('[data-what="'+nav[i]+'"]').css("text-decoration","none");
				
			}
		}

</script>