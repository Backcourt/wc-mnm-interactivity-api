<?php
/**
 * Server rendering for the Mix and Match options block.
 *
 * @package Backcourt\MixAndMatch\iAPI\Blocks
 *
 * @param array $attributes - The block attributes.
 * @param string $content - The block default content.
 * @param WP_Block $block - The block instance.
 */

use Backcourt\MixAndMatch\iAPI\Blocks\Controller;
use Backcourt\MixAndMatch\iAPI\Blocks\AddToCart\Block;

defined( 'ABSPATH' ) || exit;

Controller::render_block( Block::class, $attributes, $content, $block );
