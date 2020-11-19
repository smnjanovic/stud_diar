<?php
class PageLoader {
  private static $NOT_EXIST = "Stránka, ktorú hľadáte neexistuje!";
  private static $LOGGED_OUT_ONLY = "Už ste prihlásený!";
  private static $LOGGED_IN_ONLY = "Stránka je dostupná len pre prihlásených používateľov!";
  private static $ACCESS_DENIED = "Neoprávnený vstup!";
  private static $STRANGE_MISTAKE = "Zvláztná chyba!";
  private static $OK = "OK";

  private $menuItems;
  private $validItems;
  private $page;
  private $uid;

  public static function findIdByContentName($content) {
    $res = DB::getResults("SELECT id FROM menu WHERE content LIKE ?", [$content]);
    return @$res[0]["id"];
  }

  public static function denyAccess() {
    return ["title"=>self::$ACCESS_DENIED, "content"=>"error"];
  }

  public function __construct($page, $uid) {
    $this->page = $page;
    $this->uid = $uid;
    $this->menuItems = DB::getResults("SELECT id, title, css, content, svgicon, loggedin, loggedout FROM menu");
  }

  public function getValidState($page) {
    $res = DB::getResults("SELECT title, loggedin, loggedout FROM menu WHERE id = ?", [$page]);
    if (count($res) == 0) return self::$NOT_EXIST;
    if (count($res) == 1) {
      $out = $res[0]["loggedout"] == 1;
      $in = $res[0]["loggedin"] == 1;

      // stranka dostupna vsetkych
      if ($in && $out || $out && !$this->uid) return self::$OK;
      if (!$in && $out && $this->uid) return self::$LOGGED_OUT_ONLY;
      if (!$out && $in && !$this->uid) return self::$LOGGED_IN_ONLY;
      // nemala by takáto stránka byť
      if (!$in && !$out) self::$ACCESS_DENIED;
      return self::$OK;
    }
    return self::$STRANGE_MISTAKE;
  }

  public function getValidItems() {
    if (!$this->validItems)
      $this->validItems = array_filter($this->menuItems, function($item) {
        return $this->getValidState($item["id"]) == self::$OK;
      });
    return $this->validItems;
  }

  public function getPageData() {
    $filtered = array_filter($this->getValidItems(), function($row){
      return $row["id"] == $this->page;
    });
    if (count($filtered) == 1) return $filtered[array_keys($filtered)[0]];
    return ["title"=>$this->getValidState($this->page), "content"=>"error"];
  }
}
