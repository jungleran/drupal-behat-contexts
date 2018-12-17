<?php

namespace OrdinaDigitalServices;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Mink\Session;
use Drupal\DrupalExtension\Context\MinkContext;

/**
 * Class SequentialContext.
 *
 * Provides steps to verify that things occur before or after each other.
 */
class SequentialContext implements Context {

  /**
   * @var \Drupal\DrupalExtension\Context\MinkContext
   */
  private $minkContext;

  /**
   * @beforeScenario
   *
   * @param \Behat\Behat\Hook\Scope\BeforeScenarioScope $scope
   *
   * @throws \Exception
   */
  public function gatherContexts(BeforeScenarioScope $scope): void {
    $this->minkContext = $scope->getEnvironment()->getContext(MinkContext::class);
  }

  /**
   * @Then heading :headingBefore should directly precede :tag heading :headingAfter
   *
   * @param string $headingBefore
   *   Text before.
   * @param string $tag
   *   Tag.
   * @param string $headingAfter
   *   Text after.
   *
   * @throws \RuntimeException
   */
  public function headingShouldDirectlyPrecede(string $headingBefore, string $tag, string $headingAfter): void {
    $validTags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
    if (!\in_array($tag, $validTags, TRUE)) {
      throw new \RuntimeException("$tag tag is not a valid heading");
    }

    /** @var \Behat\Mink\Element\NodeElement[] $headingElements */
    $headingElements = $this->getSession()->getPage()->findAll('css', \implode(', ', $validTags));
    foreach ($headingElements as $index => $headingElement) {
      if ($headingElement->getText() !== $headingBefore) {
        continue;
      }

      if (!isset($headingElements[$index + 1])) {
        break;
      }

      $nextHeading = $headingElements[$index + 1];
      if ($nextHeading->getText() === $headingAfter && $nextHeading->getTagName() === $tag) {
        return;
      }
    }

    throw new \RuntimeException("Heading {$headingBefore} does not precede {$tag} heading {$headingAfter}");
  }

  /**
   * @return \Behat\Mink\Session
   */
  private function getSession(): Session {
    return $this->minkContext->getSession();
  }

  /**
   * @Then heading :headingBefore should precede heading :headingAfter
   *
   * @param string $headingBefore
   * @param string $headingAfter
   */
  public function headingShouldPrecede(string $headingBefore, string $headingAfter): void {
    /** @var \Behat\Mink\Element\NodeElement[] $headingElements */
    $headingElements = $this->getSession()->getPage()->findAll('css', 'h1, h2, h3, h4, h5, h6');

    $firstIndex = $secondIndex = null;
    foreach ($headingElements as $index => $headingElement) {
      if ($headingElement->getText() === $headingBefore) {
        if ($firstIndex !== null) {
          throw new \RuntimeException("Found multiple instances of the header {$headingBefore}");
        }
        $firstIndex = $index;
      }
      if ($headingElement->getText() === $headingAfter) {
        if ($secondIndex !== null) {
          throw new \RuntimeException("Found multiple instances of the header {$headingAfter}");
        }
        $secondIndex = $index;
      }
    }

    if ($firstIndex === null) {
      throw new \RuntimeException("Could not find the heading {$headingBefore}");
    }

    if ($secondIndex === null) {
      throw new \RuntimeException("Could not find the heading {$headingAfter}");
    }

    if ($firstIndex < $secondIndex) {
      return;
    }

    throw new \RuntimeException("Heading {$headingBefore} does not precede heading {$headingAfter}");
  }

  /**
   * @Then I should see :textBefore precede :textAfter
   *
   * @param string $textBefore
   *   Text before.
   * @param string $textAfter
   *   Text after.
   *
   * @throws \RuntimeException
   */
  public function textShouldPrecedeText(string $textBefore, string $textAfter): void {
    $pageText = $this->getSession()->getPage()->getHtml();

    $firstPosition = \strpos($pageText, $textBefore);
    if ($firstPosition === FALSE) {
      throw new \RuntimeException("Could not find {$textBefore}");
    }

    $secondPosition = \strpos($pageText, $textAfter);
    if ($secondPosition === FALSE) {
      throw new \RuntimeException("Could not find {$textAfter}");
    }

    if ($firstPosition < $secondPosition) {
      return;
    }

    throw new \RuntimeException("{$textAfter} comes before {$textBefore}");
  }

}
