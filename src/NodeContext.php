<?php

namespace OrdinaDigitalServices;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Session;
use DateTimeZone;
use Drupal\DrupalExtension\Context\MinkContext;
use Drupal\node\Entity\Node;
use stdClass;

/**
 * Class NodeContext
 *
 * Adds steps that manipulate node entities.
 */
class NodeContext implements Context {

  /**
   * @var \OrdinaDigitalServices\EntityContext
   */
  private $entityContext;

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
    $environment = $scope->getEnvironment();

    $this->entityContext = $environment->getContext(EntityContext::class);
    $this->minkContext = $environment->getContext(MinkContext::class);
  }

  /**
   * @When the title of node :originalTitle has been changed to :newTitle
   *
   * @param string $originalTitle
   * @param string $newTitle
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function theTitleOfNodeHasBeenChangedTo(string $originalTitle, string $newTitle): void {
    $node = $this->loadNodeByTitle($originalTitle);
    $node->set('title', $newTitle);
    $node->save();
  }

  /**
   * @When I am viewing :title
   *
   * @param string $title
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function iAmViewing(string $title): void {
    $nid = $this->entityContext->findEntityWithFieldValue('node', 'title', $title);
    $this->getSession()->visit($this->minkContext->locatePath('/node/' . $nid));
  }

  /**
   * @param $title
   *
   * @return \Drupal\node\Entity\Node
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Exception
   */
  public function loadNodeByTitle(string $title): Node {
    $nid = $this->entityContext->findEntityWithFieldValue('node', 'title', $title);
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->entityContext->entityLoad('node', $nid);
    if ($node === NULL) {
      throw new \RuntimeException("{$title} could not be loaded");
    }
    return $node;
  }

  /**
   * @param \stdClass $values
   *
   * @return \Drupal\node\Entity\Node
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createNode(stdClass $values): Node {
    if (!isset($values->title)) {
      throw new \InvalidArgumentException('Nodes require a title');
    }

    $nid = $this->entityContext->createEntity('node', $values);
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->entityContext->entityLoad('node', $nid);

    if ($node === NULL) {
      throw new \RuntimeException("{$values->title} could not be created");
    }

    return $node;
  }

  /**
   * @Given :type content with relative date:
   *
   * @param string $type
   * @param \Behat\Gherkin\Node\TableNode $nodesTable
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function singleContentWithUpCastDate(string $type, TableNode $nodesTable): void {
    foreach ($nodesTable->getHash() as $nodeHash) {
      $node = (object) $this->upCastDates($nodeHash);
      $node->type = $type;
      $this->createNode($node);
    }
  }

  /**
   * @Given :amount :type content with relative date:
   *
   * @param int $amount
   * @param string $contentType
   * @param \Behat\Gherkin\Node\TableNode $nodesTable
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function multipleContentWithUpCastDate(int $amount, string $contentType, TableNode $nodesTable): void {
    foreach ($nodesTable->getHash() as $nodeHash) {
      for($i = 1; $i <= $amount; $i++) {
        $currentHash = $nodeHash;
        foreach ($currentHash as $key => $value) {
          $currentHash[$key] = \str_replace('%n', $i, $value);
        }
        $node = (object) $this->upCastDates($currentHash);
        $node->type = $contentType;
        $this->createNode($node);
      }
    }
  }

  /**
   * Converts string values to dates.
   *
   * @param array $values
   *
   * @return array
   */
  private function upCastDates(array $values): array {
    foreach ($values as $key => $value) {
      if ($value === '') {
        continue;
      }

      try {
        // Create the date as UTC to prevent issues with daylight savings time.
        $date = new \DateTimeImmutable($value, new DateTimeZone('utc'));
        $values[$key] = $date->format('Y-m-d H:i:s');
        if ($key === 'published') {
          $values[$key] = $date->getTimestamp();
        }
      }
      catch (\Exception $exception) {
        // Apparently this isn't a date.
      }
    }
    return $values;
  }

  /**
   * @When I edit content :title
   *
   * @param string $title
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function iEditContent(string $title): void {
    $node = $this->loadNodeByTitle($title);
    $this->minkContext->visit($this->minkContext->locatePath('/node/' . $node->id() . '/edit'));
  }

  /**
   * @return \Behat\Mink\Session
   */
  private function getSession(): Session {
    return $this->minkContext->getSession();
  }

}
