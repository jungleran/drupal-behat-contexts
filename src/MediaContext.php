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

  /**
   * @Given :type media:
   *
   * @param string $type
   * @param \Behat\Gherkin\Node\TableNode $tableNode
   *
   * @throws \Exception
   */
  public function imageMedia(string $type, TableNode $tableNode): void {
    foreach ($tableNode->getHash() as $nodeHash) {
      $media = (object) $nodeHash;
      $media->bundle = $type;
      $this->entityContext->createEntity('media', $media);
    }
  }
}
