<?php
/**
 * Render Block Interface
 *
 * @package Backcourt\MixAndMatch\iAPI\Services
 */

namespace Backcourt\MixAndMatch\iAPI\Interfaces;

defined( 'ABSPATH' ) || exit;

interface RenderBlock {
	/**
	 * Render a block.
	 *
	 * @param array    $attributes - The block attributes.
	 * @param string   $content - The block default content.
	 * @param WP_Block $block - The block instance.
	 */
	public function render_block( array $attributes, string $content, \WP_Block $block ): void;
}
