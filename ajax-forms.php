<?php
namespace MAIN;

require_once "model/form-keys.php";
require_once "model/db.php";
require_once "model/user.php";
require_once "model/session-manager.php";
require_once "model/page-loader.php";
require_once "model/user-manager.php";
require_once "model/subject-manager.php";
require_once "model/note-manager.php";
require_once "model/schedule-manager.php";
require_once "model/user-config.php";

use Model\DB;
use Model\PageLoader;
use Model\User;
use Model\SessionManager;
use Model\UserManager;
use Model\FormKeys as Form;
use Model\SubjectManager;
use Model\NoteManager;
use Model\ScheduleManager;
use Model\UserConfig;

DB::connect();
session_start();

header('Content-Type: text/html; charset=utf-8');

function json($data) {
  die(json_encode($data));
}

if ($_POST) {
  if (isset($_POST['get-page'])) {
    die(PageLoader::getPageByContentName($_POST['get-page']).'');
  }

  if (isset($_POST['get-user'])) {
    die(SessionManager::getUserId());
  }

  // registrácia
  else if (isset($_POST[Form::SIGN_UP_FORM]))
    json(UserManager::registerUser(@$_POST[Form::NICK],
      @$_POST[Form::EMAIL], @$_POST[Form::PASS], @$_POST[Form::PASS2]));

  // prihlasenie
  elseif (isset($_POST[Form::SIGN_IN_FORM])) {
    json(UserManager::loginUser(@$_POST[Form::NICK], @$_POST[Form::PASS]));
  }

  // zmeny údajov konta
  elseif (isset($_POST[Form::MODIFY_ACCOUNT])) {
    if (isset($_POST[Form::NICK])) json(UserManager::changeNick(@$_POST[Form::USER_ID], $_POST[Form::NICK]));
    elseif (isset($_POST[Form::EMAIL])) json(UserManager::changeEmail(@$_POST[Form::USER_ID], $_POST[Form::EMAIL]));
  }

  //odhlásenie - tu sa nejedná o ajax volanie - dôjde k presmerovaniu sem
  elseif (isset($_POST[Form::SIGN_OUT_FORM])) {
    SessionManager::logout();
    die(PageLoader::makeLink(PageLoader::LOG_IN, ["action" => Form::SIGN_IN_FORM]));
  }

  // likvidacia konta
  elseif (isset($_POST[Form::ACCOUNT_REMOVAL])) {
    json(UserManager::removeAccount(@$_POST[Form::USER_ID]));
  }

  elseif (isset($_POST[Form::CHANGE_PASSWORD])) {
    json(UserManager::changePassword(
      @$_POST[Form::USER_ID],
      @$_POST[Form::OLD_PASS],
      @$_POST[Form::NEW_PASS1],
      @$_POST[Form::NEW_PASS2]
    ));
  }

  elseif (isset($_POST['load-subjects'])) {
    json(SubjectManager::getSubjects(@$_POST['start-index'], @$_POST['item-count']));
  }

  elseif (isset($_POST['search-subjects'])) {
    json(SubjectManager::searchSubjects(@$_POST['q'], @$_POST['max-result-count']));
  }

  elseif (isset($_POST['insert-subject'])) {
    json(SubjectManager::addSubject(@$_POST[Form::SUB_ABB], @$_POST[Form::SUB_NAME]));
  }

  elseif (isset($_POST['update-subject'])) {
    json(SubjectManager::editSubject(@$_POST[Form::SUB_ID], @$_POST[Form::SUB_ABB], @$_POST[Form::SUB_NAME]));
  }

  elseif (isset($_POST['delete-subject'])) {
    json(SubjectManager::removeSubject(@$_POST[Form::SUB_ID]));
  }

  elseif (isset($_POST['load-notes'])) {
    json(NoteManager::getNotes(@$_POST['start'], @$_POST['count'], @$_POST['category']));
  }

  elseif (isset($_POST['add-note'])) {
    json(NoteManager::addNote(@$_POST['date'], @$_POST['subject'], @$_POST['info']));
  }

  elseif (isset($_POST['edit-note'])) {
    json(NoteManager::editNote(@$_POST['id'], @$_POST['date'], @$_POST['subject'], @$_POST['info']));
  }

  elseif (isset($_POST['remove-note'])) {
    json(NoteManager::removeNote(@$_POST['id']));
  }

  else if (isset($_POST['get_lessons'])) {
    json(ScheduleManager::getLessons());
  }

  else if (isset($_POST['add_lesson'])) {
    $error = ScheduleManager::setLesson(@$_POST['day'], @$_POST['start'],
      @$_POST['dur'], @$_POST['type'], @$_POST['subject'], @$_POST['room']);
    json($error ? $error : ScheduleManager::getLessons());
  }

  else if (isset($_POST['clear_schedule'])) {
    $error = ScheduleManager::clearSchedule(@$_POST['day'], @$_POST['start'], @$_POST['dur']);
    json($error ? $error : ScheduleManager::getLessons());
  }

  else if (isset($_POST['set-conf']) && isset($_POST['value'])) {
    $conf = UserConfig::get_config();
    if ($conf) {
      $key = $_POST['set-conf'];
      $val = $_POST['value'];

      if ($key === 'store-color') {
        try {
          $arr = json_decode($val, TRUE);
          if (is_string($arr['key']) && is_array($arr['value']) && count($arr['value']) === 4) {
            $conf->setColor($arr['key'], $arr['value']);
          }
        }
        catch (Exception $exc) {
          die("Zlý vstup!");
        }
      }
      else if ($key === 'store-aspect-ratio') {
        try {
          $res = json_decode($val, TRUE);
          if (is_array($res) && isset($res[0]) && isset($res[1]))
            $conf->setAspectRatio((int)$res[0], (int)$res[1]);
        }
        catch (Exception $exc) {
          die("Zlý vstup!");
        }
      }
      else if ($key === 'store-image-url') $conf->setImageUrl($val);
      else if ($key === 'store-image-fit') $conf->setImageFit($val);
      else if ($key === 'store-image-pos') $conf->setImagePos((int)$val);
      else if ($key === 'store-table-pos') $conf->setTablePos((int)$val);
      $conf->save();
      die();
    }
    die("Užívateľ nie je prihlásený alebo neexistuje!");
  }

  else if (isset($_POST['get-conf'])) {
    $conf = UserConfig::get_config();
    die ($conf ? $conf->loadScheduleDesign() : json_encode(['msg' => 'Nie je prihlásený používateľ!']));
  }

  /*else if (isset($_POST['add-photo'])) {
    $gallery = Gallery::get_instance();
    if ($gallery) {
      json($gallery->uploadImage($_FILES['file']));
    }
    else {
      json([ 'msg' => "Musíte sa prihlásiť!"]);
    }
  }*/

  /*else if (isset($_POST['remove-photo'])) {
    $gallery = Gallery::get_instance();
    if ($gallery) json($gallery->removeImage($_FILES['file']));
    else json([ 'msg' => "Musíte sa prihlásiť!"]);
  }*/

  /*else if (isset($_POST['get-big-image']) && isset($_POST['size'])) {
    $gallery = Gallery::get_instance();
    if ($gallery) json(['url' => $gallery->getBigImagePath($_POST['get-big-image'], $_POST['size'])]);
    else json([ 'msg' => "Musíte sa prihlásiť!"]);
  }*/

  /*else if (isset($_POST['get-profile'])) {
    $gallery = Gallery::get_instance();
    if ($gallery) json(['url' => $gallery->getProfile($_POST['get-profile'])]);
    else json([ 'msg' => "Musíte sa prihlásiť!"]);
  }*/

  /*else if (isset($_POST['get-icon'])) {
    $gallery = Gallery::get_instance();
    if ($gallery) json(['url' => $gallery->getProfile($_POST['get-icon'])]);
    else json([ 'msg' => "Musíte sa prihlásiť!"]);
  }*/

  /*else if (isset($_POST['get-mini'])) {
    $gallery = Gallery::get_instance();
    if ($gallery) json(['url' => $gallery->getProfile($_POST['get-mini'])]);
    else json([ 'msg' => "Musíte sa prihlásiť!"]);
  }*/
}
