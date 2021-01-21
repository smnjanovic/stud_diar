<?php
namespace Model;

use Model\User;
use Model\SessionManager;

class UserManager {
  const DIRECTORY = "users";

  const TABLE = 'user';
  const ID = 'id';
  const NICK = 'nick';
  const EMAIL = 'email';
  const PASS = 'pass';

  public static function registerUser($nick, $email, $pass1, $pass2) {
    $errors = array_merge(self::nickErrors($nick), self::emailErrors($email), self::passwordErrors($pass1, $pass2));
    if (!count($errors)) {
      $values = [$nick, $email, md5($pass1)];
      $sql = "INSERT INTO " . self::TABLE . " (" . self::NICK . ", " . self::EMAIL . ", " . self::PASS . ") VALUES (?, ?, ?)";
      DB::execQuery($sql, $values);
      SessionManager::logIn(new User(DB::getLastInsertId(), $nick, $email));
    }
    return $errors;
  }

  public static function loginUser($nickemail, $pass) {
    $errors = [];
    $user = self::getUserByNameOrEmail($nickemail);
    if (!$user) $errors[] = "Používateľ s touto prezývkou alebo e-mailom tu ešte nie je registrovaný!";
    elseif (!self::verifyPassword($user, $pass)) $errors[] = "Zadali ste nesprávne heslo!";
    if (count($errors) === 0) SessionManager::logIn($user);
    return $errors;
  }

  public static function changeNick($id, $nick) {
    $errors=[];
    if (!SessionManager::isUserModifiable($id)) $errors[] = "Nedostatočné práva!";
    $errors = array_merge($errors, self::nickErrors($nick));
    if (count($errors) === 0) {
      DB::execQuery("UPDATE " . self::TABLE . " SET ".self::NICK."=? WHERE ".self::ID."=?",[$nick, $id]);
      if (SessionManager::getUserId() == $id)
        SessionManager::updateUserNick($nick);
    }
    return $errors;
  }

  public static function changeEmail($id, $email) {
    $errors=[];
    if (!SessionManager::isUserModifiable($id)) $errors[] = "Nedostatočné práva!";
    $errors = array_merge($errors, self::emailErrors($email));
    if (count($errors) === 0) {
      $query = "UPDATE " . self::TABLE . " SET " . self::EMAIL .
        "=? WHERE " . self::ID . "=?";
      DB::execQuery($query, [$email, $id]);
      if (SessionManager::getUserId() == $id)
        SessionManager::updateUserNick($email);
    }
    return $errors;
  }

  public static function changePassword($id, $old, $new1, $new2) {
    $errors=[];
    if (!SessionManager::isUserModifiable($id)) $errors[] = "Nedostatočné práva!";
    $t = self::TABLE;
    $cid = self::ID;
    $cpass = self::PASS;
    if (count(DB::getResults("SELECT 1 FROM $t WHERE $cid = ? AND $cpass = ?", [$id, md5($old)])) == 0)
      $errors[] = "Nesprávne zadané staré heslo!";
    if ($new1 != $new2) $errors[] = "Nové heslá sa nezhodujú!";
    if (count($errors) === 0) {
      $update = "UPDATE $t SET $cpass = ? WHERE $cid = ? AND $cpass LIKE ?";
      $args = [md5($new1), $id, md5($old)];
      DB::execQuery($update, $args);
    }
    return $errors;
  }

  public static function removeAccount($id) {
    if (SessionManager::isUserModifiable($id)) {
      DB::execQuery("DELETE FROM " . self::TABLE . " WHERE " . self::ID . "=?", [$id]);
      if (SessionManager::getUserId() == $id)
        SessionManager::logOut();
      return [];
    }
    return ["Nedostatočné práva!"];
  }

  public static function nickErrors($nick) {
    $errors = [];
    if (!$nick) $errors[] = "Nick je prázdny!";
    if (strlen($nick) > 15) $errors[] = "Nick je príliš dlhý!";
    if (!preg_match("/^[a-zA-Z][a-zA-Z0-9]+$/i", $nick)) $errors[] = "Nick má zlý formát";
    $query = "SELECT 1 FROM " . self::TABLE . " WHERE " . self::NICK . "=?";
    if (count(DB::getResults($query, [$nick])) > 0)
      $errors[] = "Nick je obsadený!";
    return $errors;
  }

  public static function emailErrors($email) {
    $errors = [];
    if (!$email) $errors[] = "Email je prázdny!";
    if (!preg_match("/^((\"[^\"]*?\")|([^\(\)\<\>\@\,\;\:\\\"\[\]\s\*\/]+))@(\["
    ."((25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\.){3}|((([a-zA-Z0-9\-]"
    ."+)\.)+))([a-zA-Z]{2,}|(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\])$/si",
    $email))  $errors[] = "Email je v zlom formáte!";
    $query = "SELECT 1 FROM " . self::TABLE . " WHERE " . self::EMAIL . "=?";
    if (count(DB::getResults($query, [$email])) > 0) $errors[] = "Email je už obsadený!";
    return $errors;
  }

  public static function passwordErrors($pass1, $pass2) {
    $errors = [];
    if (!$pass1 || !$pass2 || strlen($pass1) < 6 || strlen($pass2) < 6) $errors[] = "Heslo je príliš krátke!";
    if ($pass1 != $pass2) $errors[] = "Heslá sa nezhodujú!";
    return $errors;
  }

  public static function getUserById($id) {
    $query = "SELECT " . self::ID . ", " . self::NICK . ", "
      . self::EMAIL . " FROM ".self::TABLE." WHERE ".self::ID."=?";
    $rows = DB::getResults($query, [intval($id)]);
    if (count($rows) != 1) return null;
    return new User($rows[0][self::ID], $rows[0][self::NICK], $rows[0][self::EMAIL]);
  }

  public static function getUserByNameOrEmail($string) {
    $query = "SELECT " . self::ID . ", " . self::NICK . ", " . self::EMAIL . " FROM " . self::TABLE
      . " WHERE " . self::NICK . " LIKE ? OR " . self::EMAIL . " LIKE ?";

    $rows = DB::getResults($query, [$string, $string]);
    if (count($rows) == 1) {
      $user = new User($rows[0][self::ID], $rows[0][self::NICK], $rows[0][self::EMAIL]);
      return $user;
    }
    return null;
  }

  public static function verifyPassword($user, $password) {
    $query = "SELECT 1 FROM " . self::TABLE . " WHERE " . self::ID . " = ? AND " . self::PASS . " LIKE ?";
    return count(DB::getResults($query, [$user->getId(), md5($password)])) == 1;
  }

}
