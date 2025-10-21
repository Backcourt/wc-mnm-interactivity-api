<?php
/**
 * WC Mix and Match - Interactivity API Bootstrap class
 *
 * @license GPLv2 or later
 * @package Backcourt\MixAndMatch\iAPI
 */

namespace Backcourt\MixAndMatch\iAPI;

use Exception;
use Backcourt\MixAndMatch\iAPI\Auryn\Injector;
use Backcourt\MixAndMatch\iAPI\Services\HookRegistrar;
use Backcourt\MixAndMatch\iAPI\Interfaces\Hookable;

use Backcourt\MixAndMatch\iAPI\Auryn\InjectionException;
use Automattic\WooCommerce\Blocks\Utils\BlockTemplateUtils;

/**
 * Class Plugin
 */
class Plugin {

	/**
	 * Plugin slug
	 */
	const SLUG = 'plugin-name';
	/**
	 * Plugin version
	 */
	const VERSION = '1.0.0-alpha.2';
	/**
	 * Dependency Injection Container.
	 */
	protected Injector $injector;
	/**
	 * Hooks registrar.
	 */
	protected HookRegistrar $registrar;
	/**
	 * Hold the single instance.
	 */
	protected static $instance;

	/**
	 * Get single instance.
	 */
	final public static function get_instance() {
		return isset( static::$instance )
			? static::$instance
			: static::$instance = new static();
	}

	/**
	 * Make a subclass instance.
	 */
	final public static function make( string $className ) {
		return self::get_instance()->injector->make( $className );
	}

	/**
	 * Plugin constructor.
	 */
	public function __construct() {
		$this->injector  = new Injector();
		$this->registrar = new HookRegistrar();
	}

	/**
	 * Initialize plugin
	 */
	public function run(): void {
		$hookables = $this->discover_hookables( WC_MNM_INTERACTIVITY_API_DIR . '/src', __NAMESPACE__ );

		$this->register_hookables( $hookables );

		$this->registrar->run( $this->injector );

		/**
		 * You can use the $injector->make( PluginName\Some\Class::class ) to get any plugin class.
		 * More detail: https://github.com/wppunk/WPPlugin#dependency-injection-container
		 *
		 * @todo - may want to delay this to after plugins_loaded (like WooCommerce does).
		 */
		do_action( 'wc_mnm_interactivity_api_init', $this->injector );
	}


	/**
	 * Discover all hookable classes in a directory implementing Hookable.
	 *
	 * @param string $baseDir       Absolute path to directory.
	 * @param string $baseNamespace Base PHP namespace for classes.
	 *
	 * @return string[] List of fully qualified class names.
	 */
	protected function discover_hookables( string $baseDir, string $baseNamespace ): array {
		$classes = array();

		$files = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $baseDir )
		);

		foreach ( $files as $file ) {

			// Skip directories, non-PHP files, and render.php files.
			// We don't want to register render.php files as hookable classes.
			if ( $file->isDir() || $file->getExtension() !== 'php' || 'render.php' === $file->getFilename() ) {
				continue;
			}

			// Derive FQCN from file path.
			$relativePath = str_replace( array( $baseDir, '/', '.php' ), array( '', '\\', '' ), $file->getRealPath() );
			$class        = $baseNamespace . $relativePath;

			if ( ! class_exists( $class ) ) {
				continue;
			}

			$ref = new \ReflectionClass( $class );
			if ( $ref->isInstantiable() && $ref->implementsInterface( Hookable::class ) ) {
				$classes[] = $class;
			}
		}

		return $classes;
	}

	/**
	 * Call register_hooks() on each discovered hookable class.
	 *
	 * @param string[] $hookables Array of fully qualified class names.
	 */
	protected function register_hookables( array $hookables ): void {
		foreach ( $hookables as $class ) {
			$class::register_hooks( $this->registrar );
		}
	}
}
