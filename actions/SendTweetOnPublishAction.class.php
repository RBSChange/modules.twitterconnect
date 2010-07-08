<?php
/**
 * twitterconnect_SendTweetOnPublishAction
 * @package modules.twitterconnect.actions
 */
class twitterconnect_SendTweetOnPublishAction extends f_action_BaseJSONAction
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
		$relatedId = $request->getParameter('relatedId');
		$websiteId = $request->getParameter('websiteId');
		$ts = twitterconnect_TweetService::getInstance();
		foreach (explode(',', $request->getParameter('accounts')) as $accountId)
		{
			$account = DocumentHelper::getDocumentInstance($accountId);
			$tweet = $ts->getNewDocumentInstance();
			$tweet->setLabel($contents);
			$tweet->setAccountId($account->getId());
			$tweet->setWebsiteId($websiteId);
			$tweet->setRelatedId($relatedId);
			$tweet->setSendingStatus(twitterconnect_TweetService::STATUS_PLANNED_ON_PUBLISH);
			$tweet->save();
		}
	
		$pageSize = $request->getParameter('pageSize');
		$result = twitterconnect_ModuleService::getInstance()->getInfosByDocumentId($request->getParameter('relatedId'), $module, 0, $pageSize);
		
		return $this->sendJSON($result);
	}
}