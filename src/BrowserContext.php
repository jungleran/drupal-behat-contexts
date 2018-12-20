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

  use UsesMink;

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
  public function resetWindowSize(BeforeScenarioScope $scope): void {
    if (!$this->resizeOnScenarioStart) {
      return;
    }

    if (!$this->getSession()->getDriver() instanceof Selenium2Driver) {
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
    $this->getSession()->resizeWindow($width, $height, 'current');
  }

  /**
   * @Given I wait :seconds second(s)
   *
   * @param int|float $seconds
   */
  public function iWaitMilliseconds($seconds): void {
    usleep($seconds * 1000000);
  }

}
