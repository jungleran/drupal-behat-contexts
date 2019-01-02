<?php

namespace OrdinaDigitalServices;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Drupal\entityqueue\Entity\EntityQueue;
use Drupal\entityqueue\EntityQueueInterface;

/**
 * Class EntityQueueContext.
 *
 * Provides steps to manipulate entity queues.
 */
class EntityQueueContext implements Context {

  use UsesEntities;

  /**
   * @beforeFeature
   *
   * @param \Behat\Behat\Hook\Scope\BeforeFeatureScope $scope
   */
  public static function beforeFeature(BeforeFeatureScope $scope): void {
    if (!\Drupal::moduleHandler()->moduleExists('entityqueue')) {
      throw new \RuntimeException(self::class . 'does not work without the entity queue module');
    }
  }

  /**
   * @Given :nodeTitle is added to the :queueId queue
   *
   * @param string $nodeTitle
   * @param string $queueName
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function isAddedToTheQueue(string $nodeTitle, string $queueName): void {
    $nid = $this->entityContext->findEntityWithFieldValue('node', 'title', $nodeTitle);

    $items = $this->getEntityQueueItems($queueName);
    $items[] = ['target_id' => $nid];
    $this->setEntityQueueItems($queueName, $items);
  }

  /**
   * @Given the :queueId queue is empty
   *
   * @param string $queueId
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function theQueueIsEmpty(string $queueId): void {
    $this->setEntityQueueItems($queueId, []);
  }

  /**
   * @param string $queueId
   *
   * @return array
   *
   * @throws \RuntimeException
   */
  private function getEntityQueueItems($queueId): array {
    $queue = $this->getEntityQueue($queueId);

    return $queue->get('items')->getValue();
  }

  /**
   * @param string $queueId
   *
   * @return \Drupal\entityqueue\EntityQueueInterface
   */
  private function getEntityQueue(string $queueId): EntityQueueInterface {
    $queue = EntityQueue::load($queueId);
    if ($queue === NULL) {
      throw new \RuntimeException("Unknown queue: {$queueId}");
    }

    return $queue;
  }

  /**
   * @param string $queueId
   * @param array $items
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Exception
   */
  private function setEntityQueueItems(string $queueId, array $items): void {
    $queue = $this->getEntityQueue($queueId);
    $queue->set('items', $items);
    $queue->save();
  }

}
