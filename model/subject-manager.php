<?php

namespace Model;

use Model\DB;
use Model\SessionManager;

class SubjectManager {
    private const MSG = "msg";
    const TABLE = "subjects";
    const ID = "id";
    const ABB = "abb";
    const NAME = "name";
    const USER = "user";

    static function validateAbb($abb) {
        if (is_string($abb)) {
        if (preg_match('/^[a-zA-ZÀ-ž0-9-_]{1,5}$/u', $abb)) return null;
            $len = mb_strlen($abb);
            return $len > 5 ? "Príliš dlhá skratka!" : $len < 1 ? "Vyplňte skratku!" : "Zlý formát skratky!";
        }
        return "Chýba vstup alebo sa nejedná o textový vstup!";
    }

    static function validateName($name) {
      if (is_string($name)) {
      if (preg_match('/^[a-zA-ZÀ-ž0-9-_ ]{1,48}$/u', $name)) return null;
        $len = mb_strlen($name);
        return $len > 48 ? "Príliš dlhý názov predmetu!"
        : $len < 1 ? "Vyplňte názov predmetu!"
        : "Zlý formát názvu predmetu!";
      }
      return "Chýba vstup alebo sa nejedná o textový vstup!";
    }

    static function makeSubject($id, $abb, $name) {
      return [self::ID => (int)$id, self::ABB => "$abb", self::NAME => "$name"];
    }

    static function parseSubject($row) {
      return self::makeSubject($row[self::ID], $row[self::ABB], $row[self::NAME]);
    }

    static function checkSubjectById($id) {
      $userId = SessionManager::getUserId();
      $query = 'SELECT ' . self::USER . ' FROM ' . self::TABLE . ' WHERE ' . self::ID . ' = ?';
      $result = DB::getResults($query, [$id]);
      if (count($result) !== 1) return "Predmet neexistuje!";
      if ((int)($result[0][self::USER]) !== $userId) return "Nedostatočné oprávnenia!";
      return null;
    }

    static function searchSubjects($q, $limit) {
      if (!$q) return [];
      $limit = (int)$limit;
      $query = 'SELECT ' . self::ID . ', ' . self::ABB . ', ' . self::NAME . ' FROM '
      . self::TABLE . ' WHERE UPPER(' . self::ABB . ') LIKE UPPER(?) OR UPPER('
      . self::NAME . ') LIKE UPPER (?) AND ' . self::USER . ' = ? ORDER BY CASE WHEN UPPER('
      . self::ABB . ') LIKE UPPER(?) THEN 0 ELSE 1 END LIMIT ' . $limit;
      $args = ["%$q%", "%$q%", SessionManager::getUserId(), "%$q%"];
      return DB::workWithResults($query, $args, function($row){ return self::parseSubject($row); });
    }

    static function getSubjectByAbb($abb) {
      $userId = SessionManager::getUserId();
      $query = 'SELECT ' . self::ID . ', ' . self::ABB . ', ' . self::NAME . ' FROM '
      . self::TABLE . ' WHERE UPPER(' . self::ABB . ') LIKE UPPER(?) AND ' . self::USER . ' = ?';
      $result = DB::workWithResults($query, [$abb, $userId], function($row) {
        return self::parseSubject($row);
      });
      return count($result) ? $result[0] : null;
    }

    static function getSubjectCount() {
      $query = "SELECT COUNT(*) AS c FROM " . self::TABLE . " WHERE " . self::USER . " =?";
      return DB::getResults($query, [SessionManager::getUserId()])[0]['c'];
    }

    static function getSubjects($startIndex, $count) {
      $count = max(0, (int) $count);
      $startIndex = max(0, (int) $startIndex);
      $userId = SessionManager::getUserId();
      if ($userId === -1) return [];
      $cols = join(", ", [self::ID, self::ABB, self::NAME]);
      // vstup je 100% validny
      $query = "SELECT $cols  FROM " . self::TABLE . " WHERE " . self::USER
        . " = $userId ORDER BY " . self::ABB . " LIMIT $startIndex, $count";
      return DB::workWithResults($query, [], function($row) {
        return self::parseSubject($row);
      });
    }

    static function addSubject($abb, $name) {
      $abb = mb_strtoupper(trim($abb));
      $name = mb_strtolower(trim($name));
      $name = mb_strtoupper(mb_substr($name, 0, 1)) . mb_substr($name, 1);

      // overenie prihlásenia
      $userId = SessionManager::getUserId();
      if ($userId === -1) return [self::MSG => "Musíte sa prihlásiť!"];

      // overenie skratky
      $abbErr = self::validateAbb($abb);
      if ($abbErr) return [self::MSG => $abbErr, "e" => $abb];

      // overenie názvu
      $nameErr = self::validateName($name);
      if ($nameErr) return [self::MSG => $nameErr];

      // overenie obsadenia skratky
      $query = "SELECT 1 FROM " . self::TABLE . " WHERE " . self::ABB . " LIKE ? AND " . self::USER . " = ?";
      if (count(DB::getResults($query, [$abb, $userId]))) return [self::MSG => "Predmet už existuje!"];

      $id = DB::insertRecord(self::TABLE, [self::ABB => $abb, self::NAME => $name, self::USER => $userId]);
      return ['subject' => self::makeSubject($id, $abb, $name)];
    }

    static function editSubject($id, $abb, $name) {
      $id = (int)$id;
      $abb = mb_strtoupper(trim($abb));
      $name = mb_strtolower(trim($name));
      $name = mb_strtoupper(mb_substr($name, 0, 1)) . mb_substr($name, 1);

      // overenie pristupu k predmetu
      $subErr = self::checkSubjectById($id);
      if ($subErr !== null) return [self::MSG => $subErr];

      // overenie formatu skratky
      $abbErr = self::validateAbb($abb);
      if ($abbErr) return [self::MSG => $abbErr];

      // overenie formatu nazvu
      $nameErr = self::validateName($name);
      if ($nameErr) return [self::MSG => $nameErr];

      $userId = SessionManager::getUserId();

      // kontrola obsadenia skratky
      $query = 'SELECT 1 FROM ' . self::TABLE . ' WHERE UPPER(' . self::ABB
        . ') LIKE UPPER(?) AND ' . self::USER . ' = ? AND ' . self::ID . ' != ?';
      if (count(DB::getResults($query, [$abb, SessionManager::getUserId(), $id])))
        return [self::MSG => "Skratka je už použitá!"];

      // vykonanie zmeny
      $updated = DB::updateRecords(self::TABLE, [self::ABB => $abb, self::NAME => $name], self::ID . ' = ?', [$id]);
      if ($updated === 0) return [self::MSG => "Nedošlo k žiadnym zmenám!"];
      if ($updated !== 1) return [self::MSG => "Došlo k neočakávanému počtu zmien: $updated!"];
      return ['subject' => self::makeSubject($id, $abb, $name)];
    }

    static function removeSubject($id) {
      // kontrola oprávnenia
      $subErr = self::checkSubjectById($id);
      if ($subErr) return [self::MSG => $subErr];

      // odstránenie
      $count = DB::removeRecords(self::TABLE, self::ID . " = ?", [$id]);

      if ($count === 0) return [self::MSG => "Odstránenie predmetu skončilo neúspešne!"];
      if ($count !== 1) return [self::MSG => "Bolo odstránené neočakávané množstvo predmetov: $count!"];
      return ['subject' => self::makeSubject((int)$id, '', '')];
    }
}
