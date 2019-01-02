<?php

namespace OrdinaDigitalServices;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Session;
use Drupal\DrupalExtension\Context\MinkContext;

/**
 * Class BrowserContext.
 *
 * Provides browser specific implementations.
 */
class BrowserContext implements Context {

  /**
   * @var MinkContext
   */
  private $minkContext;

  /**
   * @var int
   */
  private $defaultWindowWidth = 1024;

  /**
   * @var int
   */
  private $defaultWindowHeight = 768;

  /**
   * @var bool
   */
  private $resizeOnScenarioStart;

  /**
   * BrowserContext constructor.
   *
   * @param bool $resizeOnScenarioStart
   * @param array $defaultWindowSize
   */
  public function __construct(bool $resizeOnScenarioStart = TRUE, array $defaultWindowSize = []) {
    $this->resizeOnScenarioStart = $resizeOnScenarioStart;
    if (isset($defaultWindowSize['height'])) {
      $this->defaultWindowHeight = $defaultWindowSize['height'];
    }
    if (isset($defaultWindowSize['width'])) {
      $this->defaultWindowWidth = $defaultWindowSize['width'];
    }
  }

  /**
   * @beforeScenario
   *
   * @param \Behat\Behat\Hook\Scope\BeforeScenarioScope $scope
   */
  public function gatherContexts(BeforeScenarioScope $scope): void {
    $this->minkContext = $scope->getEnvironment()->getContext(MinkContext::class);
  }

  /**
   * @beforeScenario
   *
   * @param \Behat\Behat\Hook\Scope\BeforeScenarioScope $scope
   */
  public function resetWindowSize(BeforeScenarioScope $scope): void {
    if (!$this->resizeOnScenarioStart) {
      return;
    }

    if (!$this->minkContext->getSession()->getDriver() instanceof Selenium2Driver) {
      return;
    }

    $this->resizeWindow($this->defaultWindowWidth, $this->defaultWindowHeight);
  }

  /**
   * @When I resize the window to :width pixels wide and :height pixels high
   *
   * @param int $width
   * @param int $height
   */
  public function resizeWindow(int $width, int $height): void {
    $this->minkContext->getSession()->resizeWindow($width, $height, 'current');
  }

  /**
   * @Given I wait :seconds second(s)
   *
   * @param int $seconds
   */
  public function iWaitSeconds(int $seconds): void {
    \sleep($seconds);
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

  /**
   * @Then I should be at :path
   * @Then I should be on the :path path
   *
   * @param string $path
   *
   * @throws \RuntimeException
   */
  public function iShouldBeAt(string $path): void {
    $baseUrl = \trim($this->minkContext->getMinkParameter('base_url'), '/');
    $path = \trim($path, '/');
    $expectedUrl = $baseUrl . '/' . $path;
    if ($expectedUrl !== $this->getSession()->getCurrentUrl()) {
      throw new \RuntimeException("You're not at {$expectedUrl}, but at " . $this->getSession()->getCurrentUrl());
    }
  }

}
