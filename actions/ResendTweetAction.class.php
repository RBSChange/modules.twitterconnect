<?php
/**
 * twitterconnect_ResendTweetAction
 * @package modules.twitterconnect.actions
 */
class twitterconnect_ResendTweetAction extends f_action_BaseJSONAction
{
	/**
	 * @param Context $context
	 * @param Request $request
	 */
	public function _execute($context, $request)
	{
		$result = array();

		$module = $request->getParameter('currentModule');
		$tweet = $this->getDocumentInstanceFromRequest($request);
		$tweet->getDocumentService()->sendTweet($tweet);
		if ($tweet->getSendingStatus() == twitterconnect_TweetService::STATUS_ERROR)
		{
			$account = $tweet->getAccount()->getLabel();
			return $this->sendJSONError(f_Locale::translate('&modules.twitterconnect.bo.general.error.Error-sending-tweet;', array('account' => $account, 'error' => $tweet->getErrorMessage())));
		}
		
		$startIndex = $request->hasParameter('startIndex') ? $request->getParameter('startIndex') : 0;
		$pageSize = $request->getParameter('pageSize');
		$result = twitterconnect_ModuleService::getInstance()->getInfosByDocumentId($request->getParameter('relatedId'), $module, $startIndex, $pageSize);
		
		return $this->sendJSON($result);
	}
}