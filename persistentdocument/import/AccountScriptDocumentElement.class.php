<?php
/**
 * twitterconnect_AccountScriptDocumentElement
 * @package modules.twitterconnect.persistentdocument.import
 */
class twitterconnect_AccountScriptDocumentElement extends import_ScriptDocumentElement
{
    /**
     * @return twitterconnect_persistentdocument_account
     */
    protected function initPersistentDocument()
    {
    	return twitterconnect_AccountService::getInstance()->getNewDocumentInstance();
    }
    
    /**
	 * @return f_persistentdocument_PersistentDocumentModel
	 */
	protected function getDocumentModel()
	{
		return f_persistentdocument_PersistentDocumentModel::getInstanceFromDocumentModelName('modules_twitterconnect/account');
	}
}