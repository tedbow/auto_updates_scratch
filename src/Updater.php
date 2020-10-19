<?php


namespace Drupal\auto_updates;


use Drupal\Core\FileTransfer\Local;

class Updater {
  public function update(string $version) {
    $composer_json_path = $this->getComposerJsonPath();
    $composer_lock_path = $this->getComposerLockPath();

    if (!is_file($composer_json_path)) {
      \Drupal::messenger()->addError("path: " . $composer_json_path);
      return;
    }

    $temp_update_directory = $this->getTempUpdateDir();
    $filetransfer = $this->getFileTransfer();
    if (!is_dir($temp_update_directory)) {
      if(is_file($temp_update_directory)) {
        \Drupal::messenger()->addError("Update dir is a file " . $temp_update_directory);
      }
    }
    else {
      $filetransfer->removeDirectory($temp_update_directory);
    }
    $filetransfer->createDirectory($temp_update_directory);
    $filetransfer->copyFile($composer_json_path, "$temp_update_directory/composer.json");
    $filetransfer->copyFile($composer_lock_path, "$temp_update_directory/composer.lock");

    $dir = dirname("$temp_update_directory/composer.json");
    \Drupal::messenger()->addMessage("dir: " . realpath($dir));
    chdir($dir);
    $output = [];
    \Drupal::messenger()->addMessage("Trying to update to : $version");
    exec("composer require drupal/core-recommended:$version --with-dependencies", $output, $return);
    if ($return !== 0) {
      \Drupal::messenger()->addError("return $return");
    }
    foreach ($output as $item) {
      \Drupal::messenger()->addMessage($item);
    }
    // If we made it this far validate update
    if ($this->isUpdateValid($this->getRootPath(), $temp_update_directory)) {
      $this->transferUpdate($this->getRootPath(), $temp_update_directory);
    }
    else {
      \Drupal::messenger()->addWarning("Update could not be performed.");
    }
  }

  private function getComposerJsonPath() {

    $path = __DIR__ . '/../../../../composer.json';
    //\Drupal::messenger()->addWarning(__DIR__);
    return $this->getRootPath() . '/composer.json';
  }

  private function getComposerLockPath() {
    return $this->getRootPath() . '/composer.lock';
  }

  /**
   * @return false|string
   */
  private function getRootPath() {
    return realpath(__DIR__ . '/../../../..');
  }

  private function getTempUpdateDir() {
    return $this->getRootPath() . '/DrupalCoreUpdateTemp';
  }

  /**
   * Determines if an update is valid.
   *
   * Perform various checks to see if an update is valid. For since we are only
   * updating core we may check if anything besides core and vendor updates were
   * updated.
   *
   * @param bool $getRootPath
   * @param string $temp_update_directory
   */
  private function isUpdateValid(bool $getRootPath, string $temp_update_directory) {
    return TRUE;
  }

  /**
   * @param string $root_path
   * @param string $temp_update_directory
   */
  private function transferUpdate(string $root_path, string $temp_update_directory) {
    $filetranser = $this->getFileTransfer();
    $filetranser->removeDirectory("$root_path/vendor");
    $filetranser->copyDirectory("$temp_update_directory/vendor", "$root_path/vendor");
    $filetranser->removeDirectory("$root_path/web/core");
    $filetranser->copyDirectory("$temp_update_directory/web/core", "$root_path/web/core");
    $filetranser->removeDirectory($temp_update_directory);
  }

  /**
   * @return \Drupal\Core\FileTransfer\Local
   */
  protected function getFileTransfer(): \Drupal\Core\FileTransfer\Local {
    return new Local($this->getRootPath(),
      \Drupal::service('file_system'));
  }


}