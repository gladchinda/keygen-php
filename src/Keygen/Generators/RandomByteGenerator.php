<?php

/*
 * This file is part of the Keygen package.
 *
 * (c) Glad Chinda <gladxeqs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Keygen\Generators;

use Keygen\Generator;
use Keygen\Exceptions\BinaryDataKeygenException;

class RandomByteGenerator extends Generator
{
	/**
	 * Should return as hexadecimal.
	 *
	 * @var bool
	 */
	protected $hex = false;

	/**
	 * Should return as base64-encoded.
	 *
	 * @var bool
	 */
	protected $base64 = false;

	/**
	 * List of available byte output types.
	 *
	 * @var array
	 */
	protected static $outputs = ['hex', 'base64'];

	/**
	 * Disable all byte output types.
	 *
	 * @return null
	 */
	protected function resetByteOutput()
	{
		foreach (static::$outputs as $output) {
			$this->{$output} = false;
		}

		return $this;
	}

	/**
	 * Enables a byte output type.
	 *
	 * @param string $type
	 * @return $this
	 */
	protected function enableByteOutput($type)
	{
		$type = strtolower($type);

		if (in_array($type, static::$outputs)) {
			$this->resetByteOutput();
			$this->{$type} = true;
		}

		return $this;
	}

	/**
	 * Get the enabled byte output type.
	 *
	 * @return string|null
	 */
	protected function enabledByteOutput()
	{
		$output = array_filter(static::$outputs, function($type) {
			return $this->{$type} === true;
		});

		return empty($output) ? null : array_shift($output);
	}

	/**
	 * Enables hexadecimal output.
	 *
	 * @return $this
	 */
	protected function hex()
	{
		return $this->enableByteOutput('hex');
	}

	/**
	 * Enables base64-encoded output.
	 *
	 * @return $this
	 */
	protected function base64()
	{
		return $this->enableByteOutput('base64');
	}

	/**
	 * Generates the random bytes.
	 *
	 * @param int $length
	 * @return string
	 *
	 * @throws Keygen\Exceptions\BinaryDataKeygenException
	 */
	protected static function generateRandomBytes($length)
	{
		if (function_exists('random_bytes')) {
			$bytes = random_bytes($length);
		}

		elseif (function_exists('openssl_random_pseudo_bytes')) {
			$bytes = openssl_random_pseudo_bytes($length);
		}

		elseif (@file_exists('/dev/urandom') && $length < 100) {
			$bytes = file_get_contents('/dev/urandom', false, null, 0, $length);
		}

		else {
			throw new BinaryDataKeygenException('Unable to generate binary data.');
		}

		return $bytes;
	}

	/**
	 * Generates the random bytes as hexadecimal.
	 *
	 * @param int $length
	 * @return string
	 *
	 * @throws Keygen\Exceptions\BinaryDataKeygenException
	 */
	protected static function bytesAsHex($length)
	{
		$bytes = static::generateRandomBytes(ceil($length / 2));
		return substr(bin2hex($bytes), 0, $length);
	}

	/**
	 * Generates the random bytes as base64-encoded.
	 *
	 * @param int $length
	 * @return string
	 *
	 * @throws Keygen\Exceptions\BinaryDataKeygenException
	 */
	protected static function bytesAsBase64($length)
	{
		$bytes = static::generateRandomBytes(round($length * 3 / 4));
		return substr(base64_encode($bytes), -$length);
	}

	/**
	 * Generates a random key.
	 *
	 * @param int $length
	 * @return string
	 */
	protected function keygen($length)
	{
		$output = $this->enabledByteOutput();

		if ($output) {

			$method = sprintf("bytesAs%s", ucfirst(strtolower($output)));

			if (method_exists(static::class, $method)) {
				return static::{$method}($length);
			}

		}

		return static::generateRandomBytes($length);
	}

	/**
	 * Finish up key generation logic and return the generated key or key collection.
	 *
	 * @param string|array $key (The generated key or key collection)
	 * @return string|array
	 */
	protected function finishKeyGeneration($key)
	{
		$this->resetByteOutput();
		return $key;
	}

	/**
	 * List of the allowed overloaded attributes.
	 *
	 * @return array
	 */
	protected static function getOverloadedAttributes()
	{
		$appends = ['hex', 'base64'];
		$attributes = parent::getOverloadedAttributes();

		return array_unique(array_merge($attributes, $appends));
	}

	/**
	 * List of the allowed overloaded methods.
	 *
	 * @return array
	 */
	protected static function getOverloadedMethods()
	{
		$appends = ['hex', 'base64'];
		$methods = parent::getOverloadedMethods();

		return array_unique(array_merge($methods, $appends));
	}
}
