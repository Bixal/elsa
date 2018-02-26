<?php

namespace Drupal\do_jump_links\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\block\BlockRepositoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Cache\Cache;
use Drupal\node\NodeInterface;

/**
 * Provides a 'DevelopmentObjectiveJumpLinksBlock' block.
 *
 * @Block(
 *  id = "development_objective_jump_links_block",
 *  admin_label = @Translation("Development objective jump links block"),
 * )
 */
class DevelopmentObjectiveJumpLinksBlock extends BlockBase implements ContainerFactoryPluginInterface {

  protected $node;
  protected $blockRepository;
  protected $jumpLinks = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(RouteMatchInterface $route_match, BlockRepositoryInterface $block_repository) {
    $this->node = $route_match->getParameter('node');
    $this->blockRepository = $block_repository;
  }

  /**
   * Create method.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container.
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   The plugin_id.
   * @param mixed $plugin_definition
   *   The plugin definition.
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('current_route_match'),
      $container->get('block.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    /* Check if the node we are displaying the block in has all the fields with
    info in them, and only send those to the twig template */

    if ($this->node instanceof NodeInterface) {
      // You can get nid and anything else you need from the node object.
      if (count($this->node->get("body")->getString()) > 0) {
        $this->jumpLinks["#overview"] = "Overview";
      }
    }

    $visible_blocks_per_region = $this->blockRepository->getVisibleBlocksPerRegion();

    if ($visible_blocks_per_region != NULL) {
      // This is for Socialblue Theme (Blocks on content)
      if (isset($visible_blocks_per_region['content']['views_block__projects_block_1'])) {
        $this->jumpLinks['#block-views-block-projects-block-1'] = "Related Projects";
      }
      if (isset($visible_blocks_per_region['content']['views_block__learning_library_items_block_1'])) {
        $this->jumpLinks["#block-views-block-learning-library-items-block-1"] = "Resources";
      }
      if (isset($visible_blocks_per_region['content']['views_block__news_and_highlights_topic_view__block_1'])) {
        $this->jumpLinks["#block-views-block-news-and-highlights-topic-view-block-1"] = "News & Highlights";
      }
      if (isset($visible_blocks_per_region['content']['views_block__upcoming_events_block_block_1'])) {
        $this->jumpLinks["#block-views-block-upcoming-events-block-block-1"] = "Events";
      }

      // This is for Partners Cola Theme (Blocks on content_bottom)
      if (isset($visible_blocks_per_region['content_bottom']['partners_colab_views_block__projects_block'])) {
        $this->jumpLinks['#block-partners-colab-views-block-projects-block'] = "Related Projects";
      }
      if (isset($visible_blocks_per_region['content_bottom']['partners_colab_views_block__learning_library_items_block'])) {
        $this->jumpLinks["#block-partners-colab-views-block-learning-library-items-block"] = "Resources";
      }
      if (isset($visible_blocks_per_region['content_bottom']['partners_colab_views_block__news_and_highlights__block'])) {
        $this->jumpLinks["#block-partners-colab-views-block-news-and-highlights-block"] = "News & Highlights";
      }
      if (isset($visible_blocks_per_region['content_bottom']['partners_colab_views_block__upcoming_events_block'])) {
        $this->jumpLinks["#block-partners-colab-views-block-upcoming-events-block"] = "Events";
      }
    }

    $build = [];
    $build['development_objective_jump_links_block']['#theme'] = 'do_jump_links';
    $build['development_objective_jump_links_block']['#jump_links'] = $this->jumpLinks;

    return $build;
  }

  /**
   * The getCacheTags method.
   *
   * @return array|\string[]
   *   Returns an array of cache strings.
   */
  public function getCacheTags() {
    // With this when your node change your block will rebuild.
    if ($node = \Drupal::routeMatch()->getParameter('node')) {
      // If there is node add its cachetag.
      return Cache::mergeTags(parent::getCacheTags(), ['node:' . $node->id()]);
    }
    else {
      // Return default tags instead.
      return parent::getCacheTags();
    }
  }

  /**
   * The getCacheTags method.
   *
   * @return \string[]
   *   Returns an array of cache strings.
   */
  public function getCacheContexts() {
    /* If you depends on \Drupal::routeMatch()
    you must set context of this block with 'route' context tag.
    Every new route this block will rebuild*/
    return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
  }

}
