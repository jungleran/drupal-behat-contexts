<?php

namespace OrdinaDigitalServices;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;

/**
 * Trait UsesFiles.
 *
 * Provides general functionality to easily access the entity context.
 */
trait UsesFiles {

  /**
   * @var \OrdinaDigitalServices\FileContext
   */
  private $fileContext;

  /**
   * {@inheritdoc}
   *
   * @BeforeScenario
   */
  public function getFileContext(BeforeScenarioScope $scope): void {
    $this->fileContext = $scope->getEnvironment()->getContext(FileContext::class);
  }

}
