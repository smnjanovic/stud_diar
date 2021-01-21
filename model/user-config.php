<?php
namespace Model;

use Model\SessionManager;
use Model\UserManager;

class UserConfig {
  const CONFIG_FILE = "conf.json";

  const COLORS = "colors";
  const PROFILE_PICTURE = "profilePicture";
  const IMG_URL = "imageUrl";
  const IMG_ID = "imageId";
  const ASP_RAT = "aspectRatio";
  const IMG_FIT = "imageFit";
  const IMG_POS = "imagePos";
  const TBL_POS = "tablePos";

  const COL_B = "background";
  const COL_H = "heading";
  const COL_C = "courses";
  const COL_L = "lectures";
  const COL_F = "free";

  const COVER = "cover";
  const CONTAIN = "contain";
  const FILL = "fill";

  public static function get_config() {
    $id = SessionManager::getUserId();
    if ($id === -1) return null;
    $user = UserManager::getUserById($id);
    return $user ? new UserConfig($user->getDirectoryPath()) : null;
  }

  private $directory;
  private $file;
  private $data;

  private function __construct($path) {
    $this->directory = $path;
    $this->file = "$path/" . self::CONFIG_FILE;
    if (!is_dir(UserManager::DIRECTORY)) mkdir(UserManager::DIRECTORY, 0777);
    if (!is_dir($this->directory)) mkdir($this->directory, 0777);
    $this->data = [
      self::PROFILE_PICTURE => null,
      self::COLORS => [
        self::COL_B => [255, 255, 255, 100],
        self::COL_H => [0, 65, 50, 100],
        self::COL_C => [0, 45, 60, 100],
        self::COL_L => [0, 45, 75, 100],
        self::COL_F => [0, 0, 0, 35]
      ],
      self::IMG_URL => "",
      self::IMG_ID => null,
      self::ASP_RAT => [720, 1280],
      self::IMG_FIT => self::COVER,
      self::IMG_POS => 50,
      self::TBL_POS => 50
    ];
    if (is_file($this->file)) {
      try {
        $data = json_decode(file_get_contents($this->file), TRUE);
        foreach ($data as $key => $value)
          $this->data[$key] = $value;
      }
      catch (\Exception $ex) {}
    }
  }

  public function setColor($key, $hsla) {
    if (
      ($key === self::COL_B || $key === self::COL_H || $key === self::COL_L
      || $key === self::COL_C || $key === self::COL_F) && is_array($hsla) && count($hsla) === 4
    ) {
      foreach($hsla as $index => $col) {
        if (!is_int($col) || $col < 0 || $col > ($index === 0 ? 359 : 100)) return;
      }
      $this->data[self::COLORS][$key] = $hsla;
    }
  }

  public function setImageUrl($url) {
    $this->data[self::IMG_URL] = is_string($url) ? $url : '';
  }

  public function setImage($id) {
    $this->data[self::IMG_ID] = is_int($id) ? $id : null;
  }

  public function setAspectRatio($width, $height) {
    if (is_int($width) && is_int($height) && $width >= 320 && $height >= 320
      && $width <= 6016 && $height <= 6016) {
      $this->data[self::ASP_RAT] = [$width, $height];
    }
  }

  public function setImageFit($fit) {
    $this->data[self::IMG_FIT] = $fit === self::CONTAIN || $fit === self::FILL ? $fit : self::COVER;
  }

  public function setImagePos($pos) {
    if (is_int($pos) && $pos >= 0 && $pos <= 100) {
      $this->data[self::IMG_POS] = $pos;
    }
  }

  public function setTablePos($pos) {
    if (is_int($pos) && $pos >= 0 && $pos <= 100) {
      $this->data[self::TBL_POS] = $pos;
    }
  }

  public function setProfilePicture($id) {
    if (is_int($id) && $id > -1) {
      // kontrola existencie - neskor
      $this->data[self::PROFILE_PICTURE] = $id;
    }
  }

  public function loadScheduleDesign() {
    return json_encode([
      self::COLORS => $this->data[self::COLORS],
      self::IMG_URL => $this->getImageUrl(),
      self::ASP_RAT => $this->data[self::ASP_RAT],
      self::IMG_FIT => $this->data[self::IMG_FIT],
      self::IMG_POS => $this->data[self::IMG_POS],
      self::TBL_POS => $this->data[self::TBL_POS]
    ]);
  }

  public function getImageUrl() {
    // prednost ma id - vlastny obrazok - dokoncim po galerii
    return $this->data[self::IMG_URL];
  }

  public function save() {
    file_put_contents($this->file, json_encode($this->data));
  }
}
