<?php
/**
 * @author Chiel Kunkels <hello@chielkunkels.com>
 */
class Controller {
	/**
	 * @var int   Item ID being edited
	 */
	protected static $id = 0;
	/**
	 * @var Fieldset   Fieldset for this module
	 */
	protected static $fieldset;
	/**
	 * @var Template   Template object
	 */
	protected static $template;

	/**
	 * View for creating a new entry
	 */
	public static function index($action)
	{
		self::defineFieldset();
		self::$id = Route::param(2, 0);
		self::$template = new Template(VIEW.'/modules/tag.html');

		switch ($action) {
			case 'index':
				self::_index();
				break;
			case 'new':
				self::_new();
				break;
			case 'view':
				self::_view();
				break;
			case 'edit':
				self::_edit();
				break;
			case 'delete':
				self::_delete();
				break;
			default:
				throw new ArgumentException('Unsupported action <strong>'.$action.'</strong> for module '.get_called_class());
		}
	}

	/**
	 * Default fieldset
	 */
	protected static function defineFieldset()
	{
		self::$fieldset = new Fieldset();
		self::$fieldset->add('text', 'title', false, array('label' => 'Title'));
		self::$fieldset->add('text', 'slug', false, array('label' => 'Slug'));
		self::$fieldset->add('textarea', 'description', false, array('label' => 'Description'));
	}

	/**
	 * Default list view
	 */
	protected static function _index()
	{
		dump('show list view');
	}

	/**
	 * Default form
	 */
	protected static function _new()
	{
		$form = new Form('tag', '/admin/tag/new');
		$form->setFieldset(self::$fieldset);
		if ($form->isSubmitted()) {
			$form->setDataSource(new PostData());
			if ($form->isValid()) {
				// save here
			}
		} else {
			$form->render(self::$template);
		}
	}

	/**
	 * Default detail view
	 */
	protected static function _view()
	{
		dump('show detail view');
	}

	/**
	 * Default edit form
	 */
	protected static function _edit()
	{
		$form = new Form('tag', '/admin/tag/save');
		$form->setFieldset(self::$fieldset);
		if ($form->isSubmitted()) {
			$form->setDataSource(new PostData());
			if ($form->isValid()) {
				// save here
			}
		} else {
			$form->setDataSource(new MysqlData('tag'));
			$form->render(self::$template);
		}
	}

	/**
	 * Default delete action
	 */
	protected static function _delete()
	{
		//
	}
}
