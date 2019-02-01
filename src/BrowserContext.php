<?php

namespace OrdinaDigitalServices;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Session;
use DMore\ChromeDriver\ChromeDriver;
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

    /** @var \Behat\MinkExtension\Context\MinkContext $minkContext */
    $this->minkContext = $scope->getEnvironment()->getContext(MinkContext::class);

    $driver = $this->getSession()->getDriver();
    if (!$driver instanceof Selenium2Driver && !$driver instanceof ChromeDriver) {
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
    if (!$this->getSession()->isStarted()) {
      return;
    }
    $this->getSession()->resizeWindow($width, $height, 'current');
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
