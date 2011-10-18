<?php
/**
 * @author Chiel Kunkels <hello@chielkunkels.com
 */
class Form {
	/**
	 * @var string
	 */
	private $name = '';
	/**
	 * @var string
	 */
	private $action = '';
	/**
	 * @var string
	 */
	private $method = '';
	/**
	 * @var object
	 */
	private $fieldset = null;
	/**
	 * @var object
	 */
	private $dataSource = null;

	/**
	 * Initiate a new form
	 *
	 * @param string $name   Name of the form, also used in templates
	 * @param string $action   Where the form submits to
	 * @param string $method   What method the form uses
	 */
	public function __construct($name, $action, $method = 'post')
	{
		$method = strtolower($method);

		if (!in_array(strtolower($method), array('get', 'post'))) {
			$method = 'post';
		}

		if (substr($action, 0, 1) === '/') {
			$action = 'http://localhost/kubicle'.$action;
		}
		$this->name = $name;
		$this->action = $action;
		$this->method = $method;
	}

	/**
	 * Set the form's fieldset
	 */
	public function setFieldset(&$fieldset)
	{
		if (!$fieldset instanceof Fieldset) {
			throw new ArgumentException('Invalid fieldset provided');
		}
		$this->fieldset = $fieldset;
	}

	/**
	 * Attach a datasource to the form
	 */
	public function setDataSource(&$dataSource)
	{
		if (!$dataSource instanceof DataSource) {
			throw new ArgumentException('Invalid datasource provided');
		}
		$this->dataSource = $dataSource;
	}

	/**
	 *
	 */
	public function isSubmitted()
	{
		if ($this->method === 'post' && isset($_POST[$this->name.'-submit'])) {
			return true;
		} elseif ($this->method === 'get' && isset($_GET[$this->name.'-submit'])) {
			return true;
		}
		return false;
	}

	/**
	 *
	 */
	public function isValid()
	{
		if (!$this->fieldset) {
			throw new Exception('No fieldset defined');
		}
		if (!$this->dataSource) {
			throw new Exception('No datasource available to check');
		}

		$this->fieldset->setDataSource($this->dataSource);
		return $this->fieldset->isValid();
	}

	/**
	 *
	 */
	public function render($template = null)
	{
		if (!$template) {
			global $template;
		}

		if(!$template || !$template instanceof Template) {
			throw new ArgumentException('No valid template found to parse to');
		}

		if (!$this->fieldset) {
			throw new Exception('No fieldset defined');
		}

		if ($this->dataSource) {
			$this->dataSource->setFieldset($this->fieldset);
		}

		$fields = $this->fieldset->getFields();

		foreach ($fields as $field) {
			list($input, $label ) = $field->render();
			$template->pass($field->getName().':', $label);
			$template->pass($field->getName(), $input);
		}

		$template->pass('form:open', '<form id="form-'.$this->name.'" method="'.$this->method.'" action="'.$this->action.'">');
		$template->pass('form:submit', '<input type="submit" name="'.$this->name.'-submit" value="Submit">');
		$template->pass('form:cancel', 'or <a href="#" class="cancel">Cancel</a>');
		$template->parse('form:'.$this->name);
		$template->render('form:'.$this->name);
	}
}
