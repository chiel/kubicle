<?php
/**
 * @author Chiel Kunkels <hello@chielkunkels.com>
 */
class DataSource {
	/**
	 * @var Fieldset
	 */
	protected $fieldset;

	/**
	 *
	 */
	public function setFieldset(&$fieldset)
	{
		if (!$fieldset instanceof Fieldset) {
			throw new ArgumentException('Invalid fieldset provided');
		}

		$this->fieldset = $fieldset;
	}
}
