<?php
/**
 * @package modules.twitterconnect.lib.listeners
 */
class twitterconnect_PublishListener
{
	/**
	 * @param f_persistentdocument_DocumentService $sender
	 * @param array $params
	 */
	public function onPersistentDocumentPublished($sender, $params)
	{
		$document = $params['document'];
		if ($document->hasMeta(twitterconnect_TweetService::META_TWEET_ON_PUBLISH))
		{
			$websiteId = $document->getDocumentService()->getWebsiteId($document);
			$relatedId = $document->getMeta(twitterconnect_TweetService::META_TWEET_ON_PUBLISH);
			twitterconnect_TweetService::getInstance()->sendTweetsPlannedOnPublishByRelatedDocumentId($relatedId, $websiteId);
		}
		twitterconnect_OnpublishplannerService::getInstance()->sendTweetsByRelatedDocument($document);
	}
}