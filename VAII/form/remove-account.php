<?php
session_start();
require "../model/db.php";
require "../model/user.php";
require "../model/page-loader.php";

DB::connect();

//pripad kedy chce sa chce uzivatel odstranit sam
$user = User::getLoggedInUser();
if ($user) $user->removeAccount();
unset($user);
session_unset();
header("Location: ../index.php?page=".PageLoader::findIdByContentName("log-in")."&action=access-account");
die();
