<?php

namespace OrdinaDigitalServices;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;

/**
 * Trait UsesEntities.
 *
 * Provides general functionality to easily access the entity context.
 */
trait UsesEntities {

  /**
   * @var \OrdinaDigitalServices\EntityContext
   */
  private $entityContext;

  /**
   * {@inheritdoc}
   *
   * @BeforeScenario
   */
  public function getEntityContext(BeforeScenarioScope $scope): void {
    $this->entityContext = $scope->getEnvironment()->getContext(EntityContext::class);
  }

}
