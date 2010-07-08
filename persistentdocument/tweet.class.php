<?php
/**
 * Class where to put your custom methods for document twitterconnect_persistentdocument_tweet
 * @package modules.twitterconnect.persistentdocument
 */
class twitterconnect_persistentdocument_tweet extends twitterconnect_persistentdocument_tweetbase 
{
	/**
	 * @param array $value
	 */
	public function setSendingInfos($value)
	{
		if (is_array($value))
		{
			parent::setSendingInfos(serialize($value));
		}
		else
		{
			parent::setSendingInfos(null);
		}
	}
	
	/**
	 * @return array
	 */
	public function getSendingInfos()
	{
		$value = parent::getSendingInfos();
		return ($value !== null) ? unserialize($value) : null;
	}
	
	/**
	 * @return twitterconnect_persistentdocument_account
	 */
	public function getAccount()
	{
		return DocumentHelper::getDocumentInstance($this->getAccountId());
	}
	
	/**
	 * @return string
	 */
	public function getErrorMessage()
	{
		$infos = $this->getSendingInfos();
		if (is_array($infos) && isset($infos['error']))
		{
			return $infos['error'];
		}
		return null;
	}
	
	/**
	 * @return void
	 */
	public function planNow()
	{
		$this->setSendingDate(date_Calendar::getInstance()->toString());
		$this->setSendingStatus(twitterconnect_TweetService::STATUS_PLANNED);
		$this->save();
	}
	
	/**
	 * @param string $moduleName
	 * @param string $treeType
	 * @param array<string, string> $nodeAttributes
	 */
//	protected function addTreeAttributes($moduleName, $treeType, &$nodeAttributes)
//	{
//	}
	
	/**
	 * @param string $actionType
	 * @param array $formProperties
	 */
//	public function addFormProperties($propertiesNames, &$formProperties)
//	{	
//	}
}