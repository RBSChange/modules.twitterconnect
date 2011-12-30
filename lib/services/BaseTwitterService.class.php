<?php
/**
 * twitterconnect_BaseTwitterService
 * @package modules.twitterconnect
 */
abstract class twitterconnect_BaseTwitterService extends BaseService
{
	// Methods to make a tweet from the document.
	
	/**
	 * @see twitterconnect_ModuleService::getReplacementsByRelatedDocument()
	 * 
	 * @param f_persistentdocument_PersistentDocument $document or null
	 * @param integer $websiteId
	 * @return array
	 */
	public function getReplacementsForTweet($document, $websiteId)
	{
		$shortUrl = array(
			'name' => 'shortUrl',
			'label' => f_Locale::translateUI('&modules.twitterconnect.bo.general.Short-url;'),
			'maxLength' => 30
		);
		if ($document !== null)
		{
			$shortUrl['value'] = website_ShortenUrlService::getInstance()->shortenUrl(LinkHelper::getDocumentUrl($document));
		}
		return array($shortUrl);
	}
	
	//
	// Following methods may be implemented in the TwitterService of a document or in its DocumentService. 
	//
	// /!\ Please, do not delete these comments...
	//
	
	/**
	 * Implement this method if the document is not located in a single website.
	 * @see For example: catalog_ProductService::getWebsitesForTweets()
	 * @see twitterconnect_ModuleService::getInfosByDocumentId()
	 * 
	 * @param f_persistentdocument_PersistentDocument $document
	 * @return website_persistentdocument_website[]
	 */
//	public function getWebsitesForTweets($document)
//	{
//		$websites = array();
//
//		// Your code here.
//
//		return $websites;
//	}

	/**
	 * Implement this method to specialize the application of the "tweet on publish" meta.
	 * @see For example: catalog_ProductService::setTweetOnPublishMeta()
	 * 
	 * @see twitterconnect_TweetService::setTweetOnPublishMeta()
	 * @param f_persistentdocument_PersistentDocument $document
	 * @param integer $websiteId
	 */
//	public function setTweetOnPublishMeta($document, $websiteId)
//	{
//	}

	/**
	 * Implement this method to specialize the removal of the "tweet on publish" meta.
	 * @see For example: catalog_ProductService::removeTweetOnPublishMeta()
	 * 
	 * @see twitterconnect_TweetService::removeTweetOnPublishMeta()
	 * @param f_persistentdocument_PersistentDocument $document
	 * @param integer $websiteId
	 */
//	public function removeTweetOnPublishMeta($document, $websiteId)
//	{
//	}

	/**
	 * Implement this method if you enable planner on a container of this document.
	 * 
	 * @param f_persistentdocument_PersistentDocument $document
	 * @return f_persistentdocument_PersistentDocument[]
	 */
//	public function getContainersForTweets($document)
//	{
//	}

	/**
	 * Implement this method to specify the document that should be tweeted if this document is published.
	 * @see For example: catalog_CompiledproductService::getRelatedForTweets()
	 * 
	 * @see twitterconnect_OnpublishplannerService::sendTweetsByRelatedDocument()
	 * @param f_persistentdocument_PersistentDocument $document
	 * @return f_persistentdocument_PersistentDocument
	 */
//	public function getRelatedForTweets($document)
//	{
//	}

	// Methods to make planned tweets on contained documents. 
	
	/**
	 * Implement this method to be able to plane tweets on contained documents.
	 *  
	 * @param f_persistentdocument_PersistentDocument $document
	 * @return string[]
	 */
//	public function getDocumentsModelNamesForTweet($document)
//	{
//		// return array('modules_mymodule/mydocument1', 'modules_mymodule/mydocument2');
//	}

	/**
	 * Implement this method returning false when the document can't send tweet on publication of contained documents.
	 * 
	 * @see twitterconnect_PlannerService::getContainerInfosById()
	 * @param f_persistentdocument_PersistentDocument $document
	 * @return boolean
	 */
//	public function canSendTweetOnContainedDocumentPublish($document)
//	{
//		return false;
//	}

	/**
	 * Implement this method to be able to plane tweets on contained documents.
	 * 
	 * @see twitterconnect_PlannerService::getContainerInfosById()
	 * @see twitterconnect_PeriodicplannerService::sendTweetsByPlanner()
	 * @param f_persistentdocument_PersistentDocument $document
	 * @param string $modelName
	 * @return integer[]
	 */
//	public function getContainedIdsForTweet($document, $modelName)
//	{
//	}
}