<?php
/**
 * Server rendering for the Mix and Match options block.
 *
 * @package WooCommerce Mix and Match Products/blocks
 *
 * @param array $attributes - The block attributes.
 * @param string $content - The block default content.
 * @param WP_Block $block - The block instance.
 */

use Backcourt\MixAndMatch\iAPI\Blocks\Controller;
use Backcourt\MixAndMatch\iAPI\Blocks\ChildItemQuantitySelector\Block;

defined( 'ABSPATH' ) || exit;

Controller::render_block( Block::class, $attributes, $content, $block );
