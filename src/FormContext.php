<?php

namespace OrdinaDigitalServices;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Mink\Element\NodeElement;
use Drupal\DrupalExtension\Context\MinkContext;

/**
 * Class FormContext.
 *
 * Provides steps to perform actions and assertions on forms.
 */
class FormContext implements Context {

  use UsesMink;

  /**
   * @Given I empty the :locator field
   *
   * @param string $locator
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  public function iEmptyTheField(string $locator): void {
    $this->getPage()->fillField($locator, '');
  }

  /**
   * @Given the :label checkbox should be disabled
   *
   * @param string $label
   *
   * @throws \RuntimeException
   */
  public function assertCheckboxDisabled(string $label): void {
    $checkbox = $this->findCheckbox($label);

    if ($checkbox->getAttribute('disabled') === NULL) {
      throw new \RuntimeException("The {$label} checkbox is not disabled");
    }
  }

  /**
   * @Given the :label checkbox should be enabled
   *
   * @param string $label
   *
   * @throws \RuntimeException
   */
  public function assertCheckboxEnabled(string $label): void {
    $checkbox = $this->findCheckbox($label);

    if ($checkbox->getAttribute('disabled') === NULL) {
      throw new \RuntimeException("The {$label} checkbox is not disabled");
    }
  }

  /**
   * @param string $label
   *
   * @return \Behat\Mink\Element\NodeElement
   *
   * @throws \RuntimeException
   */
  private function findCheckbox(string $label): NodeElement {
    /** @var \Behat\Mink\Element\NodeElement[] $fields */
    $fields = $this->getPage()->findAll('named', array('field', $label));

    foreach ($fields as $field) {
      if (!$field->hasAttribute('type')) {
        continue;
      }

      if ($field->getAttribute('type') !== 'checkbox') {
        continue;
      }

      return $field;
    }

    throw new \RuntimeException("No {$label} checkbox could be found");
  }

}
