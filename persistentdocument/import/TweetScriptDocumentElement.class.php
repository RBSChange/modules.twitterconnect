<?php
/**
 * twitterconnect_TweetScriptDocumentElement
 * @package modules.twitterconnect.persistentdocument.import
 */
class twitterconnect_TweetScriptDocumentElement extends import_ScriptDocumentElement
{
	/**
	 * @return twitterconnect_persistentdocument_tweet
	 */
	protected function initPersistentDocument()
	{
		return twitterconnect_TweetService::getInstance()->getNewDocumentInstance();
	}
	
	/**
	 * @return f_persistentdocument_PersistentDocumentModel
	 */
	protected function getDocumentModel()
	{
		return f_persistentdocument_PersistentDocumentModel::getInstanceFromDocumentModelName('modules_twitterconnect/tweet');
	}
}