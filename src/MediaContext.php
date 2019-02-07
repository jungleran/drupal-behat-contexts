<?php

namespace OrdinaDigitalServices;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;

/**
 * Class MediaContext.
 *
 * Adds steps to handle media entities.
 */
class MediaContext implements Context {

  use UsesEntities;
  use UsesFiles;

  /**
   * @Given :type media:
   *
   * @param string $type
   * @param \Behat\Gherkin\Node\TableNode $tableNode
   *
   * @throws \Exception
   */
  public function givenMedia(string $type, TableNode $tableNode): void {
    foreach ($tableNode->getHash() as $hash) {
      $media = (object) $this->prepareHash($hash);
      $media->bundle = $type;
      $this->entityContext->createEntity('media', $media);
    }
  }

  /**
   * @param $hash
   *
   * @return mixed
   */
  private function prepareHash($hash) {
    foreach ($hash as $fieldName => $fieldValue) {
      $sourcePath = $this->fileContext->getAbsolutePathForFile($fieldValue);
      if (!\file_exists($sourcePath)) {
        continue;
      }

      $fileName = \basename($sourcePath);
      $uri = "public://{$fileName}";
      try {
        $file = $this->fileContext->loadFileEntityForUri($uri);
      }
      catch (\RuntimeException $exception) {
        $file = $this->fileContext->fileExists($fieldValue);
      }

      $hash[$fieldName] = $uri;
    }

    return $hash;
  }
}
