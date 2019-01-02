<?php

namespace OrdinaDigitalServices;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Drupal\DrupalExtension\Context\DrupalContext;

/**
 * Trait UsesEntities.
 *
 * Provides general functionality to easily access the drupal context.
 */
trait UsesDrupal {

  /**
   * @var \Drupal\DrupalExtension\Context\DrupalContext
   */
  private $drupalContext;

  /**
   * {@inheritdoc}
   *
   * @BeforeScenario
   */
  public function getDrupalContext(BeforeScenarioScope $scope): void {
    $this->drupalContext = $scope->getEnvironment()->getContext(DrupalContext::class);
  }

}
