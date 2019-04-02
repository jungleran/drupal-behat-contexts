<?php

namespace OrdinaDigitalServices;

use Behat\Behat\Context\Context;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Mink\Element\NodeElement;

/**
 * Class ElementContext.
 *
 * Provides steps to perform html element based assertions.
 *
 * @package OrdinaDigitalServices
 *
 * @SuppressWarnings("public")
 */
class ElementContext implements Context {

  use UsesMink;

  /**
   * @Then I should see the :locator element :expectedCount time(s)
   *
   * @param string $locator
   *   The css locator.
   * @param int $expectedCount
   *
   * @throws \RuntimeException
   */
  public function iShouldSeeTheElementTime(string $locator, int $expectedCount): void {
    $elements = $this->getPage()->findAll('css', $locator);

    $actual = \count($elements);
    if ($actual !== $expectedCount) {
      throw new \RuntimeException("found {$locator} {$actual} times instead of the expected {$expectedCount} times");
    }
  }

  /**
   * @Then I should see regex :regex in the :locator element :expectedCount time(s)
   *
   * @param string $regex
   * @param string $locator
   *   The css locator.
   * @param int $expectedCount
   *
   * @throws \RuntimeException
   */
  public function iShouldSeeRegexInTheElementTime(string $regex, string $locator, int $expectedCount): void {
    $elements = $this->getPage()->findAll('css', $locator);

    $total = 0;
    /** @var \Behat\Mink\Element\NodeElement $element */
    foreach ($elements as $element) {
      $total += \preg_match_all("@$regex@", $element->getHtml());
    }

    if ($expectedCount !== $total) {
      throw new \RuntimeException("Expected to find {$expectedCount} matches for {$locator}, but found {$total}");
    }

  }

  /**
   * @Given I should not see :text outside of the :locator element
   *
   * @param string $text
   * @param string $locator
   *   The css locator.
   *
   * @throws \RuntimeException
   */
  public function iShouldNotSeeOutsideOfTheElement(string $text, string $locator): void {
    $this->minkContext->assertElementContainsText($locator, $text);

    $page = $this->getPage();
    $elements = $page->findAll('css', $locator);
    $html = $page->getHtml();
    /** @var \Behat\Mink\Element\NodeElement $element */
    foreach ($elements as $element) {
      $html = \str_replace($element->getOuterHtml(), '', $html);
    }

    $position = \strpos($html, $text);
    if ($position === FALSE) {
      return;
    }

    $textLength = \strlen($text);
    $foundText = \substr($html, $position - 50, $textLength + 100);
    throw new \RuntimeException("Found {$text} outside of a {$locator} element. Context: \n{$foundText}");
  }

  /**
   * @Given the :selector element should have a :attribute attribute containing :value
   *
   * @param string $locator
   *   The css locator.
   * @param string $attributeName
   * @param string $attributeValue
   *
   * @throws \RuntimeException
   */
  public function assertPageHasElementWithAttributeContainingValue(string $locator, string $attributeName, string $attributeValue): void {
    $element = $this->getPage()->find('css', $locator);
    if (empty($element)) {
      throw new \RuntimeException("Could not find element using the selector '{$locator}'");
    }
    $attributeValueExists = $this->elementContainsAttributeValue($element, $attributeName, $attributeValue);
    if (!$attributeValueExists) {
      throw new \RuntimeException("The value for the selector '{$locator}', attribute '{$attributeName}' does not contain '{$attributeValue}'");
    }
  }

  /**
   * Determine if a Mink NodeElement contains a specific attribute with a value.
   *
   * @param \Behat\Mink\Element\NodeElement $element
   * @param string $attribute
   * @param string $value
   *
   * @return bool
   */
  private function elementContainsAttributeValue(NodeElement $element, string $attribute, string $value): bool {
    $attributeValue = $element->getAttribute($attribute);

    if ($attributeValue === NULL) {
      return FALSE;
    }

    return \strpos($attributeValue, $value) !== FALSE;
  }

  /**
   * @Given I press button :label in the :locator element
   *
   * @param string $label
   * @param string $locator
   *   The css locator.
   *
   * @throws \Exception
   */
  public function iPressButtonWithLabelInElementWithSelector(string $label, string $locator): void {
    $elementObj = $this->findElement($locator);
    $buttonObj = $elementObj->findButton($label);

    if ($buttonObj === NULL) {
      throw new \RuntimeException("The button '{$label}' was not found in the '{$locator}' element on the page {$this->getCurrentUrl()}");
    }

    $elementObj->pressButton($label);
  }

  /**
   * Attempts to find an element with a given css locator.
   *
   * @param string $locator
   *   The css locator.
   *
   * @return \Behat\Mink\Element\NodeElement
   *
   * @throws \RuntimeException
   */
  public function findElement(string $locator): NodeElement {
    $elementObj = $this->getPage()->find('css', $locator);

    if (empty($elementObj)) {
      throw new \RuntimeException("No element with '{$locator}' could be found on the page {$this->getCurrentUrl()}");
    }

    return $elementObj;
  }

  /**
   * @Then I should see the :locator element
   *
   * @param string $locator
   *   The css locator.
   *
   * @throws \RuntimeException
   */
  public function iShouldSeeTheElement(string $locator): void {
    $this->findElement($locator);
  }

  /**
   * @Then I should not see the :locator element
   *
   * @param string $locator
   *   The css locator.
   *
   * @throws \RuntimeException
   */
  public function iShouldNotSeeTheElement(string $locator): void {
    $nodeElements = $this->getPage()->findAll('css', $locator);

    if (empty($nodeElements)) {
      return;
    }

    /** @var \Behat\Mink\Element\NodeElement $nodeElement */
    foreach ($nodeElements as $nodeElement) {
      try {
        if ($nodeElement->isVisible()) {
          throw new \RuntimeException("Found a {$locator} element where it was not supposed to be found");
        }
      }
      catch (UnsupportedDriverActionException $e) {
        throw new \RuntimeException("Found a {$locator} element in the raw HTML. It might be invisible, but we can only test that using the selenium driver. You could try tagging this scenario with @javascript.");
      }
    }
  }

  /**
   * @Then I should see a maximum of :count :locator element(s) containing :text
   *
   * @param int $count
   *   Count.
   * @param string $locator
   *   Locator.
   * @param string $text
   *   Text.
   *
   * @throws \RuntimeException
   */
  public function iShouldSeeMaximumOfElementsContaining(int $count, string $locator, string $text): void {
    $timesFound = $this->countElementsContainingText($locator, $text);

    if ($timesFound > $count) {
      throw new \RuntimeException("Expected to find a maximum of {$count} {$locator} elements containing '{$text}' but found {$timesFound}");
    }
  }

  /**
   * @param string $locator
   * @param string $text
   *
   * @return int
   */
  private function countElementsContainingText(string $locator, string $text): int {
    /** @var \Behat\Mink\Element\NodeElement[] $items */
    $items = $this->getPage()->findAll('css', $locator);

    $timesFound = 0;
    foreach ($items as $item) {
      if (\strpos($item->getText(), $text) === FALSE) {
        continue;
      }
      $timesFound++;
    }
    return $timesFound;
  }

  /**
   * @Then I should see a minimum of :count :locator element(s) containing :text
   *
   * @param int $count
   *   Count.
   * @param string $locator
   *   Locator.
   * @param string $text
   *   Text.
   *
   * @throws \RuntimeException
   */
  public function iShouldSeeMinimumOfElementsContaining(int $count, string $locator, string $text): void {
    $timesFound = $this->countElementsContainingText($locator, $text);

    if ($timesFound < $count) {
      throw new \RuntimeException("Expected to find a minimum of {$count} {$locator} elements containing '{$text}' but found {$timesFound}");
    }
  }

  /**
   * @Then I should see exactly :count :locator element(s) containing :text
   *
   * @param int $count
   *   Count.
   * @param string $locator
   *   Locator.
   * @param string $text
   *   Text.
   *
   * @throws \RuntimeException
   */
  public function iShouldSeeExactlyElementsContaining(int $count, string $locator, string $text): void {
    $timesFound = $this->countElementsContainingText($locator, $text);

    if ($timesFound !== $count) {
      throw new \RuntimeException("Expected to find exactly {$count} {$locator} elements containing '{$text}' but found {$timesFound}");
    }
  }

  /**
   * @Then I should see a maximum of :count :locator element(s)
   *
   * @param int $count
   *   Count.
   * @param string $locator
   *   Locator.
   *
   * @throws \RuntimeException
   */
  public function iShouldSeeMaximumOfElements(int $count, string $locator): void {
    $timesFound = $this->countElements($locator);

    if ($timesFound > $count) {
      throw new \RuntimeException("Expected to find a maximum of {$count} {$locator} elements but found {$timesFound}");
    }
  }

  /**
   * @param string $locator
   *   The locator.
   *
   * @return int
   *   The number of elements found
   */
  private function countElements(string $locator): int {
    /** @var \Behat\Mink\Element\NodeElement[] $items */
    $items = $this->getPage()->findAll('css', $locator);
    return \count($items);
  }

  /**
   * @Then I should see a minimum of :count :locator element(s)
   *
   * @param int $count
   *   Count.
   * @param string $locator
   *   Locator.
   *
   * @throws \RuntimeException
   */
  public function iShouldSeeMinimumOfElements(int $count, string $locator): void {
    $timesFound = $this->countElements($locator);

    if ($timesFound < $count) {
      throw new \RuntimeException("Expected to find a minimum of {$count} {$locator} elements but found {$timesFound}");
    }
  }

  /**
   * @Then I should see exactly :count :locator element(s)
   *
   * @param int $count
   *   Count.
   * @param string $locator
   *   Locator.
   *
   * @throws \RuntimeException
   */
  public function iShouldSeeExactlyElements(int $count, string $locator): void {
    $timesFound = $this->countElements($locator);

    if ($timesFound !== $count) {
      throw new \RuntimeException("Expected to find exactly {$count} {$locator} elements but found {$timesFound}");
    }
  }

  /**
   * @When I click the :locator element
   *
   * @param string $locator
   *   The css locator.
   *
   * @throws \RuntimeException
   */
  public function iClickTheElement(string $locator): void {
    $this->findElement($locator)->click();
  }

}
