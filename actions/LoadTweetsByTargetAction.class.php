<?php
/**
 * twitterconnect_LoadTweetsByTargetAction
 * @package modules.twitterconnect.actions
 */
class twitterconnect_LoadTweetsByTargetAction extends f_action_BaseJSONAction
{
	/**
	 * @param Context $context
	 * @param Request $request
	 */
	public function _execute($context, $request)
	{
		$result = array();
		
		$module = $request->getParameter('currentModule');
		$startIndex = $request->hasParameter('startIndex') ? $request->getParameter('startIndex') : 0;
		$pageSize = $request->getParameter('pageSize');
		$result = twitterconnect_ModuleService::getInstance()->getInfosByDocumentId($request->getParameter('relatedId'), $module, $startIndex, $pageSize);
		
		return $this->sendJSON($result);
	}
}