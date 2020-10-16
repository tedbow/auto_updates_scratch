<?php


namespace Drupal\upper;


class Updater {
  public function update(string $version) {
    $composer_json_path = $this->getComposerJsonPath();
    $dir = dir($composer_json_path);
    chdir($dir);
    $output = [];
    exec("composer require drupal/core-recommended:$version", $output);
    foreach ($output as $item) {
      \Drupal::messenger()->addMessage($item);
    }
  }

  private function getComposerJsonPath() {
    return __DIR__ . '/../composer.json';
  }


}