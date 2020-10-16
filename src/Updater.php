<?php


namespace Drupal\auto_updates;


class Updater {
  public function update(string $version) {
    $composer_json_path = $this->getComposerJsonPath();

    if (!is_file($composer_json_path)) {
      \Drupal::messenger()->addError("path: " . $composer_json_path);
      return;
    }
    $dir = dirname($composer_json_path);
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
  }

  private function getComposerJsonPath() {
    $path = __DIR__ . '/../../../../composer.json';
    //\Drupal::messenger()->addWarning(__DIR__);
    return realpath($path);
  }


}