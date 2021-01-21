<?php
namespace MAIN;
session_start();
require_once "model/db.php";
require_once "model/page-loader.php";
require_once "model/session-manager.php";
require_once "model/user.php";
require_once "model/user-manager.php";
require_once "model/form-keys.php";
require_once "model/subject-manager.php";
require_once "model/note-manager.php";

use Model\FormKeys;
use Model\PageLoader;
use Model\SessionManager;
use Model\User;
use Model\UserManager;
use Model\DB;

DB::connect();

PageLoader::evadeInvalidURL();

$menu = PageLoader::getValidMenuPages();
$page = PageLoader::getPageById($_GET['page']);

?>
<!DOCTYPE html>
<html lang="sk">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta charset="utf-8">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" href="css/bars.css">
<link rel="stylesheet" type="text/css" href="css/main.css">
<link rel="stylesheet" type="text/css" href="css/popup.css">
<?php if ($page->hasCss()) { ?>
<link rel="stylesheet" type="text/css" href="css/<?= $page->getCss() ?>.css?version=2">
<?php } ?>
<?php if ($page->getContent() != PageLoader::NOJS) { ?>
<noscript>
	<meta http-equiv="refresh" content="0; url=<?= PageLoader::makeLink(PageLoader::NOJS) ?>" />
</noscript>
<?php } else { ?>
	<script>
		window.location = "<?=PageLoader::DOMAIN?>";
	</script>
<?php } ?>
<title><?= $page->getTitle() ?></title>
</head>
<body>
	<!--Nesmie byť vnútri ani neviditeľný znak-->
	<div id="popup-zone"></div>
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
				<?php foreach($menu as $item) {?>
					<li>
						<a href="?page=<?= $item->getId() ?>"><?= $item->getTitle() ?></a>
					</li>
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
			$user = SessionManager::getUser();
			 ?>

			<div id="user-detail">
				<?php if ($user) { ?>
					<span><?=$user->getNick()?></span>
					<a href="#" title="Odhlásiť" class="fa fa-sign-out"><span>Odhlásiť</span></a>
					<script>
					(function(logout){
						logout.addEventListener("click", function(e){
							e.preventDefault();
							let fd = new FormData();
							fd.append("<?=FormKeys::SIGN_OUT_FORM?>", "");
							fetch("ajax-forms.php", { method: 'POST', body: fd })
								.then(res => res.text())
								.then(data => { window.location = data })
								.catch(error => {console.log(error)});
						});
					})(document.querySelector("#user-detail>a"));
					</script>
				<?php } else { ?>
					<a href="<?=PageLoader::makeLink(PageLoader::LOG_IN, ["action" => FormKeys::SIGN_IN_FORM])?>" class="fa fa-sign-in" title="Prihlásiť"><span>Prihlásiť</span></a>
					<a href="<?=PageLoader::makeLink(PageLoader::LOG_IN, ["action" => FormKeys::SIGN_UP_FORM])?>" class="fa fa-user-plus" title="Registrovať"><span>Registrovať</span></a>
				<?php } ?>
			</div>
			<!--Koniec usera-->
		</div>
		<nav>
			<ul>
				<!-- <li><a href="#" title="Účet"><img src="https://media0.webgarden.com/images/media0:4e2b6b54034a9.jpg/wall_ANIMALSINLOVE_1280x1024_07.jpg" alt="Profilová fotka" width="40" height="40"></a></li> -->
				<?php foreach ($menu as $item) { ?>
					<li <?=$item->equals($page) ? 'class="active"' : ""?>>
	          <a href="?page=<?= $item->getId() ?>" title="<?=$item->getTitle()?>">
							<?= $item->getIcon() ?>
						</a>
	        </li>
				<?php } ?>
			</ul>
		</nav>
		<main><?php include "view/" . $page->getContent() . ".php" ?></main>
		<footer>Vytvoril: <a href="mailto::janovic3@stud.uniza.sk">Šimon Janovič</a></footer>
	</div>
</body>
</html>
