<?php

/*
 * This file is part of the Keygen package.
 *
 * (c) Glad Chinda <gladxeqs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Keygen;

/**
 * @method static Generator token(int $length)
 * @method static Generator numeric(int $length)
 * @method static Generator alphanum(int $length)
 * @method static Generator bytes(int $length)
 */
class Keygen extends AbstractGenerator
{
	/**
	 * Creates instance of generator from alias.
	 * 
	 * @param string $alias
	 * @param int|bool $length
	 * @return Keygen\Generator
	 * 
	 * @throws Keygen\Exceptions\InvalidGeneratorKeygenException
	 */
	protected function newGeneratorFromAlias($alias, $length = null)
	{
		return GeneratorFactory::getGeneratorFromAlias($alias)
			->mutate($this)
			->length(($length || is_bool($length)) ? $length : $this->length)
			->prefix($this->prefix)
			->suffix($this->suffix);
	}

	/**
	 * Overloaded method for creating generator instances.
	 *
	 * @param string $method
	 * @param array $args
	 * @return false|Keygen\Generator
	 */
	protected function overloadGeneratorMethod($method, $args)
	{
		$_args = $args;

		if (GeneratorFactory::generatorAliasExists($method)) {
			array_unshift($_args, $method);
			return call_user_func_array([$this, 'newGeneratorFromAlias'], $_args);
		}

		$method = strtolower($method);

		$aliases = GeneratorFactory::getAllGeneratorAliases();

		$methodRegex = sprintf("_?(%s)_?((?:[1-9]\d*)|random)?", join('|', $aliases));
		$methodRegex = '/'. $methodRegex .'$/';

		if (preg_match($methodRegex, $method, $matches)) {

			$length = isset($matches[2]) ? $matches[2] : null;
			$length = $length ? (($length === 'random') ? false : intval($length)) : null;

			$attribute = preg_replace($methodRegex, '', $method);

			$generator = $this->newGeneratorFromAlias($matches[1], $length);

			if (empty($attribute)) {
				return $generator;
			}

			if ($generator->isOverloaded($attribute)) {
				return $generator->{$attribute}();
			}
		}

		return false;
	}

	/**
	 * Overload the __get method
	 */
	public function __get($prop)
	{
		if ($overloader = $this->overloadGeneratorMethod($prop, [])) {
			return $overloader;
		}

		return parent::__get($prop);
	}

	/**
	 * Overload the __call method
	 */
	public function __call($method, $args)
	{
		if ($overloader = $this->overloadGeneratorMethod($method, $args)) {
			return $overloader;
		}

		return parent::__call($method, $args);
	}
}
