<?php
/**
 * twitterconnect_PlanTweetsOnPublishAction
 * @package modules.twitterconnect.actions
 */
class twitterconnect_PlanTweetsOnPublishAction extends f_action_BaseJSONAction
{
	/**
	 * @param Context $context
	 * @param Request $request
	 */
	public function _execute($context, $request)
	{
		$result = array();

		$module = $request->getParameter('currentModule');
		$contents = $request->getParameter('contents');
		
		$planner = twitterconnect_OnpublishplannerService::getInstance()->getNewDocumentInstance();
		$planner->setLabel($request->getParameter('label'));
		$planner->setContents($contents);
		foreach (explode(',', $request->getParameter('accounts')) as $accountId)
		{
			$planner->addAccount(DocumentHelper::getDocumentInstance($accountId));
		}
		$planner->setWebsiteId($request->getParameter('websiteId'));
		$planner->setContainerId($request->getParameter('containerId'));
		$planner->setModelName($request->getParameter('model'));
		$planner->setModuleName($module);
		$planner->save();
	
		$pageSize = $request->getParameter('pageSize');
		$result = twitterconnect_ModuleService::getInstance()->getInfosByDocumentId($request->getParameter('containerId'), $module, 0, $pageSize);

		return $this->sendJSON($result);
	}
}