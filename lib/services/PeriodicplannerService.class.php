<?php
/**
 * twitterconnect_PeriodicplannerService
 * @package modules.twitterconnect
 */
class twitterconnect_PeriodicplannerService extends twitterconnect_PlannerService
{
	/**
	 * @var twitterconnect_PeriodicplannerService
	 */
	private static $instance;

	/**
	 * @return twitterconnect_PeriodicplannerService
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
	 * @return twitterconnect_persistentdocument_periodicplanner
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_twitterconnect/periodicplanner');
	}

	/**
	 * Create a query based on 'modules_twitterconnect/periodicplanner' model.
	 * Return document that are instance of modules_twitterconnect/periodicplanner,
	 * including potential children.
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_twitterconnect/periodicplanner');
	}
	
	/**
	 * Create a query based on 'modules_twitterconnect/periodicplanner' model.
	 * Only documents that are strictly instance of modules_twitterconnect/periodicplanner
	 * (not children) will be retrieved
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createStrictQuery()
	{
		return $this->pp->createQuery('modules_twitterconnect/periodicplanner', false);
	}
	
	/**
	 * Send tweets for all published periodic planers.
	 */
	public function sendTweets()
	{
		$query = $this->createQuery()->add(Restrictions::published())->add(Restrictions::le('nextTweetDate', date_Calendar::getInstance()->toString()));
		foreach ($query->find() as $planner)
		{
			$this->sendTweetsByPlanner($planner);
			$planner->setLastTweetDate(date_Calendar::getInstance()->toString());
			$planner->save();
		}
	}
	
	/**
	 * @param twitterconnect_persistentdocument_planner $planner
	 */
	public function sendTweetsByPlanner($planner)
	{
		$container = DocumentHelper::getDocumentInstance($planner->getContainerId());
		$service = twitterconnect_ModuleService::getInstance()->getServiceForDocument($container, $planner->getModuleName());
		if (!f_util_ClassUtils::methodExists($service, 'getContainedIdsForTweet'))
		{
			return;
		}
		$ids = $service->getContainedIdsForTweet($container, $planner->getModelName());
		foreach ($planner->getPublishedAccountArray() as $account)
		{
			$alreadyTweetedIds = twitterconnect_TweetService::getInstance()->getAlreadyTweetedPublishedIdsByModelName($planner->getModelName(), $account);
			$ids = array_values(array_diff($ids, $alreadyTweetedIds));
			
			$idsCount = count($ids);
			if ($idsCount > 0)
			{
				$id = $ids[mt_rand(0, $idsCount - 1)];
				$document = DocumentHelper::getDocumentInstance($id, $planner->getModelName());
				$websiteId = $planner->getWebsiteId();
				$label = $this->getReplacedContent($planner, $document, $websiteId);
				$this->addNewTweet($planner, $account, $document, $label);
			}
		}
	}
	
	/**
	 * @param twitterconnect_persistentdocument_periodicplanner $document
	 * @param Integer $parentNodeId Parent node ID where to save the document (optionnal => can be null !).
	 * @return void
	 */
	protected function preSave($document, $parentNodeId = null)
	{
		parent::preSave($document, $parentNodeId);

		$this->refreshNextTweetDate($document);
	}
	
	/**
	 * @param twitterconnect_persistentdocument_periodicplanner $document
	 */
	private function refreshNextTweetDate($document)
	{
		$last = $document->getLastTweetDate();
		if ($last !== null)
		{
			$next = date_Calendar::getInstance($last);
			$period = $document->getPeriod();
			$value = substr($period, 0, -1);
			$unit = substr($period, -1);
			switch ($unit)
			{
				case 'h': $next->add(date_Calendar::HOUR, $value); break;
				case 'd': $next->add(date_Calendar::DAY, $value); break;
				case 'w': $next->add(date_Calendar::DAY, $value*7); break;
				case 'm': $next->add(date_Calendar::MONTH, $value); break;
				case 'y': $next->add(date_Calendar::YEAR, $value); break;
			}
		}
		else
		{
			$next = date_Calendar::getInstance();
		}
		$document->setNextTweetDate($next->toString());
	}
}