<?php
/**
 * twitterconnect_PlannerScriptDocumentElement
 * @package modules.twitterconnect.persistentdocument.import
 */
class twitterconnect_PlannerScriptDocumentElement extends import_ScriptDocumentElement
{
    /**
     * @return twitterconnect_persistentdocument_planner
     */
    protected function initPersistentDocument()
    {
    	return twitterconnect_PlannerService::getInstance()->getNewDocumentInstance();
    }
    
    /**
	 * @return f_persistentdocument_PersistentDocumentModel
	 */
	protected function getDocumentModel()
	{
		return f_persistentdocument_PersistentDocumentModel::getInstanceFromDocumentModelName('modules_twitterconnect/planner');
	}
}