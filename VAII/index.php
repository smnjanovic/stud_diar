<?php
session_start();
require_once "model/db.php";
require_once "model/page-loader.php";
require_once "model/user.php";

DB::connect();
$pageLoader = new PageLoader(@$_GET["page"], @$_SESSION['uid']);
?>

<!DOCTYPE html>
<html lang="sk">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta charset="utf-8">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" href="css/main.css">
<?php if (@$pageLoader->getPageData()["css"]) { ?>
<link rel="stylesheet" type="text/css" href="css/<?= $pageLoader->getPageData()["css"] ?>.css">
<?php } ?>
<title><?= $pageLoader->getPageData()["title"] ?></title>
</head>
<body>
	<div id="mobile_menu" class="hidden">
		<div><span class="fa fa-close"></span></div>
		<script>
			(function(){
				document.querySelector("#mobile_menu .fa.fa-close").onclick = function() {
					document.querySelector("#mobile_menu").classList.add("hidden")
				}
			})()
		</script>
		<div>
			<ul>
				<?php foreach($pageLoader->getValidItems() as $item) {?>
				<li><a href="?page=<?= $item["id"] ?>"><?= $item["title"] ?></a></li>
				<?php } ?>
			</ul>
		</div>
	</div>
	<div id="container">
		<div id="user-bar">
			<div id="mobile_menu_opener">
				<span class="fa fa-navicon"></span>
			</div>

			<script>
				(function(){
					document.querySelector("#mobile_menu_opener").onclick = function() {
						document.querySelector("#mobile_menu").classList.remove("hidden")
					}
				})()
			</script>

			<div id="app-name">
				<h1><a href="">Študentský diár</a></h1>
			</div>

			<?php
			$user = User::getLoggedInUser();
			 ?>

			<div id="user-detail">
				<?php if (User::getLoggedInUser()) { ?>
				<a href="form/log-out.php" class="fa fa-power-off" title="Odhlásiť"><span>Odhlásiť</span></a>
				<?php } else { ?>
				<a href="?page=<?= PageLoader::findIdByContentName("log-in") ?>&action=access-account" class="fa fa-user" title="Prihlásiť"><span>Prihlásiť</span></a>
				<?php } ?>
			</div>
			<!--Koniec usera-->
		</div>
		<nav>
			<ul>
				<!-- <li><a href="#" title="Účet"><img src="https://media0.webgarden.com/images/media0:4e2b6b54034a9.jpg/wall_ANIMALSINLOVE_1280x1024_07.jpg" alt="Profilová fotka" width="40" height="40"></a></li> -->
				<?php foreach ($pageLoader->getValidItems() as $item) {
					if ($item["svgicon"]) { ?>

				<li<?php if ($item["id"] == @$pageLoader->getPageData()["id"]) { ?> class="active" <?php } ?>>
          <a href="?page=<?= $item["id"] ?>" title="<?= $item["title"] ?>">
            <?= $item["svgicon"] ?>
          </a>
        </li>
				<?php } } ?>
			</ul>
		</nav>
		<main><?php include "view/" . $pageLoader->getPageData()["content"] . ".php" ?></main>
		<footer>Vytvoril: <a href="mailto::janovic3@stud.uniza.sk">Šimon Janovič</a></footer>
	</div>
</body>
</html>
