<?php
require "../model/db.php";
require "../model/page-loader.php";
session_start();

DB::connect();

session_unset();
header("Location: ../index.php?page=".PageLoader::findIdByContentName("log-in")."&action=access-account");
