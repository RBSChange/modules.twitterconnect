<?php
/**
 * twitterconnect_LoadReplacementsAction
 * @package modules.twitterconnect.actions
 */
class twitterconnect_LoadReplacementsAction extends f_action_BaseJSONAction
{
	/**
	 * @param Context $context
	 * @param Request $request
	 */
	public function _execute($context, $request)
	{
		$result = array();
				
		$tms = twitterconnect_ModuleService::getInstance();
		$module = $request->getParameter('currentModule');
		if ($request->hasParameter('relatedId'))
		{
			$relatedDoc = DocumentHelper::getDocumentInstance($request->getParameter('relatedId'));
			$result = $tms->getReplacementsByRelatedDocument($relatedDoc, $module, $request->getParameter('websiteId'));
		}
		else if ($request->hasParameter('model'))
		{
			$result = $tms->getReplacementsByModelName($request->hasParameter('model'), $module, $request->getParameter('websiteId'));
		}
		
		return $this->sendJSON($result);
	}
}