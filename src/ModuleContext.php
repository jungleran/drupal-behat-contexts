<?php

namespace OrdinaDigitalServices;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Drupal\DrupalExtension\Context\DrupalContext;

/**
 * Class ModuleContext.
 *
 * Provides steps to verify the state of modules.
 */
class ModuleContext implements Context {

  /**
   * @Then the following modules are disabled:
   *
   * @param \Behat\Gherkin\Node\TableNode $table
   */
  public function theFollowingModulesAreDisabled(TableNode $table): void {
    $columns = $table->getColumnsHash();
    foreach ($columns as $collection) {
      if (!isset($collection['module'])) {
        throw new \RuntimeException('Module names must be listed in a column called `module`');
      }

      if (\Drupal::moduleHandler()->moduleExists($collection['module'])) {
        throw new \RuntimeException("Module '{$collection['module']}' is enabled while it should not be");
      }
    }
  }

}
