<?php

namespace OrdinaDigitalServices;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class MenuContext.
 *
 * Adds steps to manipulate menu items.
 */
class MenuContext implements Context {

  use UsesEntities;

  /**
   * @BeforeScenario
   */
  public function startSession(): void {
    $session = new Session();
    // The default session id causes issues with StrictSessionHandler.php
    // because it's either too long or contains invalid characters. Setting an
    // id ourselves solves that issue.
    $session->setId('behat-test-session');
    \Drupal::requestStack()->getCurrentRequest()->setSession($session);
  }

  /**
   * @Given :menu menu items:
   *
   * @param string $menu
   * @param \Behat\Gherkin\Node\TableNode $table
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Exception
   */
  public function mainMenuItems(string $menu, TableNode $table): void {
    $menuLinkStorage = \Drupal::entityTypeManager()->getStorage('menu_link_content');
    foreach ($table->getColumnsHash() as $hash) {
      $parent = NULL;
      if (isset($hash['parent'])) {
        $parents = $menuLinkStorage->loadByProperties([
          'menu_name' => $menu,
          'title' => $hash['parent'],
        ]);

        $parent = \reset($parents);
      }

      $item = (object) [
        'title' => $hash['title'],
        'link' => ['uri' => $hash['uri']],
        'menu_name' => $menu,
        'expanded' => TRUE,
        'parent' => $parent ? $parent->getPluginId() : NULL,
      ];

      $this->entityContext->createEntity('menu_link_content', $item);
    }
  }
}
