<?php

namespace OrdinaDigitalServices;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Class FailureContext.
 */
class FailureContext implements Context {

  use UsesMink;

  /**
   * @afterStep
   *
   * @param \Behat\Behat\Hook\Scope\AfterStepScope $scope
   *
   * @throws \RuntimeException
   */
  public function printCurrentUrl(AfterStepScope $scope): void {
    if ($scope->getTestResult()->getResultCode() !== TestResult::FAILED) {
      return;
    }

    if (!$this->getSession()->isStarted()) {
      return;
    }

    throw new \RuntimeException('Current url:' . $this->minkContext->getSession()->getCurrentUrl());
  }

}
