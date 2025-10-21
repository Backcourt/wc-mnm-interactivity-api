<?php
/**
 * Mix and Match Child Items Block
 *
 * @package Backcourt\MixAndMatch\iAPI\Blocks
 * 
 * @since   1.0.0
 * @version 1.0.0
 */

namespace Backcourt\MixAndMatch\iAPI\Blocks\ChildItems;

defined( 'ABSPATH' ) || exit;

use Backcourt\MixAndMatch\iAPI\Interfaces\RenderBlock;

/**
 * Block Name: Mix and Match Child Items
 */
class Block implements RenderBlock {

	/**
	 * Server rendering for this block
	 * 
	 * @since 1.0.0
	 *
	 * @param array    $attributes - The block attributes.
	 * @param string   $content - The block default content.
	 * @param WP_Block $block - The block instance.
	 */
	public function render_block( array $attributes, string $content, \WP_Block $block ): void {
		echo $content;
	}

}
