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
			self::$instance = self::getServiceClassInstance(get_class());
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
		$dateTimeFormat = f_Locale::translateUI('&modules.uixul.bo.datePicker.calendar.dataWriterTimeFormat;');
		
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
				$plannerInfos['lastTweetDate'] = date_DateFormat::format($planner->getUILastTweetDate(), $dateTimeFormat);
			}
			$plannerInfos['nextTweetDate'] = null;
			if ($planner instanceof twitterconnect_persistentdocument_periodicplanner)
			{
				$plannerInfos['nextTweetDate'] = date_DateFormat::format($planner->getUINextTweetDate(), $dateTimeFormat);
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
		$ms = twitterconnect_ModuleService::getInstance();
		
		$allowPeriodic = f_util_ClassUtils::methodExists($service, 'getContainedIdsForTweet');
		$allowOnPublish = (!f_util_ClassUtils::methodExists($service, 'canSendTweetOnContainedDocumentPublish') || $service->canSendTweetOnContainedDocumentPublish($document));
		$infos = array('allowPeriodic' => $allowPeriodic, 'allowOnPublish' => $allowOnPublish);
		
		$modelInfos = $this->getAllowModelInfos($document, $service);
		if (count($modelInfos) == 1)
		{
			$infos['model'] = $modelInfos[0]['name'];
			$infos['replacements'] = $ms->getReplacementsByModelName($infos['model'], null, $websiteId);
		}
		else if (count($modelInfos) > 1)
		{
			$infos['allowedModels'] = $this->getAllowModelInfos($document, $service);
			$infos['replacements'] = array();
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
	
	/**
	 * @param twitterconnect_persistentdocument_planner $document
	 * @param Integer $parentNodeId Parent node ID where to save the document (optionnal => can be null !).
	 * @return void
	 */
//	protected function preSave($document, $parentNodeId = null)
//	{
//
//	}

	/**
	 * @param twitterconnect_persistentdocument_planner $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
//	protected function preInsert($document, $parentNodeId = null)
//	{
//	}

	/**
	 * @param twitterconnect_persistentdocument_planner $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
//	protected function postInsert($document, $parentNodeId = null)
//	{
//	}

	/**
	 * @param twitterconnect_persistentdocument_planner $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
//	protected function preUpdate($document, $parentNodeId = null)
//	{
//	}

	/**
	 * @param twitterconnect_persistentdocument_planner $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
//	protected function postUpdate($document, $parentNodeId = null)
//	{
//	}

	/**
	 * @param twitterconnect_persistentdocument_planner $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
//	protected function postSave($document, $parentNodeId = null)
//	{
//	}

	/**
	 * @param twitterconnect_persistentdocument_planner $document
	 * @return void
	 */
//	protected function preDelete($document)
//	{
//	}

	/**
	 * @param twitterconnect_persistentdocument_planner $document
	 * @return void
	 */
//	protected function preDeleteLocalized($document)
//	{
//	}

	/**
	 * @param twitterconnect_persistentdocument_planner $document
	 * @return void
	 */
//	protected function postDelete($document)
//	{
//	}

	/**
	 * @param twitterconnect_persistentdocument_planner $document
	 * @return void
	 */
//	protected function postDeleteLocalized($document)
//	{
//	}

	/**
	 * @param twitterconnect_persistentdocument_planner $document
	 * @return boolean true if the document is publishable, false if it is not.
	 */
//	public function isPublishable($document)
//	{
//		$result = parent::isPublishable($document);
//		return $result;
//	}


	/**
	 * Methode à surcharger pour effectuer des post traitement apres le changement de status du document
	 * utiliser $document->getPublicationstatus() pour retrouver le nouveau status du document.
	 * @param twitterconnect_persistentdocument_planner $document
	 * @param String $oldPublicationStatus
	 * @param array<"cause" => String, "modifiedPropertyNames" => array, "oldPropertyValues" => array> $params
	 * @return void
	 */
//	protected function publicationStatusChanged($document, $oldPublicationStatus, $params)
//	{
//	}

	/**
	 * Correction document is available via $args['correction'].
	 * @param f_persistentdocument_PersistentDocument $document
	 * @param Array<String=>mixed> $args
	 */
//	protected function onCorrectionActivated($document, $args)
//	{
//	}

	/**
	 * @param twitterconnect_persistentdocument_planner $document
	 * @param String $tag
	 * @return void
	 */
//	public function tagAdded($document, $tag)
//	{
//	}

	/**
	 * @param twitterconnect_persistentdocument_planner $document
	 * @param String $tag
	 * @return void
	 */
//	public function tagRemoved($document, $tag)
//	{
//	}

	/**
	 * @param twitterconnect_persistentdocument_planner $fromDocument
	 * @param f_persistentdocument_PersistentDocument $toDocument
	 * @param String $tag
	 * @return void
	 */
//	public function tagMovedFrom($fromDocument, $toDocument, $tag)
//	{
//	}

	/**
	 * @param f_persistentdocument_PersistentDocument $fromDocument
	 * @param twitterconnect_persistentdocument_planner $toDocument
	 * @param String $tag
	 * @return void
	 */
//	public function tagMovedTo($fromDocument, $toDocument, $tag)
//	{
//	}

	/**
	 * Called before the moveToOperation starts. The method is executed INSIDE a
	 * transaction.
	 *
	 * @param f_persistentdocument_PersistentDocument $document
	 * @param Integer $destId
	 */
//	protected function onMoveToStart($document, $destId)
//	{
//	}

	/**
	 * @param twitterconnect_persistentdocument_planner $document
	 * @param Integer $destId
	 * @return void
	 */
//	protected function onDocumentMoved($document, $destId)
//	{
//	}

	/**
	 * this method is call before saving the duplicate document.
	 * If this method not override in the document service, the document isn't duplicable.
	 * An IllegalOperationException is so launched.
	 *
	 * @param twitterconnect_persistentdocument_planner $newDocument
	 * @param twitterconnect_persistentdocument_planner $originalDocument
	 * @param Integer $parentNodeId
	 *
	 * @throws IllegalOperationException
	 */
//	protected function preDuplicate($newDocument, $originalDocument, $parentNodeId)
//	{
//		throw new IllegalOperationException('This document cannot be duplicated.');
//	}

	/**
	 * this method is call after saving the duplicate document.
	 * $newDocument has an id affected.
	 * Traitment of the children of $originalDocument.
	 *
	 * @param twitterconnect_persistentdocument_planner $newDocument
	 * @param twitterconnect_persistentdocument_planner $originalDocument
	 * @param Integer $parentNodeId
	 *
	 * @throws IllegalOperationException
	 */
//	protected function postDuplicate($newDocument, $originalDocument, $parentNodeId)
//	{
//	}

	/**
	 * Returns the URL of the document if has no URL Rewriting rule.
	 *
	 * @param twitterconnect_persistentdocument_planner $document
	 * @param string $lang
	 * @param array $parameters
	 * @return string
	 */
//	public function generateUrl($document, $lang, $parameters)
//	{
//	}

	/**
	 * @param twitterconnect_persistentdocument_planner $document
	 * @return integer | null
	 */
//	public function getWebsiteId($document)
//	{
//	}

	/**
	 * @param twitterconnect_persistentdocument_planner $document
	 * @return website_persistentdocument_page | null
	 */
//	public function getDisplayPage($document)
//	{
//	}

	/**
	 * @param twitterconnect_persistentdocument_planner $document
	 * @param string $forModuleName
	 * @param array $allowedSections
	 * @return array
	 */
//	public function getResume($document, $forModuleName, $allowedSections = null)
//	{
//		$resume = parent::getResume($document, $forModuleName, $allowedSections);
//		return $resume;
//	}

	/**
	 * @param twitterconnect_persistentdocument_planner $document
	 * @param string $bockName
	 * @return array with entries 'module' and 'template'. 
	 */
//	public function getSolrserachResultItemTemplate($document, $bockName)
//	{
//		return array('module' => 'twitterconnect', 'template' => 'Twitterconnect-Inc-PlannerResultDetail');
//	}
}