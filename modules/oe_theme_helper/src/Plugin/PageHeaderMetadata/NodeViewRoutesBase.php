<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\PageHeaderMetadata;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\node\NodeInterface;
use Drupal\oe_theme_helper\Event\NodeMetadataEvent;
use Drupal\oe_theme_helper\PageHeaderMetadataEvents;
use Drupal\oe_theme_helper\PageHeaderMetadataPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Base plugin to handle metadata for node view routes.
 *
 * This is a base plugin as it should be extended to return extra metadata
 * for the node.
 */
abstract class NodeViewRoutesBase extends PageHeaderMetadataPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * Creates a new NodeViewRouteBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   */
  public function __construct(array $configuration, string $plugin_id, $plugin_definition, EventDispatcherInterface $event_dispatcher) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('event_dispatcher')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(): array {
    $entity = $this->getNode();

    $metadata = [];

    $introduction = $this->getIntroduction();
    if ($introduction) {
      $metadata['introduction'] = $introduction;
    }

    $cacheability = new CacheableMetadata();
    $cacheability
      ->addCacheableDependency($entity)
      ->addCacheContexts(['route'])
      ->applyTo($metadata);

    return $metadata;
  }

  /**
   * Returns the node retrieved from the current route.
   *
   * @return \Drupal\node\NodeInterface|null
   *   The node entity, or NULL if not found.
   */
  protected function getNode(): ?NodeInterface {

    $event = new NodeMetadataEvent();
    $this->eventDispatcher->dispatch(PageHeaderMetadataEvents::NODE, $event);

    return $event->getNode();
  }

  /**
   * Gets introduction data from the node.
   *
   * @param string $field_name
   *   Node's field name where data is taken from.
   *
   * @return array|null
   *   Inline template if data exists or NULL otherwise.
   */
  protected function getIntroduction($field_name = 'oe_summary'): ?array {
    $node = $this->getNode();
    if (!$node->hasField($field_name) || $node->get($field_name)->isEmpty()) {
      return NULL;
    }
    $summary = $node->get($field_name)->first();

    return $this->getInlineTemplate($summary->value, $summary->format, $summary->getLangcode());
  }

  /**
   * Gets inline template to be used in page header.
   *
   * @param string $text
   *   Text in the template.
   * @param string $format
   *   Text format.
   * @param string $langcode
   *   Language code.
   *
   * @return array
   *   Template array.
   */
  protected function getInlineTemplate($text, $format, $langcode): array {
    return [
      // We strip the tags because the component expects only one paragraph of
      // text and the field is using a text format which adds paragraph tags.
      '#type' => 'inline_template',
      '#template' => '{{ summary|render|striptags("<strong><a><em>")|raw }}',
      '#context' => [
        'summary' => [
          '#type' => 'processed_text',
          '#text' => $text,
          '#format' => $format,
          '#langcode' => $langcode,
        ],
      ],
    ];
  }

}
