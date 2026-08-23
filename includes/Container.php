<?php
/**
 * Dependency container.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore;

defined( 'ABSPATH' ) || exit;

use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;

/**
 * Resolves and caches plugin services.
 */
final class Container {

	/**
	 * Shared instances.
	 *
	 * @var array<string, object>
	 */
	private $instances = array();

	/**
	 * Resolves a class, reusing the instance on later calls.
	 *
	 * @param string $id Class name.
	 * @return object
	 */
	public function get( string $id ) {
		if ( ! isset( $this->instances[ $id ] ) ) {
			$this->instances[ $id ] = $this->build( $id );
		}

		return $this->instances[ $id ];
	}

	/**
	 * Instantiates a class, resolving constructor dependencies by type hint.
	 *
	 * @param string $class_name Class to build.
	 * @return object
	 *
	 * @throws RuntimeException When a dependency cannot be resolved.
	 */
	private function build( string $class_name ) {
		if ( ! class_exists( $class_name ) ) {
			throw new RuntimeException( esc_html( sprintf( 'Cannot resolve unknown class "%s".', $class_name ) ) );
		}

		$reflection  = new ReflectionClass( $class_name );
		$constructor = $reflection->getConstructor();

		if ( null === $constructor || 0 === $constructor->getNumberOfParameters() ) {
			return new $class_name();
		}

		$arguments = array();

		foreach ( $constructor->getParameters() as $parameter ) {
			$type = $parameter->getType();

			if ( $type instanceof ReflectionNamedType && ! $type->isBuiltin() ) {
				$arguments[] = $this->get( $type->getName() );
				continue;
			}

			if ( $parameter->isDefaultValueAvailable() ) {
				$arguments[] = $parameter->getDefaultValue();
				continue;
			}

			throw new RuntimeException(
				esc_html( sprintf( 'Cannot resolve parameter "$%s" of %s.', $parameter->getName(), $class_name ) )
			);
		}

		return $reflection->newInstanceArgs( $arguments );
	}
}
