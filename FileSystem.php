<?php
class FileSystem
{
  private $_entries = array();

  public static function create($entry){
    return new FileSystem($entry);
  }

  public function __construct($entry){
    if(!is_array($entry)){
      $this->_entries = array($entry);
    } else if(is_array($entry)){
      $this->_entries = $entry;
    } else {
      throw new Exception("Illegal root dir specified.");
    }
  }

  public function children(){
    $args = func_get_args();
    $callback = $this->_detectCallback($args);

    $result_entries = array();
    foreach($this->_entries as $parent){
      if(!is_dir($parent)){
        continue;
      }

      if ($handle = opendir($parent)) {
        while (false !== ($item = readdir($handle))) {
          if($item == '.' || $item == '..'){
            continue;
          }

          $entry = $parent.DIRECTORY_SEPARATOR.$item;
          $args[0] = $entry;
          if(call_user_func_array($callback, $args)){
            $result_entries[] = $entry;
          }

        }
        closedir($handle);
      }
    }
    return new FileSystem($result_entries);
  }

  public function filter(){
    $args = func_get_args();
    $callback = $this->_detectCallback($args);

    $result_entries = array();
    foreach($this->_entries as $entry){
      $args[0] = $entry;
      if(call_user_func_array($callback, $args)){
        $result_entries[] = $entry;
      }
    }
    return new FileSystem($result_entries);
  }

  public function recursive(){
    $args = func_get_args();
    $callback = $this->_detectCallback($args);

    $result_entries = array();
    foreach($this->_entries as $entry){
      $result_entries = array_merge($result_entries, $this->_recursive($entry, $callback, $args));
    }

    return new FileSystem($result_entries);
  }

  public function map(){
    $args = func_get_args();
    $callback = $this->_detectCallback($args);

    foreach($this->_entries as $entry){
      $args[0] = $entry;
      call_user_func_array($callback, $args);
    }

    return $this;
  }

  private function _detectCallback($args){
    $callback = $args[0];

    if(!is_callable($callback)){
      throw new Exception("Fist arg is must be callable.");
    }

    return $callback;
  }

  private function _recursive($dir, $callback, $args){
    if(!is_dir($dir)){
      throw new Exception($dir." is not directory.");
    }

    $result_entries = array();

    if ($handle = opendir($dir)) {
      while (false !== ($item = readdir($handle))) {
        if($item == '.' || $item == '..'){
          continue;
        }

        $entry = $dir.DIRECTORY_SEPARATOR.$item;

        if(is_dir($entry)){
          $result_entries = array_merge($result_entries, $this->_recursive($entry, $callback, $args));
        } else {
          $args[0] = $entry;
          if(call_user_func_array($callback, $args)){
            $result_entries[] = $entry;
          }
        }

      }
      closedir($handle);
    }

    return $result_entries;
  }

  public function toArray(){
    return $this->_entries;
  }

  ////////////Utility function
  public static function getLastName($entry){
    $paths = explode(DIRECTORY_SEPARATOR, $entry);
    return $paths[count($paths) - 1];
  }

  public static function hasChild($entry, $childname){
    if(!is_dir($entry)){
      return false;
    }

    return file_exists($entry.DIRECTORY_SEPARATOR.$childname);
  }

  public static function nameIs($entry, $name){
    return FileSystem::getLastName($entry) == $name;
  }

  public static function nameMatch($entry, $pattern){
    return (bool) preg_match($pattern, FileSystem::getLastName($entry));
  }
}