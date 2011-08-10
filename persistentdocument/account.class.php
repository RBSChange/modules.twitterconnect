<?php
/**
 * Class where to put your custom methods for document twitterconnect_persistentdocument_account
 * @package modules.twitterconnect.persistentdocument
 */
class twitterconnect_persistentdocument_account extends twitterconnect_persistentdocument_accountbase 
{
	/**
	 *
	 * @var Zend_Oauth_Consumer 
	 */
	private $consumer = null;
	
	/**
	 * @return Zend_Oauth_Consumer
	 */
	public function getConsumer()
	{
		if ($this->consumer === null)
		{
			$ms = ModuleService::getInstance();
			$config = array('consumerKey' => $ms->getPreferenceValue('twitterconnect', 'consumerKey'),
			'consumerSecret' => $ms->getPreferenceValue('twitterconnect', 'consumerSecret'),
			'callbackUrl' => LinkHelper::getUIParametrizedLink(array('module' => 'twitterconnect', 'action' => 'Authorize', 'cmpref' => $this->getId()))->getUrl(),
			'siteUrl' => 'http://twitter.com/oauth');
			$this->consumer = new Zend_Oauth_Consumer($config);
		}
		return $this->consumer;
	}
	
	/**
	 * @return boolean
	 */
	public function isAuthorized()
	{
		return $this->getAuthorizationDate() !== null;
	}
	
	/**
	 * @return Zend_Oauth_Token_Access
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
	 * @param Zend_Oauth_Token_Access $token
	 */
	public function setAccessToken($token)
	{
		parent::setAccessToken(serialize($token));
	}
}