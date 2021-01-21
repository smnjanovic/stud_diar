<?php
namespace Model;

use Model\DB;
use Model\SessionManager;
use Model\FormKeys;

class PageLoader {
  // jednotlivé podstránky dostupné v databáze v stĺpci [CONTENT]
  const LOG_IN = "log-in";
  const ACCOUNT = "account";
  const GALLERY = "gallery";
  const SCHEDULE = "schedule";
  const SUBJECTS = "subjects";
  const NOTES = "notes";
  const ERROR = "error";
  const NOJS = "noscript";

  const DOMAIN = "http://localhost/stud_diar";
  // error messages
  const NOT_EXIST = "not-exist";
  const LOGGED_OUT_ONLY = "logged-in-only";
  const LOGGED_IN_ONLY = "logged-out-only";
  const ACCESS_DENIED = "access-denied!";
  const OK = "OK";

  const NOT_EXIST_MSG = "Stránka, ktorú hľadáte neexistuje!";
  const LOGGED_OUT_ONLY_MSG = "Už ste prihlásený!";
  const LOGGED_IN_ONLY_MSG = "Stránka je dostupná len pre prihlásených používateľov!";
  const ACCESS_DENIED_MSG = "Neoprávnený vstup!";
  const STRANGE_MISTAKE_DESC = "Zvláztná chyba!";


  // tabulka SQL
  const TABLE = "menu";
  const ID = "id";
  const TITLE = "title";
  const CSS = "css";
  const IN_MENU = "inmenu";
  const CONTENT = "content";
  const ICON = "svgicon";
  const LOGGED_IN = "loggedin";
  const LOGGED_OUT = "loggedout";

  // premenné inštancie
  private $id;
  private $title;
  private $css;
  private $inMenu;
  private $content;
  private $icon;

  public static function makeLink($content, $otherData=[]): string {
    $url = self::DOMAIN . "/index.php?page=" . self::getPageByContentName($content);
    foreach ($otherData as $key => $value) $url .= "&$key=$value";
    return $url;
  }

  public static function getPageByContentName($content): int {
    $query = "SELECT ".self::ID." FROM ".self::TABLE." WHERE UPPER(".self::CONTENT.") LIKE UPPER(?)";
    $res = DB::getResults($query, [$content]);
    return count($res) ? $res[0][self::ID] : 0;
  }

  /**
  *pred pokusom o návštevu neexistujúcej alebo nedostupnej stránky presmerovať inde
  */
  public static function evadeInvalidURL() {
    function redirect($val = PageLoader::SCHEDULE, $otherData=[]) {
      exit(header("Location: " . PageLoader::makeLink($val, $otherData)));
    }

    if (isset($_GET['page']) && $_GET['page']) {
      $res = DB::getResults("SELECT * FROM " . self::TABLE .  " WHERE " . self::ID . "=?", [$_GET['page']]);
      $err = ['reason'=>''];
      // kontrola dostupnosti stránky
      if (count($res) === 0) $err['reason'] = self::NOT_EXIST;
      else {
        $isLoggedIn = SessionManager::hasLoggedInUser();
        $sign = $isLoggedIn ? self::LOGGED_IN : self::LOGGED_OUT;
        if ($res[0][$sign] != 1) $err['reason'] = $isLoggedIn ? self::LOGGED_OUT_ONLY : self::LOGGED_IN_ONLY;
      }
      if ($err['reason']) redirect(self::ERROR, $err);

      // stránka dostupná. má správne dáta?
      $content = $res[0][self::CONTENT];

      if ($content == self::LOG_IN) {
        $action = @$_GET['action'];
        if ($action != FormKeys::SIGN_IN_FORM
          && $action != FormKeys::SIGN_UP_FORM
          && $action != FormKeys::FORGOT_PASSWORD) {
          redirect(self::LOG_IN, ['action'=>FormKeys::SIGN_IN_FORM]);
        }
      }

      else if ($content == self::ACCOUNT) {
        $nick = @$_GET['user'];
        // ak som sa dostal az sem, uzivatel by mal byt už prihlásený
        if (!$nick) redirect(self::ACCOUNT, ['user'=>SessionManager::getUserNick()]);
        // existuje spomínaný užívateľ?
        elseif (!UserManager::getUserByNameOrEmail($nick)) redirect(self::ERROR, ['reason'=>NOT_EXIST]);
      }

      else if ($content == self::ERROR) {
        $reason = @$_GET['reason'];
        if ($reason != self::NOT_EXIST && $reason != self::LOGGED_IN_ONLY && $reason != self::LOGGED_OUT_ONLY)
          redirect(self::ERROR, ['reason'=>self::NOT_EXIST]);
      }

      else if ($content == self::GALLERY) {
        // galéria
      }

      else if ($content == self::SCHEDULE) {
        // nastavenia rozvrhu a dizajnu
      }

      else if ($content == self::NOTES) {
        // notes
      }
    }
    else redirect();
  }

  public static function getValidMenuPages() {
    $col = (SessionManager::hasLoggedInUser()) ? self::LOGGED_IN : self::LOGGED_OUT;
    $query = "SELECT * FROM " . self::TABLE . " WHERE " . self::IN_MENU . "=1 AND $col=1";
    return DB::workWithResults($query, [], function($row) {
      return new PageLoader(
        $row[self::ID], $row[self::TITLE], $row[self::CSS],
        $row[self::IN_MENU], $row[self::CONTENT], $row[self::ICON]
      );
    });
  }

  public static function getPageById($id) {
    $query = "SELECT * FROM " . self::TABLE . " WHERE " . self::ID . "=?";
    return @DB::workWithResults($query, [$id], function($row) {
      return new PageLoader(
        $row[self::ID], $row[self::TITLE], $row[self::CSS],
        $row[self::IN_MENU], $row[self::CONTENT], $row[self::ICON]
      );
    })[0];
  }

  public function __construct($pageID, $title, $css, $inMenu, $content, $icon) {
    $this->id = $pageID;
    $this->title = $title;
    $this->css = $css;
    $this->inMenu = $inMenu;
    $this->content = $content;
    $this->icon = $icon;
  }

  public function getId () {
    return $this->id;
  }
  public function getTitle () {
    return $this->title;
  }
  public function getCss () {
    return $this->css;
  }
  public function getInMenu () {
    return $this->inMenu;
  }
  public function getContent () {
    return $this->content;
  }
  public function getIcon () {
    return $this->icon;
  }

  public function hasCss() {
    return !!$this->css;
  }
  public function isInMenu() {
    return !!$this->inMenu;
  }
  public function hasIcon() {
    return !!$this->icon;
  }
  public function equals($page) {
    return $page->id == $this->id;
  }
}
