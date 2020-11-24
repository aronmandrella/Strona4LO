<?php
	if (!isset($_GET['photo']))
	{
		header('Location: index.php');
		exit();
	}
?>

<!DOCTYPE HTML>
<html lang="pl">
<head>
	<meta charset="utf-8"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		
	<title>IV Liceum Ogólnokształcące im. Bolesława Chrobrego w Bytomiu</title>

	<!--GLOWNA CZCIAKA-->
	<link href="https://fonts.googleapis.com/css?family=Cuprum:400,700|Dancing+Script&amp;subset=latin-ext" rel="stylesheet">
	<!--IKONKI-->
	<link rel="stylesheet" href="fontello/css/fontello.css" type="text/css"/>
	<link rel="icon" href="style/img/icon.png">
</head>

<link rel="stylesheet" href="style/photo.css" type="text/css"/>

<body>
	<div class="photo_frame">
		<div class="header"><a href="<?php echo $_GET['photo']; ?>" download><i class="demo-icon icon-download"></i></a></div>
		<img src="<?php echo $_GET['photo']; ?>"/>
		<div class="footer">IV Liceum Ogólnokształcące &copy; Wszelkie prawa zastrzeżone.</div>
	</div>
	
	
	
</body>
</html>