<?php
namespace Model;

use Model\DB;
use Model\SessionManager;
use Model\SubjectManager as Sub;
use \DateTime;

class NoteManager {
    private const MSG = "msg";
    const TABLE = "notes";
    const ID = "id";
    const DATE = "deadline";
    const OUT_DATE = "date";
    const SUB = "subject";
    const INFO = "info";

    private static function parseNote($row) {
      return [
        self::ID => (int)$row[self::ID],
        self::OUT_DATE => $row[self::DATE] ? (new DateTime($row[self::DATE]))->format("Y-m-d H:i") : "",
        self::SUB => Sub::makeSubject((int)$row[self::SUB], $row[Sub::ABB], $row[Sub::NAME]),
        self::INFO => $row[self::INFO]
      ];
    }

    private static function validateDateTime($date) {
      $y = "[0-9]+";
      $m = "((0?[1-9])|(1[0-2]))";
      $d = "((0?[1-9])|([1-2][0-9])|(3[0-1]))";
      $h = "((0?[0-9])|(1[0-9])|(2[0-4]))";
      $i = "((0?)|([1-5]))[0-9]";
      $format = "Y-m-d H:i";
      if (!is_string($date)) return "";
      if (preg_match("/^$y-$m-$d $h:$i\$/", $date)) return (new DateTime($date))->format($format);
      if (preg_match("/^$y-$m-$d\[ ]?\$/", $date)) return (new DateTime($date . " 22:00"))->format($format);
      if (preg_match("/^[ ]?$h:$i\$/", $date)) {
        $d = new DateTime($date);
        if ($d->format('U') - (new DateTime('NOW'))->format('U') <= 0) $d->add(new DateInterval("P1D"));
        return $d->format($format);
      }
      return "";
    }

    static function getNotes($startIndex, $count, $category) {
      $count = max(0, (int) $count);
      $startIndex = max(0, (int) $startIndex);
      $userId = SessionManager::getUserId();
      if ($userId === -1) return [];
      $query = "SELECT n." . self::ID . ", n." . self::DATE . ", n." . self::SUB
      . ", s." . Sub::ABB . ", s." . Sub::NAME . ", n.". self::INFO . " FROM "
      . self::TABLE . " n JOIN " . Sub::TABLE . " s ON n." . self::SUB . " = s."
      . Sub::ID . " WHERE s." . Sub::USER . " = ?";
      if ($category == "late-notes") $query .= " AND n." . self::DATE . " < NOW()";
      elseif ($category == "recent") $query .= " AND n." . self::DATE . " BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 1 WEEK)";
      elseif ($category == "long-term") $query .= " AND n." . self:: DATE . " IS NULL";
      elseif ($category != "all-notes") $query .= " AND UPPER(s." . Sub::ABB . ") LIKE UPPER(?)";
      $query .= " ORDER BY CASE WHEN n." . self::DATE . " IS NULL THEN 1 ELSE 0 END";
      $query .= ', n.' . self::DATE . ', UPPER(s.' . Sub::ABB . '), UPPER(n.';
      $query .= self::INFO . ") LIMIT $startIndex, $count";
      $args = preg_match_all("/\?/", $query) === 1 ? [$userId] : [$userId, $category];
      $list = DB::workWithResults($query, $args, function($row){
        return self::parseNote($row);
      });
      return ['offset' => $startIndex, 'list' => $list];
    }

    static function addNote($date, $sub, $info) {
      if (!is_string($date) || !is_string($sub) || !is_string($info)) return [self::MSG => "Neplatný vstup!"];
      $date = self::validateDateTime($date);
      $subject = Sub::getSubjectByAbb($sub);
      if (!$subject) return [self::MSG => "Predmet $sub neexistuje!"];
      $info = trim("$info");
      if (!$info) return [self::MSG => "Úlohe chýba popis!"];
      if (mb_strlen($info) > 255) return [self::MSG => "Popis úlohy je príliš dlhý!"];
      $id = DB::insertRecord(self::TABLE, [self::DATE => $date, self::SUB => $subject[Sub::ID], self::INFO => $info]);
      return [
        'note' => [
          self::ID => (int)$id,
          self::OUT_DATE => $date,
          self::SUB => $subject,
          self::INFO => $info
        ]
      ];
    }

    static function editNote($id, $date, $sub, $info) {
      if ($id && (int)$id != $id || !is_string($date) || !is_string($sub) || !is_string($info))
        return [self::MSG => "Neplatný vstup!"];
      $date = self::validateDateTime($date);
      $subject = Sub::getSubjectByAbb($sub);
      if (!$subject) return [self::MSG => "Predmet $sub neexistuje!"];
      $info = trim("$info");
      if (!$info) return [self::MSG => "Úlohe chýba popis!"];
      if (mb_strlen($info) > 255) return [self::MSG => "Popis úlohy je príliš dlhý!"];
      $input = [self::DATE => $date, self::SUB => $subject[Sub::ID], self::INFO => $info];
      $count = DB::updateRecords(self::TABLE, $input, self::ID . " = ?", [$id]);
      if ($count === 0) return [self::MSG => "Zmena úlohy skončila neúspešne!"];
      if ($count !== 1) return [self::MSG => "Došlo k zmene nadmerného množstva úloh: $count!"];
      return [
        'note' => [
          self::ID => (int)$id,
          self::OUT_DATE => $date,
          self::SUB => $subject,
          self::INFO => $info
        ]
      ];
    }

    static function removeNote($id) {
      $count = DB::removeRecords(self::TABLE, self::ID . " = ?", [$id]);
      if ($count === 0) return [self::MSG => "Odstránenie úlohy skončilo neúspešne!"];
      if ($count !== 1) return [self::MSG => "Bolo odstránené neočakávané množstvo úloh: $count!"];
      return ['note' => [ self::ID => (int)$id ]];
    }

    static function countLateNotes() {
      $sql = "SELECT COUNT(*) AS c FROM " . self::TABLE . " n JOIN " . Sub::TABLE
      . " s ON n." . self::SUB . " = s." . Sub::ID . " WHERE n." . self::DATE
      . " IS NOT NULL AND n." . self::DATE . " <= NOW() AND s." . Sub::USER . " = ?";
      return (int)DB::getResults($sql, [SessionManager::getUserId()])[0]['c'];
    }

    static function countCurrentNotes() {
      $sql = "SELECT COUNT(*) AS c FROM " . self::TABLE . " n JOIN " . Sub::TABLE
      . " s ON n." . self::SUB . " = s." . Sub::ID . " WHERE n." . self::DATE
      . " IS NOT NULL AND n." . self::DATE . " BETWEEN NOW() AND DATE_ADD(NOW(),"
      . "INTERVAL 1 WEEK) AND s." . Sub::USER . " = ?";
      return (int)DB::getResults($sql, [SessionManager::getUserId()])[0]['c'];
    }

    static function countNotes() {
      $sql = "SELECT COUNT(*) AS c FROM " . self::TABLE . " n JOIN " . Sub::TABLE
      . " s ON n." . self::SUB . " = s." . Sub::ID . " WHERE s." . Sub::USER . " = ?";
      return (int)DB::getResults($sql, [SessionManager::getUserId()])[0]['c'];
    }
}
