<?php

namespace DigitalServices;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Drupal\DrupalExtension\Context\DrupalContext;

/**
 * Class ModuleContext.
 *
 * Provides steps to verify the state of modules.
 *
 * @package DigitalServices
 */
class ModuleContext implements Context {

  /**
   * The Drupal Context.
   *
   * @var \Drupal\DrupalExtension\Context\DrupalContext
   */
  private $drupalContext;

  /**
   * @beforeScenario
   *
   * @param \Behat\Behat\Hook\Scope\BeforeScenarioScope $scope
   *
   * @throws \Exception
   */
  public function gatherContexts(BeforeScenarioScope $scope) {
    $this->drupalContext = $scope->getEnvironment()->getContext(DrupalContext::class);
  }

  /**
   * @Then the following modules are disabled:
   */
  public function theFollowingModulesAreDisabled(TableNode $table): void {
    $columns = $table->getColumnsHash();
    foreach ($columns as $collection) {
      if (!isset($collection['module'])) {
        throw new \RuntimeException('Module names must be listed in a column called `module`');
      }

      if (\Drupal::moduleHandler()->moduleExists($collection['module'])) {
        throw new \RuntimeException(sprintf('Module %s is enabled while it should not be', $collection['module']));
      }
    }
  }

}
