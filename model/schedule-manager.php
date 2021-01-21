<?php
namespace Model;

use Model\DB;
use Model\SessionManager;
use Model\SubjectManager as Sub;

class ScheduleManager {
  const MSG = "msg";
  //tabulka casoveho rozvrhu
  const SCHEDULE = "schedule";
  const SC_LES_ID = "les_id";
  const LES = "les_no";
  const DAY = "day";
  const USER = "user_id";

  //tabulka vyucovacich hodin
  const LESSONS = "lessons";
  const LES_ID = "id";
  const LES_TYPE = "lecture";
  const SUB = "subject";
  const ROOM = "room";

  private static function parseLesson($day, $start, $dur, $type, $sub, $room) {
    return [
      self::DAY => (int)$day,
      'start' => (int)$start,
      'dur' => (int)$dur,
      'type' => (int)$type,
      self::SUB => $sub,
      self::ROOM => "$room"
    ];
  }
  private static function scheduleCheck($day, $start, $dur) {
    if ($day < 1 && $day > 5) return "Neplatný deň ($day)!";
    if ($start < 1 && $start > 17) return "Začiatok hodiny mimo rozsahu [1..17] ($start)!";
    if ($dur < 1 && $dur > 17) return "Trvanie hodiny mimo rozsahu [1..17] ($dur)!";
    if ($start + $dur > 18) return "Koniec hodiny prekročil rozsah [2..18] ($start + $dur)!";
    return null;
  }
  private static function cleanUpLessons() {
    $sub = 'SELECT ' . Sub::ID . ' FROM ' . Sub::TABLE . ' WHERE ' . Sub::USER . ' = ?';
    $sched = 'SELECT ' . self::SC_LES_ID . ' FROM ' . self::SCHEDULE . ' WHERE '
    . self::USER . ' = ? GROUP BY ' . self::SC_LES_ID;
    $query = self::SUB . " IN ($sub) AND " . self::LES_ID . " NOT IN ($sched)";
    $user = SessionManager::getUserId();
    return DB::removeRecords(self::LESSONS, $query, [$user, $user]);
  }

  static function getLessons() {
    $cmpQuery = 'SELECT ' . self::DAY . ', ' . self::LES . ', ' . self::SC_LES_ID . ' FROM ' . self::SCHEDULE;
    $startEnd = 'SELECT l1.' . self::USER . ', l1.' . self::DAY . ', l1.' . self::LES
    . ' AS start, l2.' . self::LES . ' AS end, l1.' . self::SC_LES_ID . ' FROM '
    . self::SCHEDULE.' l1 JOIN ' . self::SCHEDULE.' l2 ON l1.' . self::SC_LES_ID
    . ' = l2.' . self::SC_LES_ID . ' AND l1.' . self::DAY . ' = l2.' . self::DAY
    . ' WHERE (l1.' . self::DAY . ', l1.' . self::LES . ' - 1, l1.' . self::SC_LES_ID
    . ') NOT IN (' . $cmpQuery . ') AND (l2.' . self::DAY . ', l2.' . self::LES
    . ' + 1, l2.' . self::SC_LES_ID . ') NOT IN (' . $cmpQuery . ') AND l2.'
    . self::LES . ' >= l1.' . self::LES;

    $query = 'SELECT sc.' . self::USER . ', sc.' . self::DAY
    . ', sc.start, MIN(end) - sc.start + 1 AS duration, sc.' . self::SC_LES_ID
    . ', le.' . self::LES_TYPE . ', le.' . self::SUB . ', su.' . Sub::ABB . ', su.'
    . Sub::NAME . ', le.' . self::ROOM . ' FROM (' . $startEnd . ') AS sc JOIN '
    . self::LESSONS . ' le ON sc.' . self::SC_LES_ID . ' = le.' . self::LES_ID
    . ' JOIN ' . Sub::TABLE . ' su ON le.' . self::SUB . ' = su.' . Sub::ID
    . ' AND sc.' . self::USER . ' = su.' . Sub::USER . ' WHERE sc.' . self::USER
    . ' = ? GROUP BY (sc.start) ORDER BY sc.' . self::DAY . ', sc.start';

    return ['lessons' => DB::workWithResults($query, [SessionManager::getUserId()], function($row) {
      $sub = Sub::makeSubject($row[self::SUB], $row[Sub::ABB], $row[Sub::NAME]);
      return self::parseLesson($row[self::DAY], $row['start'], $row['duration'],
      $row[self::LES_TYPE], $sub, $row[self::ROOM]);
    })];
  }
  static function clearSchedule($day, $start, $dur, $cleanUp = true) {
    $err = self::scheduleCheck($day, $start, $dur);
    if ($err) return [self::MSG => $err];
    $cond = self::USER . ' = ? AND ' . self::DAY . ' = ? AND ' . self::LES . ' BETWEEN ? AND ?';
    $args = [SessionManager::getUserId(), $day, $start, $start + $dur - 1];
    $count = DB::removeRecords(self::SCHEDULE, $cond, $args);
    if ($cleanUp) self::cleanUpLessons();
    return null;
  }
  static function setLesson($day, $start, $dur, $type, $sub, $room) {
    $day = (int)$day;
    $start = (int)$start;
    $dur = (int)$dur;
    $type = (int)$type;
    $sub = "$sub";
    $room = "$room";
    $me = SessionManager::getUserId();
    if ($me == -1) return [self::MSG => "Len registrovaný užívateľ môže pridávať hodiny!"];

    $err = self::scheduleCheck($day, $start, $dur);
    if ($err) return [self::MSG => $err];

    if ($type != 0 && $type != 1) return [self::MSG => "Zadaný typ vyučovania neexistuje!"];

    if (mb_strlen($room) > 12) return [self::MSG => "Popis miestnosti je príliš dlhý!"];

    // ziskat ID predmetu
    $subject = SubjectManager::getSubjectByAbb($sub);
    if (!$subject) return [self::MSG => "Predmet neexistuje!"];

    //uvolnenie hodin
    self::clearSchedule($day, $start, $dur, false);

    //ziskanie alebo vytvorenie hodiny
    $query = 'SELECT ' . self::LES_ID . ' FROM ' . self::LESSONS . ' WHERE '
    . self::LES_TYPE . ' = ? AND ' . self::SUB . ' = ? AND ' . self::ROOM . ' LIKE ?';
    $result = DB::getResults($query, [$type, $subject[Sub::ID], $room]);
    $lesson = count($result) > 0 ? $result[0][self::LES_ID] : DB::insertRecord(
    self::LESSONS, [self::LES_TYPE => $type, self::SUB => $subject[Sub::ID],
    self::ROOM => $room]);
    if (!$lesson || $lesson == -1) return [self::MSG => "Hodina nie je k dispozícii!"];

    //zapis hodiny do rozvrhu
    $records = [];
    for ($i = $start; $i < $start + $dur; $i++)
    $records[] = [self::SC_LES_ID => $lesson, self::DAY => $day, self::LES => $i, self::USER => $me];

    DB::insertRecords(self::SCHEDULE, $records);

    // odstranenie nadbytocnych hodin
    self::cleanUpLessons();
    return null;
  }
}
