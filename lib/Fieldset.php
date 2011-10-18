<?php
/**
 * @author Chiel Kunkels <hello@chielkunkels.com>
 */
class Fieldset {
	/**
	 * @var array $fields   The fields
	 */
	private $fields = array();
	/**
	 * @var
	 */
	private $dataSource;

	/**
	 * Add a new field to the set
	 */
	public function add($type, $name, $multiLang = false, $options = null)
	{
		switch ($type) {
			case 'text':
				$field = new TextField($name, $multiLang, $options);
				break;
			case 'textarea':
				$field = new TextAreaField($name, $multiLang, $options);
				break;
			default:
				throw new ArgumentException('Unsupported field type <strong>'.$type.'</strong>');
		}
		$this->fields[] = $field;
	}

	/**
	 *
	 */
	public function getFields()
	{
		return $this->fields;
	}

	/**
	 *
	 */
	public function setDataSource(&$dataSource)
	{
		if (!$dataSource instanceof DataSource) {
			throw new ArgumentException('Invalid DataSource provided');
		}

		$this->dataSource = $dataSource;
	}

	/**
	 *
	 */
	public function isValid()
	{
		if (!$this->dataSource) {
			throw new Exception('No datasource found to validate');
		}

	}
}

/**
 *
 */
class Field {
	/**
	 * @var string   Name of the field
	 */
	protected $name;
	/**
	 * @var boolean   Whether the field is multilingual
	 */
	protected $multiLang = false;
	/**
	 * @var array   Options provided
	 */
	protected $options = array();
	/**
	 * @var string   Non-sanitized raw data
	 */
	protected $rawData;
	/**
	 * @var string   Sanitized data
	 */
	protected $cleanData;
	/**
	 * @var array   Validation errors
	 */
	protected $errors = array();

	/**
	 * Initiate a new field
	 */
	public function __construct($name, $multiLang, $options = null)
	{
		$this->name = $name;
		if ($options && is_array($options)) {
			$this->options = $options;
		}
	}

	/**
	 * Return the field's name
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Renders the field and it's label and returns them
	 */
	public function render()
	{
		return array($this->renderInput(), $this->renderLabel());
	}

	/**
	 *
	 */
	protected function renderLabel()
	{
		$label = '';
		if (isset($this->options['label'])) {
			$label = '<label>'.$this->options['label'].'</label>';
		}
		return $label;
	}

	/**
	 *
	 */
	protected function renderInput()
	{
		$input = '<input name="'.$this->name.'"';
		$input.= '>';
		return $input;
	}
}

/**
 *
 */
class TextField extends Field {}

/**
 *
 */
class TextAreaField extends Field {
	/**
	 *
	 */
	protected function renderInput()
	{
		$input = '<textarea cols="50" rows="20" name="'.$this->name.'"';
		$input.= '></textarea>';
		return $input;
	}
}
