<?php
/**
 * Class where to put your custom methods for document twitterconnect_persistentdocument_account
 * @package modules.twitterconnect.persistentdocument
 */
class twitterconnect_persistentdocument_account extends twitterconnect_persistentdocument_accountbase 
{
	/**
	 * @return f_web_oauth_Consumer
	 */
	public function getConsumer()
	{
		$ms = ModuleService::getInstance();
		return new f_web_oauth_Consumer($ms->getPreferenceValue('twitterconnect', 'consumerKey'), $ms->getPreferenceValue('twitterconnect', 'consumerSecret'));	
	}
	
	/**
	 * @return boolean
	 */
	public function isAuthorized()
	{
		return $this->getAuthorizationDate() !== null;
	}
	
	/**
	 * @return f_web_oauth_Token
	 */
	public function getAccessToken()
	{
		$serToken = parent::getAccessToken();
		if ($serToken !== null)
		{
			return unserialize(parent::getAccessToken());
		}
		return null;
	}
	
	/**
	 * @param f_web_oauth_Token $token
	 */
	public function setAccessToken($token)
	{
		parent::setAccessToken(serialize($token));
	}
}