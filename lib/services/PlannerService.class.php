<?php
/**
 * twitterconnect_PlannerService
 * @package modules.twitterconnect
 */
class twitterconnect_PlannerService extends f_persistentdocument_DocumentService
{
	const TYPE_ON_PUBLISH = 'onpublish';
	const TYPE_PERIODIC = 'periodic';
	
	/**
	 * @var twitterconnect_PlannerService
	 */
	private static $instance;

	/**
	 * @return twitterconnect_PlannerService
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
	 * @return twitterconnect_persistentdocument_planner
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_twitterconnect/planner');
	}

	/**
	 * Create a query based on 'modules_twitterconnect/planner' model.
	 * Return document that are instance of modules_twitterconnect/planner,
	 * including potential children.
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_twitterconnect/planner');
	}
	
	/**
	 * Create a query based on 'modules_twitterconnect/planner' model.
	 * Only documents that are strictly instance of modules_twitterconnect/planner
	 * (not children) will be retrieved
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createStrictQuery()
	{
		return $this->pp->createQuery('modules_twitterconnect/planner', false);
	}
	
	/**
	 * @param f_persistentdocument_PersistentDocument $document
	 * @param BaseService $service
	 */
	public function getPlannersInfosByContainerId($document, $service)
	{
		$id = $document->getId();
		$planners = $this->createQuery()->add(Restrictions::eq('containerId', $id))->addOrder(Order::asc('label'))->find();
		if (f_util_ArrayUtils::isEmpty($planners))
		{
			return array('planners' => array());
		}
		
		$plannersInfos = array();
		foreach ($planners as $planner)
		{
			$plannerInfos = array();
			$plannerInfos['documentId'] = $planner->getId();
			$plannerInfos['label'] = $planner->getLabel();
			
			$accountLabels = array();
			foreach ($planner->getAccountArray() as $account)
			{
				$accountLabels[] = $account->getLabel();
			}
			$plannerInfos['accountLabels'] = implode(', ', $accountLabels);
			$plannerInfos['contents'] = $planner->getContents();
			$plannerInfos['plannerTypeLabel'] = $planner->getPlannerTypeLabel();
			$plannerInfos['lastTweetDate'] = '-';
			if ($planner->getUILastTweetDate() !== null)
			{
				$plannerInfos['lastTweetDate'] = date_Formatter::toDefaultDateTimeBO($planner->getUILastTweetDate());
			}
			$plannerInfos['nextTweetDate'] = null;
			if ($planner instanceof twitterconnect_persistentdocument_periodicplanner)
			{
				$plannerInfos['nextTweetDate'] = date_Formatter::toDefaultDateTimeBO($planner->getUINextTweetDate());
			}

			$plannersInfos[] = $plannerInfos;
		}
		return array('planners' => $plannersInfos);
	}
	
	/**
	 * @param f_persistentdocument_PersistentDocument $document
	 * @param BaseService $service
	 * @param integer $websiteId
	 * @return array
	 */
	public function getContainerInfosById($document, $service, $websiteId)
	{
		$allowPeriodic = f_util_ClassUtils::methodExists($service, 'getContainedIdsForTweet');
		$allowOnPublish = (!f_util_ClassUtils::methodExists($service, 'canSendTweetOnContainedDocumentPublish') || $service->canSendTweetOnContainedDocumentPublish($document));
		$infos = array('allowPeriodic' => $allowPeriodic, 'allowOnPublish' => $allowOnPublish);
		
		$modelInfos = $this->getAllowModelInfos($document, $service);
		if (count($modelInfos) == 1)
		{
			$infos['model'] = $modelInfos[0]['name'];
		}
		else if (count($modelInfos) > 1)
		{
			$infos['allowedModels'] = $this->getAllowModelInfos($document, $service);
		}
		
		return $infos;
	}
	
	/**
	 * @param f_persistentdocument_PersistentDocument $document
	 * @param f_persistentdocument_DocumentService $service
	 * @return array
	 */
	private function getAllowModelInfos($document, $service)
	{
		if (!f_util_ClassUtils::methodExists($service, 'getDocumentsModelNamesForTweet'))
		{
			return array();
		}
		
		$result = array();
		$modelNames = $service->getDocumentsModelNamesForTweet($document);
		if (count($modelNames) == 1)
		{
			$result[] = array('name' => f_util_ArrayUtils::firstElement($modelNames));
		}
		else if (count($modelNames) > 1)
		{
			foreach ($modelNames as $modelName)
			{
				$model = f_persistentdocument_PersistentDocumentModel::getInstanceFromDocumentModelName($modelName);
				$result[] = array('name' => $modelName, 'label' => $model->getLabel());
			}
		}
		return $result;
	}
	
	/**
	 * @param twitterconnect_persistentdocument_planner $planner
	 * @param twitterconnect_persistentdocument_account $planner
	 * @param f_persistentdocument_PersistentDocument $document
	 * @param string $label
	 */
	protected function addNewTweet($planner, $account, $document, $label)
	{
		$tweet = twitterconnect_TweetService::getInstance()->getNewDocumentInstance();
		$tweet->setLabel($label);
		$tweet->setAccountId($account->getId());
		$tweet->setWebsiteId($planner->getWebsiteId());
		$tweet->setRelatedId($document->getId());
		$tweet->setContainerId($planner->getContainerId());
		$tweet->setSendingStatus(twitterconnect_TweetService::STATUS_PLANNED);
		$tweet->setSendingDate(date_Calendar::getInstance()->toString());
		$tweet->save();
	}
	
	/**
	 * @param twitterconnect_persistentdocument_planner $planner
	 * @param f_persistentdocument_PersistentDocument $document
	 * @param integer $websiteId
	 */
	protected function getReplacedContent($planner, $document, $websiteId)
	{
		$string = $planner->getContents();
		$replacements = twitterconnect_ModuleService::getInstance()->getReplacementsByRelatedDocument($document, $planner->getModuleName(), $websiteId);
		if (f_util_ArrayUtils::isNotEmpty($replacements))
		{
			foreach ($replacements as $row)
			{
				$string = preg_replace('#{' . $row['name'] . ':[0-9]+}#',	$row['value'], $string);
			}
		}

		// Remove the not-replaced elements.
		$string = preg_replace('#\{(.*)\}#', '-', $string);
		return $string;
	}
}