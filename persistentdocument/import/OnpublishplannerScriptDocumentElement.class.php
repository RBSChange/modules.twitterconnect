<?php
/**
 * twitterconnect_OnpublishplannerScriptDocumentElement
 * @package modules.twitterconnect.persistentdocument.import
 */
class twitterconnect_OnpublishplannerScriptDocumentElement extends import_ScriptDocumentElement
{
	/**
	 * @return twitterconnect_persistentdocument_onpublishplanner
	 */
	protected function initPersistentDocument()
	{
		return twitterconnect_OnpublishplannerService::getInstance()->getNewDocumentInstance();
	}
	
	/**
	 * @return f_persistentdocument_PersistentDocumentModel
	 */
	protected function getDocumentModel()
	{
		return f_persistentdocument_PersistentDocumentModel::getInstanceFromDocumentModelName('modules_twitterconnect/onpublishplanner');
	}
}