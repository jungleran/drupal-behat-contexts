<?php
/**
 * Created by PhpStorm.
 * User: chrisjansen
 * Date: 02-01-19
 * Time: 09:06
 */

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
