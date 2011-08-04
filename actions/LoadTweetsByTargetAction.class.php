<?php
/**
 * twitterconnect_LoadTweetsByTargetAction
 * @package modules.twitterconnect.actions
 */
class twitterconnect_LoadTweetsByTargetAction extends change_JSONAction
{
	/**
	 * @param change_Context $context
	 * @param change_Request $request
	 */
	public function _execute($context, $request)
	{
		$result = array();
		try 
		{
			$module = $request->getParameter('currentModule');
			$startIndex = $request->hasParameter('startIndex') ? $request->getParameter('startIndex') : 0;
			$pageSize = $request->getParameter('pageSize');
			$result = twitterconnect_ModuleService::getInstance()->getInfosByDocumentId($request->getParameter('relatedId'), $module, $startIndex, $pageSize);
		}
		catch (Exception $e)
		{
			return $this->sendJSONException($e);
		}
		return $this->sendJSON($result);
	}
}