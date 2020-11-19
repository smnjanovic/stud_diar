<?php
require_once "../model/db.php";
require_once "../model/user.php";

DB::connect();

session_start();

if ($_POST && isset($_POST['id'])) {
  $targetUser = User::getUserById($_POST['id']);
  $loggedInUser = User::getLoggedInUser();

  if ($targetUser && $loggedInUser && $targetUser->compareTo($loggedInUser)) {
    if (isset($_POST['nick'])) {
      $err = $targetUser->changeNick($_POST['id'], trim($_POST['nick']));
      if (count($err) == 0) die(trim($_POST['nick']));
    }
    else if (isset($_POST['mail'])) {
      $err = $targetUser->changeEmail($_POST['id'], trim($_POST['mail']));
      if (count($err) == 0) die(trim($_POST['mail']));
    }
  }
}
