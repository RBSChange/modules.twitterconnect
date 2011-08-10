<?php
/**
 * twitterconnect_SendTweetAction
 * @package modules.twitterconnect.actions
 */
class twitterconnect_SendTweetAction extends change_JSONAction
{
	/**
	 * @param change_Context $context
	 * @param change_Request $request
	 */
	public function _execute($context, $request)
	{
		$result = array();
		$errors = array();

		$tms = twitterconnect_ModuleService::getInstance();
		$module = $request->getParameter('currentModule');
		$contents = $request->getParameter('contents');
		foreach (explode(',', $request->getParameter('accounts')) as $accountId)
		{
			$account = DocumentHelper::getDocumentInstance($accountId);
			$token = $account->getAccessToken();
			$ms = ModuleService::getInstance();
			$config = array('consumerKey' => $ms->getPreferenceValue('twitterconnect', 'consumerKey'),
							'consumerSecret' => $ms->getPreferenceValue('twitterconnect', 'consumerSecret'));
			$client = $token->getHttpClient($config, null, Framework::getHttpClientConfig());
			$client->setUri('http://twitter.com/statuses/update.xml');
			$client->setMethod(Zend_Http_Client::POST);
			$client->setParameterPost('status', $contents);
			$response = $client->request();
			$infos = $tms->parseTwitterResult($response->getBody());
			if (array_key_exists('error', $infos))
			{
				$errors[] = f_Locale::translate('&modules.twitterconnect.bo.general.error.Error-sending-tweet;', array('account' => $account->getLabel(), 'error' => $infos['error']));
			}
			else
			{
				$tweetId = $infos['id'];
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