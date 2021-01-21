<?php
namespace Model;

use \PDO;

class DB {
  private const HOST = 'localhost';
  private const DB = 'stud_diar';
  private const USERNAME = 'root';
  private const PASSWORD = '';
  private static $connection;

  public static function connect() {
    if (!self::$connection) {
      try{
        self::$connection = new PDO("mysql:host=" . self::HOST . ";dbname=" . self::DB,  self::USERNAME,  self::PASSWORD);
        if(!self::$connection) die("Neúspešné pripojenie!");
        self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      }
      catch (PDOException $e){
        die ($e->getMessage());
      }
    }
  }

  /**
  * @param sql SQL výraz na vyhodnotenie
  * @param args pole hodnôt
  */
  public static function execQuery($sql, $args) {
    self::$connection->prepare($sql)->execute($args);
  }

  /**
  * @param sql SQL výraz
  * @return array Pole výsledkov
  */
  public static function getResults($sql, $args=[]) {
    $stmt = self::$connection->prepare($sql);
    $stmt->execute($args);
    return $stmt->fetchAll();
  }

  public static function workWithResults($sql, $args, $job) {
    if (is_callable($job)) {
      $stmt = self::$connection->prepare($sql);
      $stmt->execute($args);
      $data=[];
      while($row = $stmt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
        $data[] = $job($row);
      return $data;
    }
    return [];
  }

  public static function insertRecord($table, $data) {
    $keys = join(", ", array_keys($data));
    $marks = substr(str_repeat(", ?", count($data)), 2);
    $sql = "INSERT INTO $table ($keys) VALUES ($marks)";
    self::$connection->prepare($sql)->execute(array_values($data));
    return (int)self::$connection->lastInsertId();
  }

  public static function insertRecords($table, $data) {
    $keys = join(", ", array_keys($data[0]));
    $marks = substr(str_repeat(", ?", count($data[0])), 2);
    $insertion = substr(str_repeat(", ($marks)", count($data)), 2);
    $sql = "INSERT INTO $table ($keys) VALUES $insertion";

    foreach ($data as $row) {
      foreach ($row as $value) {
        $args[] = $value;
      }
    }
    self::$connection->prepare($sql)->execute($args);
  }

  public static function updateRecords($table, $data, $where = null, $args = []) {
    $set = "";
    $values = [];
    foreach ($data as $col => $val) {
      if ($set) $set .= ", ";
      $set .= "$col = ?";
      $values[] = $val;
    }
    $cond = $where !== null && is_string($where);
    $sql = $cond ? "UPDATE $table SET $set WHERE $where" : "UPDATE $table SET $set";
    $args = array_merge($values, $args);
    $ready = self::$connection->prepare($sql);
    $ready->execute($args);
    return $ready->rowCount();
  }

  public static function removeRecords($table, $where, $args) {
    $ready = self::$connection->prepare("DELETE FROM $table WHERE $where");
    $ready->execute($args);
    return $ready->rowCount();
  }

  public static function transaction($callback) {
    if (is_callable($callback)) {
      try {
        $connection->beginTransaction();
        $callback($connection);
        $connection->commit();
      }
      catch(PDOException $exc) {
        $connection.rollBack();
        throw $exc;
      }
    }
  }

  public static function getLastInsertId() {
    return self::$connection->lastInsertId();
  }
}
