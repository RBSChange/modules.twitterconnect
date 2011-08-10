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
			$token = $oauthConsumer->getAccessToken(
                 $_GET,
                 unserialize($account->getMeta('oauth.token'))
             );
			$account->setAccesstoken($token);
			$account->setAuthorizationdate(date_Calendar::now());
			$account->save();
			echo '<html><head><script type="text/javascript">window.close();</script></head><body></body></html>';
			return change_View::NONE;
		}
		else
		{
			$token = $oauthConsumer->getRequestToken();
			$account->setMeta('oauth.token', serialize($token));
			$account->saveMeta();
			$oauthConsumer->redirect();
		}
		return change_View::NONE;
	}
}