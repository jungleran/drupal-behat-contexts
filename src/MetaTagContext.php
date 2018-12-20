<?php

namespace OrdinaDigitalServices;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Session;
use Drupal\DrupalExtension\Context\MinkContext;

/**
 * Class MetaTagContext.
 *
 * Provides assertions to test meta tags.
 */
class MetaTagContext implements Context {

  use UsesMink;

  /**
   * @Then there should be a meta-tag with a :attribute attribute containing :value
   *
   * @param string $attribute
   * @param string $value
   *
   * @throws \Exception
   */
  public function assertMetaTagWithAttributeValue(string $attribute, string $value): void {
    if (empty($this->findMatchingTags([$attribute => $value]))) {
      throw new \RuntimeException("No meta-tag with attribute '{$attribute}' containing {$value} has been found");
    }
  }

  /**
   * @param array $attributes
   *   An array of attribute values to look for, keyed by their attribute name.
   *
   * @return \Behat\Mink\Element\NodeElement[]
   */
  private function findMatchingTags(array $attributes): array {
    $tags = $this->getMetaTags();

    $matchingTags = [];
    foreach ($tags as $tag) {
      foreach ($attributes as $name => $value) {
        if (!$tag->hasAttribute($name)) {
          continue 2;
        }

        if ($tag->getAttribute($name) !== $value) {
          continue 2;
        }
      }

      $matchingTags[] = $tag;
    }

    return $matchingTags;
  }

  /**
   * @return \Behat\Mink\Element\NodeElement[]
   */
  private function getMetaTags(): array {
    return $this->getPage()->findAll('css', 'meta');
  }

  /**
   * @Then there should not be a meta-tag with a :attribute attribute containing :value
   *
   * @param string $attribute
   * @param string $value
   *
   * @throws \Exception
   */
  public function assertNoMetaTagWithAttributeValue(string $attribute, string $value): void {
    if (!empty($this->findMatchingTags([$attribute => $value]))) {
      throw new \RuntimeException("A meta tag with attribute {$attribute} containing '{$value}' has been found");
    }
  }

  /**
   * @Then there should be a meta-tag with the following attributes:
   *
   * @param \Behat\Gherkin\Node\TableNode $table
   */
  public function assertMetaTagMultipleAttributes(TableNode $table): void {
    $attributes = $table->getRowsHash();
    $matchingTags = $this->findMatchingTags($attributes);

    $formattedAttributes = $this->formatAttributes($attributes);

    if (empty($matchingTags)) {
      throw new \RuntimeException("No metatag were found with the following attributes: \n{$formattedAttributes}");
    }

    if (\count($matchingTags) > 1) {
      throw new \RuntimeException("Multiple metatags were found with the following attributes: \n{$formattedAttributes}");
    }
  }

  /**
   * @param $attributes
   *
   * @return string
   */
  private function formatAttributes($attributes): string {
    $formattedAttributes = '';
    foreach ($attributes as $name => $value) {
      $formattedAttributes .= "{$name}: $value\n";
    }
    return $formattedAttributes;
  }

  /**
   * @Then there should not be a meta-tag with the following attributes:
   *
   * @param \Behat\Gherkin\Node\TableNode $table
   */
  public function assertNoMetaTagMultipleAttributes(TableNode $table): void {
    $attributes = $table->getRowsHash();
    $matchingTags = $this->findMatchingTags($attributes);

    $formattedAttributes = $this->formatAttributes($attributes);

    if (!empty($matchingTags)) {
      throw new \RuntimeException("A metatag was found with the following attributes: \n{$formattedAttributes}");
    }
  }

}
