<?php

namespace OrdinaDigitalServices;

use Behat\Behat\Context\Context;
use Drupal\file\FileInterface;

/**
 * Class FileContext.
 *
 * Provides steps and an API to manage files from behat.
 */
class FileContext implements Context {

  use UsesMink;

  /**
   * @Given file :sourcePath exists
   *
   * @param string $sourcePath
   *   The path to the file relative to the mink files directory.
   */
  public function fileExists(string $sourcePath) {
    $sourcePath = $this->getAbsolutePathForFile($sourcePath);
    if (!\is_file($sourcePath)) {
      throw new \RuntimeException("{$sourcePath} is not a file.");
    }

    $fileName = \basename($sourcePath);

    $destination = "public://{$fileName}";
    $data = \file_get_contents($sourcePath);
    $file = \file_save_data($data, $destination, FILE_EXISTS_REPLACE);
    if (!$file instanceof FileInterface) {
      throw new \RuntimeException("Could not save {$sourcePath} to {$destination}");
    }

    return $file;
  }

  /**
   * @param string $sourcePath
   *   The path to the file relative to the mink files directory.
   *
   * @return string
   *   The absolute path to the file based on the given source path.
   */
  public function getAbsolutePathForFile(string $sourcePath): string {
    $filePath = $this->minkContext->getMinkParameter('files_path');
    if ($filePath === NULL) {
      throw new \RuntimeException('The mink "files_path" parameter needs to be configured or we can\'t upload the file.');
    }

    $realpath = \realpath($filePath);
    if ($realpath === FALSE) {
      throw new \RuntimeException("{$filePath} does not exist");
    }

    return \rtrim($realpath, DIRECTORY_SEPARATOR)  . DIRECTORY_SEPARATOR . $sourcePath;
  }

  /**
   * @var string $uri
   *   The uri of the file. e.g. public::/my-image.png
   *
   * @return \Drupal\file\FileInterface
   *   The loaded file.
   *
   * @throw \RuntimeException
   *   When the file doesn't exist.
   */
  public function loadFileEntityForUri(string $uri) {
    $existing_files = entity_load_multiple_by_properties('file', ['uri' => $uri]);
    if (!count($existing_files)) {
      throw new \RuntimeException("No file with {$uri} exists");
    }
    return \reset($existing_files);
  }

}
