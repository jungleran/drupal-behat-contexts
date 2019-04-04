<?php

namespace OrdinaDigitalServices;

use Behat\Behat\Context\Context;
use Drupal\search_api\Entity\Index;

/**
 * Class SearchContext
 *
 * Provides search related steps.
 */
class SearchContext implements Context {

  /**
   * @Given all content has been indexed
   * @SuppressWarnings("static")
   */
  public function allContentIsIndexed(): void {
    if (!\Drupal::moduleHandler()->moduleExists('search_api')) {
      throw new \RuntimeException('Search api must be enabled to be able to index items');
    }

    /** @var \Drupal\search_api\Entity\Index $index */
    foreach (Index::loadMultiple() as $index) {
      $index->indexItems();
    }
  }

}
