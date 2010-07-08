<?php
/**
 * twitterconnect_PeriodicplannerScriptDocumentElement
 * @package modules.twitterconnect.persistentdocument.import
 */
class twitterconnect_PeriodicplannerScriptDocumentElement extends import_ScriptDocumentElement
{
    /**
     * @return twitterconnect_persistentdocument_periodicplanner
     */
    protected function initPersistentDocument()
    {
    	return twitterconnect_PeriodicplannerService::getInstance()->getNewDocumentInstance();
    }
    
    /**
	 * @return f_persistentdocument_PersistentDocumentModel
	 */
	protected function getDocumentModel()
	{
		return f_persistentdocument_PersistentDocumentModel::getInstanceFromDocumentModelName('modules_twitterconnect/periodicplanner');
	}
}