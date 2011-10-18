<?php
/**
 * @author Chiel Kunkels <hello@chielkunkels.com>
 */
class Template {
	/**
	 * Opening delimiter of a block definition
	 */
	const BLOCK_OPEN = '<!--';
	/**
	 * Closing delimiter of a block definition
	 */
	const BLOCK_CLOSE = '-->';
	/**
	 * Beginning keyword for a block definition
	 */
	const BEGIN = 'begin';
	/**
	 * Ending keyword for a block definition
	 */
	const END = 'end';
	/**
	 * Defines the opening delimiter for a variable
	 */
	const VAR_OPEN = '{';
	/**
	 * Defines the closing delimiter for a variable
	 */
	const VAR_CLOSE = '}';
	/**
	 * @var string $path   Path to the template file
	 */
	private $path = '';
	/**
	 * @var array $delimiters   Array containing all delimiters used for parsing
	 */
	private $delimiters = array();
	/**
	 * @var array $escapedDelimiters   Array containing all delimiters escaped for use in regular expressions
	 */
	private $escapedDelimiters = array();
	/**
	 * @var array $blocks   Array containing all parseable blocks from the template
	 */
	private $blocks = array();
	/**
	 * @var array $parsed   Keep track of the blocks that have been parsed
	 */
	private $parsed = array();
	/**
	 * @var array $vars   All the variables that have been assigned to the template
	 */
	private $vars = array();
	/**
	 * @var array $functions   User-set functions to format values that are assigned
	 */
	private $functions = array();

	/**
	 * Initiate a new template object
	 *
	 * @param string $path   Path to the template file
	 * @param array $delimiters   Array of alternate delimiters
	 */
	public function __construct($path, $delimiters = array())
	{
		if (empty($path)) {
			throw new ArumentException('No template file specified');
		}
		if (!file_exists($path)) {
			throw new ArgumentException('Specified file <strong>'.$path.'</strong> does not exist');
		}

		$this->path = $path;
		$this->setDelimiters($delimiters);
		$this->splitSource();
	}

	/**
	 * Pass a value to the template
	 *
	 * @param string $key   Key representing the value
	 * @param array|string $value   The value to be assigned
	 * @param boolean $expand   If you pass an array as $value, you can choose to have it expanded. The array keys will form the key in conjunction with the passed key
	 * @return boolean   Returns true if the value was assigned correctly, false otherwise
	 */
	public function pass($key, $value, $expand = true)
	{
		if (empty($key)) {
			throw new ArgumentException('No key passed to assign.');
		}

		if (is_array($value) && $expand) {
			foreach ($value as $k => $v) {
				$this->vars[$key.'.'.$k] = $v;
			}
		} else {
			$this->vars[$key] = $value;
		}
	}

	/**
	 * Register a custom template function
	 *
	 * @param string $trigger   The string representing the function in the template
	 * @param mixed $callback   A function or class method to be used as a template function
	 * @return boolean   True if the function was succesfully registered, false otherwise
	 */
	public function registerFunction($trigger, $callback)
	{
		if (isset($this->functions[$trigger])) {
			throw new ArgumentException('Function already registered for trigger <strong>'.$trigger.'</strong>');
		}
		if (!is_callable($callback)) {
			throw new ArgumentException('Cannot find provided callback');
		}

		$this->functions[$trigger] = $callback;
	}

	/**
	 * Parse a template block
	 *
	 * @param string $block   Name of the block
	 * @return boolean   True if the block was parsed without problems, false otherwise
	 */
	public function parse($block)
	{
		if (!isset($this->blocks[$block])) {
			throw new Exception('Specified block <strong>'.$block.'</strong> does not exist');
		}

		$d = $this->delimiters;
		$ed = $this->escapedDelimiters;

		if (!isset($this->parsed[$block])) {
			$this->parsed[$block] = '';
		}
		$parse = $this->blocks[$block];

		// Match all could-be vars in this block
		$regex_var = '/'.$ed['VAR_OPEN'].'([-_a-z0-9:]+(?:\.[-_a-z0-9]+)*)((?:\.[-_a-z0-9]+\([^)]*\))*)'.$ed['VAR_CLOSE'].'/i';
		preg_match_all($regex_var, $parse, $match, PREG_SET_ORDER);
		foreach ($match as $var) {
			// Check if a value was assigned
			if (isset($this->vars[$var[1]])) {
				$value = $this->vars[$var[1]];
				// Check if any template functions are to be used on this value
				if ($var[2] != '') {
					$functions = explode('.', ltrim($var[2], '.'));
					foreach ($functions as $callback) {
						// Split function & arguments
						preg_match('/(.*?)\(([^(]*)\)/i', $callback, $match);
						$function = $match[1];
						$arguments = $match[2];
						// Check if there's a callback for this
						if (isset($this->functions[$function])) {
							$value = call_user_func($this->functions[$function], $value, $arguments);
						}
					}
				}
				$parse = str_replace($var[0], $value, $parse);
			}
		}

		// Match all block references in this block
		$regex_block = '/'.$ed['BLOCK_OPEN'].$ed['BEGIN'].'(.+?)'.$ed['END'].$ed['BLOCK_CLOSE'].'/i';
		preg_match_all($regex_block, $parse, $match, PREG_SET_ORDER);
		foreach ($match as $reference) {
			if (isset($this->parsed[$reference[1]])) {
				$parse = str_replace($reference[0], $this->parsed[$reference[1]], $parse);
				$this->parse[$reference[1]] = '';
			}
		}

		$this->parsed[$block] .= $parse;
	}

	/**
	 * Echo or return a block
	 *
	 * @param string $block   Name of the block
	 * @param boolean $return   Whether you want to return the block or not
	 * @return string|void   Returns the block if $return is set to true, otherwise void
	 */
	public function render($block, $return = false)
	{
		if (!isset($this->parsed[$block])) {
			throw new Exception('Specified block <strong>'.$block.'</strong> has not been parsed');
		}

		if ($return) {
			return $this->parsed[$block];
		} else {
			echo $this->parsed[$block];
		}
	}

	/**
	 * Set delimiters to be used with specified template file
	 *
	 * @param array $delimiters   Array of delimiters which are to be used.
	 */
	private function setDelimiters($d)
	{
		$this->delimiters = array(
			'BLOCK_OPEN' => isset($d['BLOCK_OPEN']) ? $d['BLOCK_OPEN'] : self::BLOCK_OPEN,
			'BLOCK_CLOSE' => isset($d['BLOCK_CLOSE']) ? $d['BLOCK_CLOSE'] : self::BLOCK_CLOSE,
			'BEGIN' => isset($d['BEGIN']) ? $d['BEGIN'] : self::BEGIN,
			'END' => isset($d['END']) ? $d['END'] : self::END,
			'VAR_OPEN' => isset($d['VAR_OPEN']) ? $d['VAR_OPEN'] : self::VAR_OPEN,
			'VAR_CLOSE' => isset($d['VAR_CLOSE']) ? $d['VAR_CLOSE'] : self::VAR_CLOSE
		);
		$this->escapedDelimiters = array(
			'BLOCK_OPEN' => preg_quote($this->delimiters['BLOCK_OPEN'], '/'),
			'BLOCK_CLOSE' => preg_quote($this->delimiters['BLOCK_CLOSE'], '/'),
			'BEGIN' => preg_quote($this->delimiters['BEGIN'], '/'),
			'END' => preg_quote($this->delimiters['END'], '/'),
			'VAR_OPEN' => preg_quote($this->delimiters['VAR_OPEN'], '/'),
			'VAR_CLOSE' => preg_quote($this->delimiters['VAR_CLOSE'], '/')
		);
	}

	/**
	 * Splits the source into parse-able blocks.
	 *
	 * @throws Exception
	 */
	private function splitSource()
	{
		$d = $this->delimiters;
		$ed = $this->escapedDelimiters;

		$regex_begin = '/^\s*'.$ed['BEGIN'].' ([^.]+?)\s*'.$ed['BLOCK_CLOSE'].'(.*)/is';
		$regex_end = '/^\s*'.$ed['END'].' ([^.]+?)\s*'.$ed['BLOCK_CLOSE'].'(.*)/is';

		$source = file_get_contents($this->path);
		$blocks = explode($d['BLOCK_OPEN'], $source);
		$source = null;

		$blocknames = array();

		foreach ($blocks as $block) {
			if (preg_match($regex_begin, $block, $match)) {
				// We just opened a new block
				$parent = implode('.', $blocknames);
				$blocknames[] = $match[1];
				$current = implode('.', $blocknames);
				$this->blocks[$current] = $match[2];
			}

			// Closing block
			elseif (preg_match($regex_end, $block, $match)) {
				// We just closed a block
				$reference = implode('.', $blocknames);
				$close = array_pop($blocknames);
				// Check if the last opened block is being closed again
				if ($close == $match[1]) {
					$parent = implode('.', $blocknames);
					if ('' != $parent) {
						$this->blocks[$parent] .= $d['BLOCK_OPEN'].$d['BEGIN'].$reference.$d['END'].$d['BLOCK_CLOSE'];
						$this->blocks[$parent] .= $match[2];
					}
				} else {
					throw new Exception('Incorrect block nesting.');
				}
			}

			// This is regular content that just happened to match the BLOCK_OPEN sequence
			else {
				$parent = implode('.', $blocknames);
				if ('' != $parent) {
					$this->blocks[$parent] .= $d['BLOCK_OPEN'].$block;
				}
			}
		}
	}
}

class TemplateFunctions {}
