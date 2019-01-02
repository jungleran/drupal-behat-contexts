<?php

namespace OrdinaDigitalServices;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Mink\Element\ElementInterface;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Session;
use Drupal\DrupalExtension\Context\MinkContext;

/**
 * Class JavascriptContext.
 *
 * Adds steps and assertions that need javascript to function.
 */
final class JavascriptContext implements Context {

  /**
   * MinkContext.
   *
   * @var \Drupal\DrupalExtension\Context\MinkContext
   */
  private $minkContext;

  /**
   * {@inheritdoc}
   *
   * @BeforeScenario
   */
  public function gatherContexts(BeforeScenarioScope $scope): void {
    /** @var \Behat\Behat\Context\Environment\InitializedContextEnvironment $environment */
    $environment = $scope->getEnvironment();

    $this->minkContext = $environment->getContext(MinkContext::class);
  }

  /**
   * @When for WYSIWYG field :name I enter :value
   *
   * @param string $name
   * @param string $value
   */
  public function forWysiwygEnter(string $name, string $value): void {
    $field = $this->getSession()->getPage()->findField($name);

    if ($field === NULL) {
      throw new \RuntimeException("Could not find CKEditor with locator: {$name}");
    }

    $fieldId = $field->getAttribute('id');
    if ($fieldId === NULL) {
      throw new \RuntimeException("Could not find an id for field with locator: {$name}");
    }
    $this->executeScript("CKEDITOR.instances[\"$fieldId\"].setData(\"$value\");");

    if (\Drupal::moduleHandler()->moduleExists('maxlength')) {
      // The maxlength script listens to ckeditor's elementsPathUpdate event.
      $this->executeScript("CKEDITOR.instances[\"$fieldId\"].fire(\"elementsPathUpdate\");");
      // The maxlength script waits 100 milliseconds before recalculating the
      // number of remaining characters, so we'll do the same.
      \usleep(100000);
    }
  }

  /**
   * @return \Behat\Mink\Session
   */
  private function getSession(): Session {
    return $this->minkContext->getSession();
  }

  /**
   * @param string $script
   */
  private function executeScript(string $script): void {
    $this->getSession()
      ->executeScript($script);
  }

  /**
   * @When I move the mouse to indicate that I am human
   */
  public function iMoveTheMouseToIndicateThatAmHuman(): void {
    $this->executeScript("jQuery('body').trigger('mousemove')");
  }

  /**
   * @When I expand the dropbutton in the :label row
   *
   * @throws \RuntimeException
   */
  public function iExpandTheDropButtonInTheRow($label): void {
    $page = $this->getSession()->getPage();

    $dropButton = $this->findTableRow($page, $label)->find('css', '.dropbutton-toggle button');
    if ($dropButton) {
      $dropButton->click();
      return;
    }

    throw new \RuntimeException("Found a row containing '{$label}', but no dropbutton on page {$this->getSession()->getCurrentUrl()}");
  }

  /**
   * Retrieve a table row containing specified text from a given element.
   *
   * @param \Behat\Mink\Element\ElementInterface $element
   * @param string $search
   *   The text to search for in the table row.
   *
   * @return \Behat\Mink\Element\NodeElement
   *
   * @throws \RuntimeException
   */
  public function findTableRow(ElementInterface $element, string $search): NodeElement {
    $rows = $element->findAll('css', 'tr');
    if (empty($rows)) {
      throw new \RuntimeException("No rows found on page {$this->getSession()->getCurrentUrl()}");
    }

    $foundRows = [];
    foreach ($rows as $row) {
      if (\strpos($row->getText(), $search) !== FALSE) {
        $foundRows[] = $row;
      }
    }

    if (empty($foundRows)) {
      throw new \RuntimeException("Failed to find a row containing '{$search}' on page {$this->getSession()->getCurrentUrl()}");
    }

    if (\count($foundRows) > 1) {
      throw new \RuntimeException("Found multiple rows containing '{$search}' on page {$this->getSession()->getCurrentUrl()}");
    }

    return \reset($foundRows);
  }

  /**
   * @Given browser form validation is disabled
   */
  public function iDisableBrowserFormValidation(): void {
    $script = <<<JS
var forms = document.querySelectorAll('form');

for (i = 0; i < forms.length; ++i) {
  forms[i].setAttribute('novalidate', '');
}
JS;
    $this->executeScript($script);
  }

  /**
   * @Then the :locator element should have focus
   *
   * @param string $locator
   *
   * @throws \RuntimeException
   */
  public function locatorShouldHaveFocus(string $locator): void {
    /** @var \Behat\Mink\Element\NodeElement[] $elements */
    $elements = $this->getSession()->getPage()->findAll('css', $locator);

    if (empty($elements)) {
      throw new \RuntimeException("No element with css locator '{$locator}' could be found");
    }

    foreach ($elements as $element) {
      $xpath = $element->getXpath();
      $script = "return document.activeElement === document.evaluate(\"{$xpath}\", document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;";

      $hasFocus = $this->getSession()->evaluateScript($script);
      if ($hasFocus) {
        return;
      }
    }

    throw new \RuntimeException("Could not find any css element '{$locator}' with focus");
  }

  /**
   * @Given I scroll :locator into view
   *
   * @param string $locator
   *   The CSS locator.
   */
  public function iScrollToIntoView(string $locator): void {
    $script = "document.querySelector('{$locator}').scrollIntoView()";
    $this->executeScript($script);
  }

}
