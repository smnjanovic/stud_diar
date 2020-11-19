<?php
class User {
  public static function emailExists($email) {
    return count(DB::getResults("SELECT 1 FROM user WHERE email=?", [$email]));
  }

  public static function nickExists($nick) {
    return count(DB::getResults("SELECT 1 FROM user WHERE nick=?", [$nick]));
  }

  public static function registerUser($nick, $email, $pass1, $pass2) {
    $errors = array_merge(self::nickErrors($nick), self::emailErrors($email), self::passwordErrors($pass1, $pass2));
    if (!count($errors)) {
      DB::execQuery(
        "INSERT INTO user (nick, email, pass) VALUES (?, ?, ?)",
        [$nick, $email, md5($pass1)]
      );
      (new User(DB::getLastInsertId(), $nick, $email))->logIn();
    }
    return $errors;
  }

  public static function loginUser($nickemail, $pass) {
    $user = self::getUserByNameOrEmail($nickemail);
    if (!$user) return "Používateľ s touto prezývkou alebo e-mailom tu ešte nie je registrovaný!";
    if (!User::verifyPassword($user, $pass)) return "Zadali ste nesprávne heslo!";
    $user -> logIn();
    return "";
  }

  public static function nickErrors($nick) {
    $errors = [];
    if (!$nick) $errors[] = "Nick je prázdny!";
    if (strlen($nick) > 15) $errors[] = "Nick je príliš dlhý!";
    if (!preg_match("/^[a-zA-Z][a-zA-Z0-9]+$/i", $nick)) $errors[] = "Nick má zlý formát";
    if (count(DB::getResults("SELECT 1 FROM user WHERE nick=?", [$nick])) > 0) $errors[] = "Nick je obsadený!";
    return $errors;
  }

  public static function emailErrors($email) {
    $errors = [];
    if (!$email) $errors[] = "Email je prázdny!";
    if (!preg_match("/^((\"[^\"]*?\")|([^\(\)\<\>\@\,\;\:\\\"\[\]\s\*\/]+))@(\["
    ."((25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\.){3}|((([a-zA-Z0-9\-]"
    ."+)\.)+))([a-zA-Z]{2,}|(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\])$/si",
    $email))  $errors[] = "Email je v zlom formáte!";
    if (count(DB::getResults("SELECT 1 FROM user WHERE email=?", [$email]))) $errors[] = "Email je už obsadený!";
    return $errors;
  }

  public static function passwordErrors($pass1, $pass2) {
    $errors = [];
    if (!$pass1 || !$pass2 || strlen($pass1) < 6 || strlen($pass2) < 6) $errors[] = "Heslo je príliš krátke!";
    if ($pass1 != $pass2) $errors[] = "Heslá sa nezhodujú!";
    return $errors;
  }

  public static function getUserById($id) {
    $rows = DB::getResults("SELECT id, nick, email FROM user WHERE id=?", [$id]);
    if (count($rows) == 1) return new User($rows[0]['id'], $rows[0]['nick'], $rows[0]['email']);
    return null;
  }

  public static function getUserByNameOrEmail($string) {
    $rows = DB::getResults("SELECT id, nick, email FROM user WHERE nick LIKE ? OR email LIKE ?", [$string, $string]);
    if ((count($rows)) == 1) return new User($rows[0]['id'], $rows[0]['nick'], $rows[0]['email']);
    return null;
  }

  public static function getLoggedInUser() {
    if (isset($_SESSION['uid']) && $_SESSION['uid'])
      return new User($_SESSION['uid'], $_SESSION['nick'], $_SESSION['email']);
    return null;
  }

  public static function verifyPassword($user, $password) {
    $row = DB::getResults("SELECT 1 FROM user WHERE id = ? AND pass LIKE ?", [$user->getId(), md5($password)]);
    return count($row) == 1;
  }

  private $id;
  private $nick;
  private $email;

  public function __construct($id, $nick, $email) {
    $this->id = $id;
    $this->nick = $nick;
    $this->email = $email;
  }

  public function getId() {
    return $this->id;
  }

  public function getNick() {
    return $this->nick;
  }

  public function getEmail() {
    return $this->email;
  }

  public function notifyChanges() {
    $rows = DB::getResults("SELECT nick, email FROM user WHERE id = ?", [$this->id]);
    if (count($rows) == 1) {
      $this->nick = $row[0]['nick'];
      $this->email = $row[0]['email'];
      if (isset($_SESSION['uid']) && $_SESSION['uid'] == $this->id) $this->logIn();
    }
  }

  public function logIn() {
    session_start();
    $_SESSION['uid'] = $this->id;
    $_SESSION['nick'] = $this->nick;
    $_SESSION['email'] = $this->email;
  }

  public function logOut() {
    session_unset();
  }

  public function changeNick($id, $nick) {
    $err = self::nickErrors($nick);
    if (count($err) == 0) DB::execQuery("UPDATE user SET nick=? WHERE id=?",[$nick, $id]);
    return $err;
  }

  public function changeEmail($id, $mail) {
    $err = self::emailErrors($mail);
    if (count($err) == 0) DB::execQuery("UPDATE user SET email=? WHERE id=?",[$mail, $id]);
    return $err;
  }

  public function removeAccount() {
    if (@$_SESSION['uid'] == $this->id) {
      DB::execQuery("DELETE FROM user WHERE id =?", [$this->id]);
      session_unset();
    }
  }

  public function compareTo($user) {
    return $this->id == $user->id;
  }
}
