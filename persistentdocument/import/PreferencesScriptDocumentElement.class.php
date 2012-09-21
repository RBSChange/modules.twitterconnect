<?php
/**
 * twitterconnect_PreferencesScriptDocumentElement
 * @package modules.twitterconnect.persistentdocument.import
 */
class twitterconnect_PreferencesScriptDocumentElement extends import_ScriptDocumentElement
{
	/**
	 * @return twitterconnect_persistentdocument_preferences
	 */
	protected function initPersistentDocument()
	{
		return twitterconnect_PreferencesService::getInstance()->getNewDocumentInstance();
	}
	
	/**
	 * @return f_persistentdocument_PersistentDocumentModel
	 */
	protected function getDocumentModel()
	{
		return f_persistentdocument_PersistentDocumentModel::getInstanceFromDocumentModelName('modules_twitterconnect/preferences');
	}
}