<?php
	session_start();
	
	//Gdy już zalogowano.
	if (isset($_SESSION['id']))
	{
		header('Location: index.php');
		exit();
	}
	
	//-------------------------------------------------------------//
	//--------PARAMETRY, NAZWY, KODY CAPTCHA----------//
	//-------------------------------------------------------------//
	
	require_once "php/parameters.php";
	
	define("ERROR_LOGIN_WRONG_LENGTH", 			"Login musi posiadać od 3 do 20 znaków.");
	define("ERROR_LOGIN_NOT_ALPHANUMERIC", 	"Login może składać się tylko z liter i cyfr (bez polskich znaków).");
	define("ERROR_EMAIL_NOT_CORRECT", 				"Podaj poprawy adres email.");
	define("ERROR_EMAIL_IS_USED", 						"Ten email jest już zajęty.");
	define("ERROR_LOGIN_IS_USED", 				"Ten login jest już zajęty.");
	define("ERROR_PASSWORD_WRONG_LENGTH", 		"Hasło musi posiadać od 8 do 20 znaków.");
	define("ERROR_PASSWORD_DIFFERENT", 			"Podane hasła nie są identyczne.");
	define("ERROR_CHECKBOX_NOT_CHECKED", 		"Musisz przysiąc!");
	define("ERROR_RECAPTCHA_FAILED", 			"Potwierdź że nie jesteś botem.");
	define("ERROR_REGISTER_CODE_WRONG", 		"Wprowadzony kod rejestracyjny nie był poprawny.");
	
	define("reCAPTCHA_SiteKey", 				"xxx");
	define("reCAPTCHA_SecretKey", 				"xxx");	
	
	//-------------------------------------------------------------//
	//----------OBSŁUGA FORMULARZA LOGOWANIA----------//
	//-------------------------------------------------------------//
	
	if (isset($_POST['login']) && isset($_POST['pass1']) &&
		isset($_POST['pass2']) && isset($_POST['email']) &&
		isset($_POST['g-recaptcha-response']) && isset($_POST['register_code']))
	{	
		//-------------------------------------------------------------//
		//----------WALIDACJA DANYCH Z FORMULARZA---------//
		//-------------------------------------------------------------//
		
		$validation_succeed = true;
		
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
		
		
		//Sprawdzenie czy zaznaczono check boxa.
		if(!isset($_POST['checkbox']))
		{
			$validation_succeed = false;
			$error_checkbox = ERROR_CHECKBOX_NOT_CHECKED;
		}
		
		
		//Sprawdzenie reCAPTCHA
		$reCAPTCHA_check = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.reCAPTCHA_SecretKey.'&response='.$_POST['g-recaptcha-response']);
		$response = json_decode($reCAPTCHA_check);
		if(!$response->success)
		{
			$validation_succeed = false;
			$error_recaptcha = ERROR_RECAPTCHA_FAILED;
		}
		
		//-------------------------------------------------------------//
		//--------------POŁĄCZENIE Z BAZĄ DANYCH--------------//
		//-------------------------------------------------------------//

		//Zamiast ostrzeżeń będą rzucane wyjątki.
		mysqli_report(MYSQLI_REPORT_STRICT);
		
		require_once "php/connect.php";
		
		if($validation_succeed)
		try
		{
			//Nawiązywanie połączenia z bazą danych.
			$connection = new mysqli($host, $db_user, $db_pass, $db_name);
			if($connection->connect_errno != 0)
				throw new Exception(mysqli_connect_errno());

			//-------------------------------------------------------------//
			//-----------DALSZA WALIDACJA DANYCH----------------//
			//-------------------------------------------------------------//
			
			//Sprawdzenie czy istnieje konto z takim emailem.
			$result = $connection->query("SELECT id FROM user WHERE email='".$_POST['email']."'");
			if (!$result)	throw new Exception($connection->error);			
			if($result->num_rows > 0)
			{
				$validation_succeed = false;
				$error_email = ERROR_EMAIL_IS_USED;
			}
			
			$_POST['register_code'] = $connection->real_escape_string($_POST['register_code']);
			
			//Sprawdzenie czy kod rejestracyjny jest poprawny.
			$result = $connection->query("SELECT id FROM register_code WHERE value='".$_POST['register_code']."'");
			if (!$result)	throw new Exception($connection->error);			
			if($result->num_rows < 1)
			{
				$validation_succeed = false;
				$register_code = ERROR_REGISTER_CODE_WRONG;
			}

			
			//Sprawdzenie czy istnieje konto z takim nickiem.
			$result = $connection->query("SELECT id FROM user WHERE login='".$_POST['login']."'");
			if (!$result)	throw new Exception($connection->error);		
			if($result->num_rows > 0)
			{
				$validation_succeed = false;
				$error_login = ERROR_LOGIN_IS_USED;
			}
				
			//-------------------------------------------------------------//
			//-----------DODANIE NOWEGO UZYTKOWNIKA ----------//
			//-------------------------------------------------------------//
			
			if($validation_succeed)
			{
				$sql = sprintf("INSERT INTO user (login, pass, email) VALUES ('%s', '%s', '%s')",
									$_POST['login'],
									$pass_hash,
									$_POST['email']);
			
				$result = $connection->query($sql);
				if (!$result)	throw new Exception($connection->error);
				
				
				//Usuwanie kodu rejestracyjnego
				$result = $connection->query("DELETE FROM register_code WHERE value='".$_POST['register_code']."'");
				if (!$result)	throw new Exception($connection->error);			
				
				$connection->close();
				$_SESSION['registration_success'] = true;
				header('Location: login.php');
				exit();
			}
		}
		catch(Exception $exception)
		{			
			if(isset($connection))
				$connection->close();
			
			if(DEBUG_MODE)
				echo $exception;
			else
			{
				header('Location: server_error.php');
				exit();
			}
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
</style>


<body>

	<div class="viniete"></div>
	<div class="top_separation"></div>

	<div class="form_container">
		<form method="post">
			<div class="header">Wprowadź dane:</div>
			
			<input type="text" maxlength="20"	size="20" name="login" placeholder="Login"
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
			
			<input type="email" size="20" name="email"  placeholder="e-mail"
				value="<?php if(isset($_POST['email'])) echo $_POST['email'];?>" required/><br/>
			<?php
				if(isset($error_email))
				{
					echo '<div class="error">'.$error_email.'</div>';
				}
			?>
			
			<input type="password" maxlength="20" size="20" name="pass1" placeholder="Hasło" required/><br/>
			<?php
				if(isset($error_pass))
				{
					echo '<div class="error">'.$error_pass.'</div>';
				}
			?>
			
			<input type="password" maxlength="20" size="20" name="pass2" placeholder="Powtórz hasło" required/><br/>
			
			<!--label, przydatny trick-->
			<div class="agreement"><label><input type="checkbox" name="checkbox" 
				<?php if(isset($_POST['checkbox'])) echo "checked";?> required/> Uroczyście przysięgam, że knuję coś niedobrego.</label></div>
			<?php
				if(isset($error_checkbox)) 
				{
					echo '<div class="error">'.$error_checkbox.'</div>';
				}
			?>
			
			<input type="text" maxlength="20" size="20" name="register_code" placeholder="Wpisz kod rejestracyjny" required/><br/>
			<?php
				if(isset($register_code))
				{
					echo '<div class="error">'.$register_code.'</div>';
				}
			?>
			
			<!--Google reCAPTHA-->
			<script src='https://www.google.com/recaptcha/api.js'></script>
			<div class="g-recaptcha" data-sitekey="<?php echo reCAPTCHA_SiteKey; ?>"></div>
			<input type="hidden" name="recaptcha" data-rule-recaptcha="true">
			<?php if(isset($error_recaptcha)){echo '<div class="error">'.$error_recaptcha.'</div>';} ?>
			
			
			<div class="form_separator"></div>
			
			<button type="submit"><i class="icon-user-plus"></i>Zarejestruj się</button>
		</form>
		
		<div class="message">Posiadasz już konto? <a href="login.php">Zaloguj się.</a></div>
		
	</div>
	
	<div class="footer">
	<div><a href="index.php">Wróć do Strony Głównej.<i class="demo-icon icon-home"></i></a></div>
	IV Liceum Ogólnokształcące &copy; Wszelkie prawa zastrzeżone.</div>
	
</body>
</html>
