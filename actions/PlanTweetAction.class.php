<?php
/**
 * twitterconnect_PlanTweetAction
 * @package modules.twitterconnect.actions
 */
class twitterconnect_PlanTweetAction extends f_action_BaseJSONAction
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
		foreach (explode(',', $request->getParameter('accounts')) as $accountId)
		{
			$account = DocumentHelper::getDocumentInstance($accountId);
			$tweet = twitterconnect_TweetService::getInstance()->getNewDocumentInstance();
			$tweet->setLabel($contents);
			$tweet->setAccountId($account->getId());
			$tweet->setWebsiteId($request->getParameter('websiteId'));
			$tweet->setRelatedId($request->getParameter('relatedId'));
			$tweet->setSendingStatus(twitterconnect_TweetService::STATUS_PLANNED);
			$tweet->setSendingDate(date_Converter::convertDateToGMT($request->getParameter('plannedDate')));
			$tweet->save();
		}
	
		$pageSize = $request->getParameter('pageSize');
		$result = twitterconnect_ModuleService::getInstance()->getInfosByDocumentId($request->getParameter('relatedId'), $module, 0, $pageSize);
		
		return $this->sendJSON($result);
	}
}