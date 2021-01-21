<?php
namespace Model;

use Model\SessionManager;
use Model\UserManager;
use Model\PageLoader;

class Gallery {
  const FILE = "gallery.json";
  const DIR = "gallery";
  const MSG = 'msg';

  const LARGE = 2560;
  const BIG = 1960;
  const MID = 1280;
  const SMALL = 768;
  const ICON = 250;
  const PROFILE = 200;
  const MINI = 40;

  public static function detectIdFromUrl($url, $userId) {
    if (preg_match("/^.*\/img[0-9]+-[0-9]+\.jpeg$/i", $url)) {
      $id = (int)preg_replace("/^.*\/img([0-9]+)-[0-9]+\.jpeg$/i", "", $url);
      $size = (int)preg_replace("/^.*\/img[0-9]+-([0-9]+)\.jpeg$/i", "", $url);
      if (
        $size === self::LARGE || $size === self::BIG
        || $size === self::MID || $size === self::SMALL
        || $size === self::ICON || $size === self::PROFILE
        || $size === self::MINI
      ) {
        $url2 = PageLoader::DOMAIN . '/' . UserManager::DIRECTORY . "/$userId/" . self::DIR . "/img$userId-$size.jpeg";
        if ($url2 != $url) return null;
        else return $id;
      }
      else return false;
    }
    else return -1;
  }

  public static function get_instance() {
    $id = SessionManager::getUserId();
    if ($id === -1) return null;
    $user = UserManager::getUserById($id);
    return $user ? new Gallery($user) : null;
  }

  private $dir;
  private $file;
  private $data;

  private function __construct($user) {
    $user_dir = $user->getDirectoryPath();
    $this->dir = "$user_dir/" . self::DIR;
    $this->file = "$user_dir/" . self::FILE;
    if (!is_dir(UserManager::DIRECTORY)) mkdir(UserManager::DIRECTORY, 0777);
    if (!is_dir($user_dir)) mkdir($user_dir, 0777);
    if (!is_dir($this->dir)) mkdir($this->dir, 0777);
    if (is_file($this->file)) {
      try {
        $this->data = json_decode(file_get_contents($this->file), TRUE);
      } catch (\Exception $e) {
        $this->data = [];
      }
    }
    else {
      $this->data = [];
    }
  }

  private function getNewId() {
    $c = count($this->data);
    if ($c === 0) return 1;
    return $this->data[$c - 1];
  }

  private function findId($p_id) {
    if (!is_int($p_id)) return -1;
    $c = count($this->data);
    if ($c === 0) return -1;
    $first = $this->data[0];
    $last = $this->data[$c - 1];
    if ($last < $p_id || $p_id < $first || $p_id - (int)$p_id !== 0) return -1;
    // ak je posledne id mensie ako hladane, tak je jasne ze neexistuje
    if ($last === $first) return 0;
    $start = abs($first - $p_id) < abs($last - $p_id) ? 0 : $c - 1;
    if ($start === 0) {
      for ($i = $start; $i < $c; $i++)
        if ($this->data[$i] === $p_id)
          return $i;
    }
    else {
      for ($i = $start; $i >= 0; $i--)
        if ($this->data[$i] === $p_id)
          return $i;
    }
    return -1;
  }

  public function getBigImagePath($id, $size) {
    $s = 0;
    if ($size >= self::LARGE) $s = self::LARGE;
    else if ($size >= self::BIG) $s = self::BIG;
    else if ($size >= self::MID) $s = self::MID;
    else $s = self::SMALL;
    return "{$this->dir}/img$id-$size.jpeg";
  }

  public function getMini($id) {
    return "{$this->dir}/img$id-" . self::MINI . ".jpeg";
  }

  public function getIcon($id) {
    return "{$this->dir}/img$id-" . self::ICON . ".jpeg";
  }

  public function getProfile($id) {
    return "{$this->dir}/img$id-" . self::PROFILE . ".jpeg";
  }

  public function uploadImage($file) {
    $size = getimagesize($file["tmp_name"]);
    if ($size === false) return [self::MSG => "Súbor nie je obrázok!"];
    if (!preg_match("/image\/(jpe?g)|(png)|(gif)|(bmp)/i", $file['type']))
      return [self::MSG => "Obrázok musí byť typu: jpg, jpeg, png, gif alebo bmp!"];
    if ($file["size"] > 15 * 1024 * 1024) return [self::MSG => "Obrázok je príliš veľký!"];

    $orig_ext = preg_replace("/image\//", "", $file['type']);
    //vlozit id vopred
    $id = $this->getNewId();
    array_push($this->data, $id);
    $this->save();

    $name = "{$this->dir}/img$id";
    $ext = "jpeg";
    $tmp = "{$this->dir}/img$id-tmp.$orig_ext";
    $large = "{$this->dir}/img$id-" . self::LARGE . ".$ext";
    $big = "{$this->dir}/img$id-" . self::BIG . ".$ext";
    $mid = "{$this->dir}/img$id-" . self::MID . ".$ext";
    $small = "{$this->dir}/img$id-" . self::SMALL . ".$ext";
    $icon = "{$this->dir}/img$id-" . self::ICON . ".$ext";
    $profile = "{$this->dir}/img$id-" . self::PROFILE . ".$ext";
    $mini = "{$this->dir}/img$id-" . self::MINI . ".$ext";

    // táto podmienka by nemala nikdy platiť
    if (
      is_file($tmp) || is_file($large) || is_file($big) || is_file($mid)
      || is_file($small) || is_file($mini) || is_file($profile) || is_file($icon)
    ) return [self::MSG => "Obrázok sa nepodarilo vložiť!"];

    move_uploaded_file($file['tmp_name'], $tmp);

    $this->createResizedImage($tmp, $orig_ext, self::LARGE, $large);
    $this->createResizedImage($tmp, $orig_ext, self::BIG, $big);
    $this->createResizedImage($tmp, $orig_ext, self::MID, $mid);
    $this->createResizedImage($tmp, $orig_ext, self::SMALL, $small);
    $this->createCroppedImage($tmp, $orig_ext, self::ICON, ((int)((self::ICON * 2 / 3) / 5)) * 5, $icon);
    $this->createCroppedImage($tmp, $orig_ext, self::PROFILE, self::PROFILE, $profile);
    $this->createCroppedImage($tmp, $orig_ext, self::MINI, self::MINI, $mini);
    unlink($tmp);
    return ["id" => $id];
  }

  private function createResizedImage($path, $ext, $maxSize, $destPath) {
    $source = null;
    if (preg_match("/jpe?g/i", $ext)) $source = imagecreatefromjpeg($path);
    if ($ext === "png") $source = imagecreatefrompng($path);
    if ($ext === "gif") $source = imagecreatefromgif($path);
    if ($ext === "bmp") $source = imagecreatefrombmp($path);
    if (!$source) return;

    list($width, $height) = getimagesize($path);
    $max = max($width, $height);
    $koeficient = $max <= $maxSize ? 1.0 : $maxSize / $max;
    $new_w = (int)($width * $koeficient);
    $new_h = (int)($height * $koeficient);
    $output = imagecreatetruecolor($new_w, $new_h);

    // Resize the source image to new size
    imagecopyresized($output, $source, 0, 0, 0, 0, $new_w, $new_h, $width, $height);
    imagejpeg($output, $destPath, 98);
  }

  private function createCroppedImage($srcPath, $ext, $dst_w, $dst_h, $dstPath) {
    $source = null;
    if (preg_match("/jpe?g/i", $ext)) $source = imagecreatefromjpeg($srcPath);
    if ($ext === "png") $source = imagecreatefrompng($srcPath);
    if ($ext === "gif") $source = imagecreatefromgif($srcPath);
    if ($ext === "bmp") $source = imagecreatefrombmp($srcPath);
    if (!$source) return;
    $output = imagecreatetruecolor($dst_w, $dst_h);

    list($width, $height) = getimagesize($srcPath);
    $koeficient = max($dst_w / $width, $dst_h / $height);
    $resized_w = $width * $koeficient;
    $resized_h = $height * $koeficient;

    $src_x = round(($resized_w - $dst_w) / $koeficient);
    $src_y = round(($resized_h - $dst_h) / $koeficient);
    $src_w = $width / $resized_w * $dst_w;
    $src_h = $height / $resized_h * $dst_h;

    // Resize the source image to new size
    imagecopyresized($output, $source, 0, 0, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
    imagejpeg($output, $dstPath, 98);
  }

  public function removeImage($id) {
    $pos = $this->findId($id);
    if ($pos === -1) return [ self::MSG => 'Súbor sa nepodarilo odstrániť' ];
    array_splice($this->data, $pos, 1);
    foreach ([self::LARGE, self::BIG, self::MID, self::SMALL, self::ICON, self::PROFILE, self::MINI] as $value) {
      unlink("{$this->dir}/img$id-$value.jpeg");
    }
    return [ "ok" => $id ];
  }

  public function loadImages($count, $offset) {
    $count = (int)$count;
    $offset = (int)$offset;
    if ($offset < 0 && $offset + $count < 0) return [];
    if ($offset < 0 && $offset + $count >= 0) {
      $count = $offset + $count;
      $offset = 0;
    }
    $c = count($this->data());
    if ($offset >= $c) return [];

    $c = $offset + $count < $c ? $offset + $count : $c;
    $data = [];
    for ($i = $offset; $i < $c; $i++) {
      $data[] = [
        'id' => $this->data[$i],
        'large' => [
          'size' => self::LARGE,
          'url' => $this->getBigImagePath($this->data[$i], self::LARGE)
        ],
        'big' => [
          'size' => self::BIG,
          'url' => $this->getBigImagePath($this->data[$i], self::BIG)
        ],
        'mid' => [
          'size' => self::MID,
          'url' => $this->getBigImagePath($this->data[$i], self::MID)
        ],
        'small' => [
          'size' => self::SMALL,
          'url' => $this->getBigImagePath($this->data[$i], self::SMALL)
        ],
        'icon' => [
          'size' => self::ICON,
          'url' => $this->getIcon($this->data[$i])
        ]
      ];
    }
    return $data;
  }

  public function save() {
    file_put_contents($this->file, json_encode($this->data));
  }
}
