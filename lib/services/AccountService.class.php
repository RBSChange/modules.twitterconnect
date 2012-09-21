<?php
/**
 * @package modules.twitterconnect
 * @method twitterconnect_AccountService getInstance()
 */
class twitterconnect_AccountService extends f_persistentdocument_DocumentService
{
	/**
	 * @return twitterconnect_persistentdocument_account
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_twitterconnect/account');
	}

	/**
	 * Create a query based on 'modules_twitterconnect/account' model.
	 * Return document that are instance of modules_twitterconnect/account,
	 * including potential children.
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->getPersistentProvider()->createQuery('modules_twitterconnect/account');
	}
	
	/**
	 * Create a query based on 'modules_twitterconnect/account' model.
	 * Only documents that are strictly instance of modules_twitterconnect/account
	 * (not children) will be retrieved
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createStrictQuery()
	{
		return $this->getPersistentProvider()->createQuery('modules_twitterconnect/account', false);
	}
	
	/**
	 * @param website_persistentdocument_website $website
	 */
	public function getAuthorizedByWebsite($website)
	{
		return $this->createQuery()->add(Restrictions::eq('website', $website))->add(Restrictions::isNotNull('authorizationDate'))->find();
	}

	/**
	 * @param twitterconnect_persistentdocument_account $document
	 * @return void
	 */
	protected function preDelete($document)
	{
		// Delete all tweets related to this account, without removing them from twitter.
		twitterconnect_TweetService::getInstance()->createQuery()->add(Restrictions::eq('accountId', $document->getId()))->delete();
	}

	/**
	 * @param twitterconnect_persistentdocument_account $document
	 * @param string $forModuleName
	 * @param array $allowedSections
	 * @return array
	 */
	public function getResume($document, $forModuleName, $allowedSections = null)
	{
		$resume = parent::getResume($document, $forModuleName, $allowedSections);
		
		$resume['properties']['authorized'] = LocaleService::getInstance()->trans('f.boolean.' . ($document->isAuthorized() ? 'true' : 'false'));
		
		return $resume;
	}
}