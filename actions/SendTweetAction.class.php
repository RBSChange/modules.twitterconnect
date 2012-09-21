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
		
		$ls = LocaleService::getInstance();
		$tms = twitterconnect_ModuleService::getInstance();
		$module = $request->getParameter('currentModule');
		$contents = $request->getParameter('contents');
		Framework::fatal(__METHOD__ . ' ' . $contents);
		foreach (explode(',', $request->getParameter('accounts')) as $accountId)
		{
			$account = twitterconnect_persistentdocument_account::getInstanceById($accountId);
			$token = $account->getAccessToken();
			$ms = ModuleService::getInstance();
			$config = array('consumerKey' => $ms->getPreferenceValue('twitterconnect', 'consumerKey'),
							'consumerSecret' => $ms->getPreferenceValue('twitterconnect', 'consumerSecret'));
			$client = $token->getHttpClient($config, null, Framework::getHttpClientConfig());
			$client->setUri('http://twitter.com/statuses/update.xml');
			$client->setMethod(\Zend\Http\Request::METHOD_POST);
			$client->setParameterPost(array('status' => $contents));
			$response = $client->send();
			$infos = $tms->parseTwitterResult($response->getBody());
			if (array_key_exists('error', $infos))
			{
				$params = array('account' => $account->getLabel(), 'error' => $infos['error']);
				$errors[] = $ls->trans('m.twitterconnect.bo.general.error.error-sending-tweet', array('ucf'), $params);
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