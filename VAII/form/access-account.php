<?php
require_once "../model/db.php";
require_once "../model/page-loader.php";
require_once "../model/user.php";

DB::connect();

if ($_POST) {
  $error = User::loginUser(@$_POST['nick'], @$_POST['pass']);
  if (!$error) {
    $page = @PageLoader::findIdByContentName("account");
    $user = User::getLoggedInUser()->getNick();
    header("Location: ../index.php?page=$page&user=$user");
    die();
  }
  $page = @PageLoader::findIdByContentName("log-in");

  ?>
  <div><?= $error ?></div>
  <a href="../index.php?page=<?= $page ?>&action=access-account">Skúsiť znova</a>
  <a href="../index.php?page=<?= $page ?>&action=create-account">Zaregistrovať sa</a>
  <?php
}
