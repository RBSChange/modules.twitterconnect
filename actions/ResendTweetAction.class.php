<?php
/**
 * @package modules.twitterconnect
 */
class twitterconnect_ResendTweetAction extends change_JSONAction
{
	/**
	 * @param change_Context $context
	 * @param change_Request $request
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
			return $this->sendJSONError(LocaleService::getInstance()->trans('m.twitterconnect.bo.general.error.error-sending-tweet', array('ucf'), array('account' => $account, 'error' => $tweet->getErrorMessage())));
		}
		
		$startIndex = $request->hasParameter('startIndex') ? $request->getParameter('startIndex') : 0;
		$pageSize = $request->getParameter('pageSize');
		$result = twitterconnect_ModuleService::getInstance()->getInfosByDocumentId($request->getParameter('relatedId'), $module, $startIndex, $pageSize);
		
		return $this->sendJSON($result);
	}
}