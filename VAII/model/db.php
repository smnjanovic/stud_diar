<?php

class DB {
  private static $host = 'localhost';
  private static $db = 'stud_diar';
  private static $username = 'root';
  private static $password = '';

  private static $connection;

  public static function connect() {
    if (!self::$connection) {
      try{
        self::$connection = new PDO("mysql:host=" . self::$host . ";dbname=" . self::$db,  self::$username,  self::$password);

        if(!self::$connection) {
          die("Neúspešné pripojenie!");
        }
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
  * @return arr Pole výsledkov
  */
  public static function getResults($sql, $args=[]) {
    $stmt = self::$connection->prepare($sql);
    $stmt->execute($args);
    return $stmt->fetchAll();
  }

  public static function getLastInsertId() {
    return self::$connection->lastInsertId();
  }
}
