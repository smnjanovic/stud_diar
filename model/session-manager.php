<?php
namespace Model;

use Model\User;

class SessionManager {
  const ID = 'uid';
  const NICK = 'nick';
  const EMAIL = 'email';

  /**
  * Prihlásenie užívateľa. Bez garancie jeho existencie :-(.
  * @param user používateľ, ktorého sa snažím prihlásiť
  */
  public static function logIn($user) {
    if (!isset($_SESSION)) session_start();
    $_SESSION[self::ID] = $user->getId();
    $_SESSION[self::NICK] = $user->getNick();
    $_SESSION[self::EMAIL] = $user->getEmail();
  }

  /**
  * Odhlásenie užívateľa
  */
  public static function logOut() {
    session_unset();
  }

  /**
  * Kontrola, či je niekto prihlásený
  * @return bool 1, ak je niekto prihlásený
  */
  public static function hasLoggedInUser(): bool {
    return isset($_SESSION[self::ID], $_SESSION[self::NICK], $_SESSION[self::EMAIL])
      && $_SESSION[self::ID] && $_SESSION[self::NICK] && $_SESSION[self::EMAIL];
  }

  /**
  * Získanie ID používateľa
  * @return int vráti ID užívateľa alebo null;
  */
  public static function getUserId(): int {
    if (isset($_SESSION[self::ID])) return $_SESSION[self::ID];
    return -1;
  }

  public static function getUserNick() {
    if (isset($_SESSION[self::NICK])) return $_SESSION[self::NICK];
    return "";
  }

  public static function updateUserNick($nick) {
    if (isset($_SESSION[self::ID])) $_SESSION[self::NICK] = $nick;
  }

  public static function getUserEmail(): string {
    if (isset($_SESSION[self::EMAIL])) return $_SESSION[self::EMAIL];
    return -1;
  }

  public static function updateUserEmail($email) {
    if (isset($_SESSION[self::ID])) $_SESSION[self::EMAIL] = $email;
  }

  public static function getUser() {
    if (!self::hasLoggedInUser()) return null;
    return new User($_SESSION[self::ID], $_SESSION[self::NICK], $_SESSION[self::EMAIL]);
  }

  /**
  * Kontrola, či sú dáta užívateľa so strany prihláseného prepísateľné
  * @param user Používateľ, ktorého dáta chcem ovplyvňovať
  * @return bool 1, ak mám právo meniť zobrazovať
  */
  public static function isUserModifiable($user): bool {
    return @$_SESSION[self::ID] && $user == $_SESSION[self::ID] ||
      is_object($user) && @$user->getId() == $_SESSION[self::ID];
  }
}
