<?php
/**
 * twitterconnect_OnpublishplannerService
 * @package modules.twitterconnect
 */
class twitterconnect_OnpublishplannerService extends twitterconnect_PlannerService
{
	/**
	 * @var twitterconnect_OnpublishplannerService
	 */
	private static $instance;

	/**
	 * @return twitterconnect_OnpublishplannerService
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * @return twitterconnect_persistentdocument_onpublishplanner
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_twitterconnect/onpublishplanner');
	}

	/**
	 * Create a query based on 'modules_twitterconnect/onpublishplanner' model.
	 * Return document that are instance of modules_twitterconnect/onpublishplanner,
	 * including potential children.
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_twitterconnect/onpublishplanner');
	}
	
	/**
	 * Create a query based on 'modules_twitterconnect/onpublishplanner' model.
	 * Only documents that are strictly instance of modules_twitterconnect/onpublishplanner
	 * (not children) will be retrieved
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createStrictQuery()
	{
		return $this->pp->createQuery('modules_twitterconnect/onpublishplanner', false);
	}
	
	/**
	 * @param f_persistentdocument_PersistentDocument $document
	 */
	public function sendTweetsByRelatedDocument($document)
	{
		$ts = twitterconnect_TweetService::getInstance();
		$service = twitterconnect_ModuleService::getInstance()->getServiceForDocument($document);
		if (f_util_ClassUtils::methodExists($service, 'getRelatedForTweets'))
		{
			$related = $service->getRelatedForTweets($document);
			if ($related !== null)
			{
				$this->sendTweetsByRelatedDocument($related);
			}
			return;
		}
		
		if (!f_util_ClassUtils::methodExists($service, 'getContainersForTweets'))
		{
			return;
		}
				
		$containers = $service->getContainersForTweets($document);
		if (f_util_ArrayUtils::isNotEmpty($containers))
		{
			$model = $document->getPersistentModel();
			$modelNames = $document->getPersistentModel()->getAncestorModelNames();
			$modelNames[] = $model->getName();
			$query = $this->createQuery()->add(Restrictions::published());
			$query->add(Restrictions::in('containerId', DocumentHelper::getIdArrayFromDocumentArray($containers)));
			$query->add(Restrictions::in('modelName', $modelNames));
			foreach ($query->find() as $planner)
			{
				$tweetCount = 0;
				$websiteId = $planner->getWebsiteId();
				$label = $this->getReplacedContent($planner, $document, $websiteId);
				foreach ($planner->getPublishedAccountArray() as $account)
				{
					// Do not tweet automatically on a document that has already a tweet on this account.
					if ($ts->hasTweetForDocumentAndAccount($document, $account))
					{
						continue;
					}
					$this->addNewTweet($planner, $account, $document, $label);
					$tweetCount++;
				}
				if ($tweetCount > 0)
				{
					$planner->setLastTweetDate(date_Calendar::getInstance()->toString());
					$planner->save();
				}
			}
		}
	}
}