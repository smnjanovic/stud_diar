<?php
require_once "../model/db.php";
require_once "../model/page-loader.php";
require_once "../model/user.php";

DB::connect();

if ($_POST) {
  $errors = User::registerUser(@$_POST['nick'], @$_POST['email'], @$_POST['pass'], @$_POST['pass2']);
  if (count($errors) === 0) {
    $user = User::getLoggedInUser()->getNick();
    $page = PageLoader::findIdByContentName("account");
    header("Location: ../index.php?page=$page&user=$user");
    die();
  }
  ?>
  <ul>
    <?php foreach($errors as $error) { ?>
      <li><?= $error ?></li>
    <?php } ?>
  </ul>
  <a href="../index.php?page=<?= $page ?>&action=create-account">Späť na registráciu</a>
  <?php
}
