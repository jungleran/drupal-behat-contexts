<?php

namespace OrdinaDigitalServices;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Mink\Session;
use Drupal\DrupalExtension\Context\MinkContext;

/**
 * Class LinkContext.
 *
 * Provide assertions to verify links.
 */
class LinkContext implements Context {

  /**
   * @var \Drupal\DrupalExtension\Context\MinkContext
   */
  private $minkContext;

  /**
   * @BeforeScenario
   *
   * @param \Behat\Behat\Hook\Scope\BeforeScenarioScope $scope
   */
  public function gatherContexts(BeforeScenarioScope $scope): void {
    $this->minkContext = $scope->getEnvironment()->getContext(MinkContext::class);
  }

  /**
   * @Then I should see a link :label with url :url
   *
   * @param string $label
   * @param string $url
   *
   * @throws \RuntimeException
   */
  public function assertLinkWithUrl(string $label, string $url): void {
    /** @var \Behat\Mink\Element\NodeElement[] $links */
    $links = $this->getSession()->getPage()->findAll('named', array('link', $label));

    if (empty($links)) {
      throw new \RuntimeException("No link '{$label}' found on " . $this->getSession()->getCurrentUrl());
    }

    $invisibleLinkFound = FALSE;
    foreach ($links as $link) {
      if ($link->getAttribute('href') !== $url) {
        continue;
      }

      // We have found a matching link, but it may not be visible. We therefore
      // check it's visibility to make sure.
      try {
        if (!$link->isVisible()) {
          $invisibleLinkFound = TRUE;
          continue;
        }
      }
      catch (UnsupportedDriverActionException $exception) {
        // Not all drivers support the isVisible method. We assume a link is
        // visible if the method is not supported.
      }

      return;
    }

    if ($invisibleLinkFound) {
      throw new \RuntimeException("Found a '{$label}' link with url {$url}, but it was invisible.");
    }
    throw new \RuntimeException("Found multiple '{$label}' links, but none with the url {$url}");
  }

  /**
   * @return \Behat\Mink\Session
   */
  private function getSession(): Session {
    return $this->minkContext->getSession();
  }

  /**
   * @Then I should not see the link :label with url :url
   *
   * @param string $label
   * @param string $url
   *
   * @throws \Exception
   */
  public function assertNoLinkWithUrl(string $label, string $url): void {
    try {
      $this->assertLinkWithUrl($label, $url);
    }
    catch (\RuntimeException $e) {
      // We're expecting an exception because we're negating the positive check.
      return;
    }

    throw new \RuntimeException("At least one '{$label}' link with url '{$url}' was found");
  }

  /**
   * @Given I click :label in the :locator element
   *
   * @param string $label
   * @param string $locator
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  public function assertClickableInElement(string $label, string $locator): void {
    $session = $this->getSession();
    $elements = $session->getPage()->findAll('css', $locator);

    if (empty($elements)) {
      throw new ElementNotFoundException($session, null, 'css', $locator);
    }

    /** @var \Behat\Mink\Element\NodeElement $element */
    foreach ($elements as $element) {
      $link = $element->findLink($label);
      if ($link !== NULL) {
        $link->click();
        return;
      }
    }

    throw new \RuntimeException(sprintf('No link %s could be found', $label));
  }

}
