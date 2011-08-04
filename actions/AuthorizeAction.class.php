<?php
/**
 * twitterconnect_AuthorizeAction
 * @package modules.twitterconnect.actions
 */
class twitterconnect_AuthorizeAction extends change_Action
{
	/**
	 * @param change_Context $context
	 * @param change_Request $request
	 */
	public function _execute($context, $request)
	{
		$account = $this->getDocumentInstanceFromRequest($request);
		$oauthConsumer = $account->getConsumer();
		if ($request->hasParameter('oauth_token') && $request->hasParameter('oauth_verifier'))
		{
			// We have an authorized token, let's try to convert it into an access token
			$oauthRequest = new f_web_oauth_Request('https://api.twitter.com/oauth/access_token', $oauthConsumer);
			$oauthRequest->setParameter('oauth_verifier', $request->getParameter('oauth_verifier'));
			$token = unserialize($account->getMeta('oauth.token'));
			$oauthRequest->setToken($token);
			$oauthRequest->sign();
			$url = $oauthRequest->getUrl(true);
			$client = HTTPClientService::getInstance()->getNewHTTPClient();
			$data = $client->get($url);
			if ($client->getHTTPReturnCode() == 200)
			{
				$variables = array();
				parse_str($data, $variables);
				if (isset($variables['oauth_token']) && isset($variables['oauth_token_secret']))
				{
					$token = new f_web_oauth_Token($variables['oauth_token'], $variables['oauth_token_secret']);
					$account->setAccesstoken($token);
					$account->setAuthorizationdate(date_Calendar::now());
					$account->save();
				}
				echo '<html><head><script type="text/javascript">window.close();</script></head><body></body></html>';
			}
			else
			{
				echo $data;
				return change_View::NONE;
			}
			return change_View::NONE;
		}
		else
		{
			$oauthRequest = new f_web_oauth_Request('http://api.twitter.com/oauth/request_token', $oauthConsumer);
			$oauthRequest->setParameter('oauth_callback', LinkHelper::getUIParametrizedLink(array('module' => 'twitterconnect', 'action' => 'Authorize', 'cmpref' => $account->getId()))->getUrl());
			$oauthRequest->sign();
			$url = $oauthRequest->getUrl(true);
			$client = HTTPClientService::getInstance()->getNewHTTPClient();
			$data = $client->get($url);
			if ($client->getHTTPReturnCode() == 200)
			{
				$variables = array();
				parse_str($data, $variables);
				if (isset($variables['oauth_token']) && isset($variables['oauth_token_secret']))
				{
					$token = new f_web_oauth_Token($variables['oauth_token'], $variables['oauth_token_secret']);
					$oauthRequest = new f_web_oauth_Request('http://api.twitter.com/oauth/authorize', $oauthConsumer);
					$oauthRequest->setToken($token);
					$oauthRequest->sign();
					$account->setMeta('oauth.token', serialize($token));
					$account->saveMeta();
					header("Location: " . $oauthRequest->getUrl(true));
					exit();
				}
			}
			else
			{
				echo $data;
				return change_View::NONE;
			}
		}
		return change_View::NONE;
	}
}