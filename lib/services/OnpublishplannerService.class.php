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
			self::$instance = self::getServiceClassInstance(get_class());
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
	
	/**
	 * @param twitterconnect_persistentdocument_onpublishplanner $document
	 * @param Integer $parentNodeId Parent node ID where to save the document (optionnal => can be null !).
	 * @return void
	 */
//	protected function preSave($document, $parentNodeId = null)
//	{
//		parent::preSave($document, $parentNodeId);
//
//	}

	/**
	 * @param twitterconnect_persistentdocument_onpublishplanner $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
//	protected function preInsert($document, $parentNodeId = null)
//	{
//		parent::preInsert($document, $parentNodeId);
//	}

	/**
	 * @param twitterconnect_persistentdocument_onpublishplanner $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
//	protected function postInsert($document, $parentNodeId = null)
//	{
//		parent::postInsert($document, $parentNodeId);
//	}

	/**
	 * @param twitterconnect_persistentdocument_onpublishplanner $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
//	protected function preUpdate($document, $parentNodeId = null)
//	{
//		parent::preUpdate($document, $parentNodeId);
//	}

	/**
	 * @param twitterconnect_persistentdocument_onpublishplanner $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
//	protected function postUpdate($document, $parentNodeId = null)
//	{
//		parent::postUpdate($document, $parentNodeId);
//	}

	/**
	 * @param twitterconnect_persistentdocument_onpublishplanner $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
//	protected function postSave($document, $parentNodeId = null)
//	{
//		parent::postSave($document, $parentNodeId);
//	}

	/**
	 * @param twitterconnect_persistentdocument_onpublishplanner $document
	 * @return void
	 */
//	protected function preDelete($document)
//	{
//		parent::preDelete($document);
//	}

	/**
	 * @param twitterconnect_persistentdocument_onpublishplanner $document
	 * @return void
	 */
//	protected function preDeleteLocalized($document)
//	{
//		parent::preDeleteLocalized($document);
//	}

	/**
	 * @param twitterconnect_persistentdocument_onpublishplanner $document
	 * @return void
	 */
//	protected function postDelete($document)
//	{
//		parent::postDelete($document);
//	}

	/**
	 * @param twitterconnect_persistentdocument_onpublishplanner $document
	 * @return void
	 */
//	protected function postDeleteLocalized($document)
//	{
//		parent::postDeleteLocalized($document);
//	}

	/**
	 * @param twitterconnect_persistentdocument_onpublishplanner $document
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
	 * @param twitterconnect_persistentdocument_onpublishplanner $document
	 * @param String $oldPublicationStatus
	 * @param array<"cause" => String, "modifiedPropertyNames" => array, "oldPropertyValues" => array> $params
	 * @return void
	 */
//	protected function publicationStatusChanged($document, $oldPublicationStatus, $params)
//	{
//		parent::publicationStatusChanged($document, $oldPublicationStatus, $params);
//	}

	/**
	 * Correction document is available via $args['correction'].
	 * @param f_persistentdocument_PersistentDocument $document
	 * @param Array<String=>mixed> $args
	 */
//	protected function onCorrectionActivated($document, $args)
//	{
//		parent::onCorrectionActivated($document, $args);
//	}

	/**
	 * @param twitterconnect_persistentdocument_onpublishplanner $document
	 * @param String $tag
	 * @return void
	 */
//	public function tagAdded($document, $tag)
//	{
//		parent::tagAdded($document, $tag);
//	}

	/**
	 * @param twitterconnect_persistentdocument_onpublishplanner $document
	 * @param String $tag
	 * @return void
	 */
//	public function tagRemoved($document, $tag)
//	{
//		parent::tagRemoved($document, $tag);
//	}

	/**
	 * @param twitterconnect_persistentdocument_onpublishplanner $fromDocument
	 * @param f_persistentdocument_PersistentDocument $toDocument
	 * @param String $tag
	 * @return void
	 */
//	public function tagMovedFrom($fromDocument, $toDocument, $tag)
//	{
//		parent::tagMovedFrom($fromDocument, $toDocument, $tag);
//	}

	/**
	 * @param f_persistentdocument_PersistentDocument $fromDocument
	 * @param twitterconnect_persistentdocument_onpublishplanner $toDocument
	 * @param String $tag
	 * @return void
	 */
//	public function tagMovedTo($fromDocument, $toDocument, $tag)
//	{
//		parent::tagMovedTo($fromDocument, $toDocument, $tag);
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
//		parent::onMoveToStart($document, $destId);
//	}

	/**
	 * @param twitterconnect_persistentdocument_onpublishplanner $document
	 * @param Integer $destId
	 * @return void
	 */
//	protected function onDocumentMoved($document, $destId)
//	{
//		parent::onDocumentMoved($document, $destId);
//	}

	/**
	 * this method is call before saving the duplicate document.
	 * If this method not override in the document service, the document isn't duplicable.
	 * An IllegalOperationException is so launched.
	 *
	 * @param twitterconnect_persistentdocument_onpublishplanner $newDocument
	 * @param twitterconnect_persistentdocument_onpublishplanner $originalDocument
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
	 * @param twitterconnect_persistentdocument_onpublishplanner $newDocument
	 * @param twitterconnect_persistentdocument_onpublishplanner $originalDocument
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
	 * @param twitterconnect_persistentdocument_onpublishplanner $document
	 * @param string $lang
	 * @param array $parameters
	 * @return string
	 */
//	public function generateUrl($document, $lang, $parameters)
//	{
//	}

	/**
	 * @param twitterconnect_persistentdocument_onpublishplanner $document
	 * @return integer | null
	 */
//	public function getWebsiteId($document)
//	{
//		return parent::getWebsiteId($document);
//	}

	/**
	 * @param twitterconnect_persistentdocument_onpublishplanner $document
	 * @return website_persistentdocument_page | null
	 */
//	public function getDisplayPage($document)
//	{
//		return parent::getDisplayPage($document);
//	}

	/**
	 * @param twitterconnect_persistentdocument_onpublishplanner $document
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
	 * @param twitterconnect_persistentdocument_onpublishplanner $document
	 * @param string $bockName
	 * @return array with entries 'module' and 'template'. 
	 */
//	public function getSolrserachResultItemTemplate($document, $bockName)
//	{
//		return array('module' => 'twitterconnect', 'template' => 'Twitterconnect-Inc-OnpublishplannerResultDetail');
//	}
}