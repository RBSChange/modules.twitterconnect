<?php
class twitterconnect_PreferencesService extends f_persistentdocument_DocumentService
{
	/**
	 * @var twitterconnect_PreferencesService
	 */
	private static $instance;

	/**
	 * @return twitterconnect_PreferencesService
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * @return twitterconnect_persistentdocument_preferences
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_twitterconnect/preferences');
	}

	/**
	 * Create a query based on 'modules_twitterconnect/preferences' model.
	 * Return document that are instance of modules_twitterconnect/preferences,
	 * including potential children.
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->getPersistentProvider()->createQuery('modules_twitterconnect/preferences');
	}
	
	/**
	 * Create a query based on 'modules_twitterconnect/preferences' model.
	 * Only documents that are strictly instance of modules_twitterconnect/preferences
	 * (not children) will be retrieved
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createStrictQuery()
	{
		return $this->getPersistentProvider()->createQuery('modules_twitterconnect/preferences', false);
	}
	
	/**
	 * @param customer_persistentdocument_preferences $document
	 * @param Integer $parentNodeId Parent node ID where to save the document (optionnal => can be null !).
	 * @return void
	 */
	protected function preSave($document, $parentNodeId)
	{
		$document->setLabel('twitterconnect');
	}
}