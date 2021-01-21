<?php
namespace Model;

use Model\UserManager;

class User {
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

  public function getDirectoryPath() {
    return UserManager::DIRECTORY . '/' . $this->id;
  }

  public function compareTo($user) {
    return $this->id == $user->id;
  }
}
