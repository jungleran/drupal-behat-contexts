<?php

namespace OrdinaDigitalServices;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Mink\Session;
use Drupal\DrupalExtension\Context\MinkContext;

/**
 * Class BrowserContext.
 *
 * Provides browser specific implementations.
 *
 * @package DigitalServices
 */
class BrowserContext implements Context {

  /**
   * @var MinkContext
   */
  private $minkContext;

  /**
   * @beforeScenario
   *
   * @param \Behat\Behat\Hook\Scope\BeforeScenarioScope $scope
   */
  public function gatherContexts(BeforeScenarioScope $scope): void {
    $this->minkContext = $scope->getEnvironment()->getContext(MinkContext::class);
  }

  /**
   * @Given I switch to the :locator iframe
   *
   * @param string $locator
   *   Selector for the iframe.
   *
   * @throws \Exception
   */
  public function switchToIframe(string $locator): void {
    $function = <<<JS
      (function(){
         let iframe = document.querySelector("$locator");
         iframe.name = "iframeToSwitchTo";
      })()
JS;

    try {
      $this->getSession()->executeScript($function);
    }
    catch (\Exception $e) {
      throw new \RuntimeException(sprintf('Could not locate iframe %s', $locator));
    }

    $this->getSession()->getDriver()->switchToIFrame('iframeToSwitchTo');
  }

  /**
   * @return \Behat\Mink\Session
   */
  private function getSession(): Session {
    return $this->minkContext->getSession();
  }

  /**
   * @When I switch back from the iframe
   * @When I am not switched to any iframe
   *
   * @throws \Exception
   */
  public function switchToMainFrame(): void {
    $this->getSession()->getDriver()->switchToIFrame();
  }

}
