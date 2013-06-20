<?php
/**
 * twitterconnect_SendTweetAction
 * @package modules.twitterconnect.actions
 */
class twitterconnect_SendTweetAction extends f_action_BaseJSONAction
{
	/**
	 * @param Context $context
	 * @param Request $request
	 * @return null
	 */
	public function _execute($context, $request)
	{
		$errors = array();

		$tms = twitterconnect_ModuleService::getInstance();
		$module = $request->getParameter('currentModule');
		$contents = $request->getParameter('contents');
		foreach (explode(',', $request->getParameter('accounts')) as $accountId)
		{
			$account = DocumentHelper::getDocumentInstance($accountId);
			$oauthRequest = new f_web_oauth_Request('https://api.twitter.com/1.1/statuses/update.' . $tms->getResultFormat(), $account->getConsumer(), f_web_oauth_Request::METHOD_POST);
			$oauthRequest->setParameter('status', $contents);
			$oauthRequest->setToken($account->getAccessToken());
			$client = new f_web_oauth_HTTPClient($oauthRequest);
			$client->getBackendClientInstance()->setTimeOut(0);
			$infos = $tms->parseTwitterResult($client->execute());
			if (array_key_exists('error', $infos))
			{
				$errors[] = LocaleService::getInstance()->trans('m.twitterconnect.bo.general.error.error-sending-tweet', array('ucf'), array('account' => $account->getLabel(), 'error' => $infos['error']));
			}
			else
			{
				$tweetId = $infos['id_str'];
				$tweet = twitterconnect_TweetService::getInstance()->getNewDocumentInstance();
				$tweet->setLabel($contents);
				$tweet->setAccountId($account->getId());
				$tweet->setWebsiteId($request->getParameter('websiteId'));
				$tweet->setRelatedId($request->getParameter('relatedId'));
				$tweet->setSendingStatus(twitterconnect_TweetService::STATUS_SENT);
				$tweet->setSendingInfos($infos);
				$tweet->setSendingDate(date_Calendar::getInstance()->toString());
				$tweet->setTweetId($tweetId);
				$tweet->save();
			} 
		}
		
		if (count($errors) > 0)
		{
			return $this->sendJSONError(implode("\n", $errors));
		}
		
		$pageSize = $request->getParameter('pageSize');
		$result = twitterconnect_ModuleService::getInstance()->getInfosByDocumentId($request->getParameter('relatedId'), $module, 0, $pageSize);
		
		return $this->sendJSON($result);
	}
}