<?php
/**
 * @package modules.twitterconnect.lib.services
 */
class twitterconnect_ModuleService extends ModuleBaseService
{
	/**
	 * Singleton
	 * @var twitterconnect_ModuleService
	 */
	private static $instance = null;
	
	/**
	 * @return twitterconnect_ModuleService
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = self::getServiceClassInstance(get_class());
		}
		return self::$instance;
	}
	
	/**
	 * @param f_persistentdocument_PersistentDocument $document
	 * @param string $module
	 * @return BaseService
	 */
	public function getServiceForDocument($document, $module = null)
	{
		$model = $document->getPersistentModel();
		return $this->getServiceForModel($model, $module);
	}
	
	/**
	 * @param f_persistentdocument_PersistentDocumentModel $model
	 * @param string $module
	 * @return BaseService
	 */
	public function getServiceForModel($model, $module = null)
	{
		if ($module === null)
		{
			$module = $model->getModuleName();
		}
		$name = $model->getDocumentName();
		if ($name != 'folder' && $name != 'topic' && $name != 'systemtopic')
		{
			$module = $model->getModuleName();
		}
		$className = $module . '_' . ucfirst($model->getDocumentName()) . 'TwitterService';
		if (f_util_ClassUtils::classExists($className))
		{
			return f_util_ClassUtils::callMethod($className, 'getInstance');
		}
		else
		{
			return $model->getDocumentService();
		}
	}
	
	/**
	 * @param f_persistentdocument_PersistentDocument $document
	 * @param string $module
	 * @param integer $websiteId
	 * @return array
	 */
	public function getReplacementsByRelatedDocument($document, $module, $websiteId)
	{
		$service = $this->getServiceForDocument($document, $module);
		return $this->getReplacementsByService($service, $websiteId, $document);
	}
	
	/**
	 * @param f_persistentdocument_PersistentDocument $document
	 * @param string $module
	 * @param integer $websiteId
	 * @return array
	 */
	public function getReplacementsByModelName($modelName, $module, $websiteId)
	{
		$model = f_persistentdocument_PersistentDocumentModel::getInstanceFromDocumentModelName($modelName);
		$service = $this->getServiceForModel($model, $module);
		return $this->getReplacementsByService($service, $websiteId);
	}
	
	/**
	 * @param BaseService $service
	 * @param integer $websiteId
	 * @param f_persistentdocument_PersistentDocument $document
	 * @return array
	 */
	public function getReplacementsByService($service, $websiteId, $document = null)
	{
		if (f_util_ClassUtils::methodExists($service, 'getReplacementsForTweet'))
		{
			return $service->getReplacementsForTweet($document, $websiteId);
		}
		return array();
	}
	
	/**
	 * @param integer $id
	 * @param string $module
	 * @param integer $startIndex
	 * @param integer $pageSize
	 * @return array
	 */
	public function getInfosByDocumentId($id, $module, $startIndex, $pageSize)
	{
		$relatedDoc = DocumentHelper::getDocumentInstance($id);
		$service = $this->getServiceForDocument($relatedDoc, $module);
		$websiteId = null;
		if (f_util_ClassUtils::methodExists($service, 'getWebsitesForTweets'))
		{
			$websites = $service->getWebsitesForTweets($relatedDoc);
			if (count($websites) > 0)
			{
				$websitesData = array();
				foreach ($websites as $website)
				{
					$websitesData[] = array('id' => $website->getId(), 'label' => $website->getLabel());
				}
				$websitesInfos = array('websitesData' => $websitesData);
			}
			else
			{
				throw new BaseException('No website', 'modules.twitterconnect.bo.doceditor.panel.tweets.Error-document-not-in-a-website');
			}
		}
		else
		{
			$websiteId = $relatedDoc->getDocumentService()->getWebsiteId($relatedDoc);
			if ($websiteId !== null)
			{
				$websitesInfos = array('websiteId' => $websiteId);
			}
			else
			{
				throw new BaseException('No website', 'modules.twitterconnect.bo.doceditor.panel.tweets.Error-document-not-in-a-website');
			}
		}
		
		$ts = twitterconnect_TweetService::getInstance();
		$tweetsInfos = $ts->getTweetsInfosByDocumentId($relatedDoc, $service, $startIndex, $pageSize);
		$relatedInfos = $ts->getRelatedInfosById($relatedDoc, $service, $websiteId);
		
		$ps = twitterconnect_PlannerService::getInstance();
		$plannersInfos = $ps->getPlannersInfosByContainerId($relatedDoc, $service);
		$containerInfos = $ps->getContainerInfosById($relatedDoc, $service, $websiteId);
		
		return array('websitesInfos' => $websitesInfos, 'tweetsInfos' => $tweetsInfos, 'plannersInfos' => $plannersInfos, 'relatedInfos' => $relatedInfos, 'containerInfos' => $containerInfos);
	}
	
	/**
	 * @return string
	 */
	public function getResultFormat()
	{
		// Tweet ids are bigger thant the biggest integer that PHP can handle. 
		// So we can't use the JSON format for now because decoding with JsonService will break them...
		return 'xml';
	}
	
	/**
	 * @param string $result
	 * @return array
	 */
	public function parseTwitterResult($result, $format = null)
	{
		$format = ($format === null) ? $this->getResultFormat() : $format;
		if ($format == 'xml')
		{
			return $this->parseTwitterResultXml($result);
		}
		else if ($format == 'json')
		{
			return $this->parseTwitterResultJson($result);
		}
		else
		{
			throw Exception('bad format');
		}
	}
	
	/**
	 * @param string $result
	 * @return array
	 */
	private function parseTwitterResultJson($result)
	{
		return JsonService::getInstance()->decode($result);
	}
	
	/**
	 * @param string $result
	 * @return array
	 */
	private function parseTwitterResultXml($result)
	{
		$infos = array();
		$doc = new DOMDocument();
		$doc->loadXML($result);
		
		$statusesList = $doc->getElementsByTagName('statuses');
		if ($statusesList->length == 1)
		{
			foreach ($doc->getElementsByTagName('status') as $status)
			{
				$row = array();
				foreach ($status->childNodes as $child)
				{
					$this->getInfos($row, $child);
				}
				$infos[] = $row;
			}
			return $infos;
		}
		
		$statusList = $doc->getElementsByTagName('status');
		if ($statusList->length == 1)
		{
			foreach ($statusList->item(0)->childNodes as $child)
			{
				$this->getInfos($infos, $child);
			}
			return $infos;
		}
		
		$hashList = $doc->getElementsByTagName('hash');
		if ($hashList->length == 1)
		{
			foreach ($hashList->item(0)->childNodes as $child)
			{
				$this->getInfos($infos, $child);
			}
			return $infos;
		}
		
		throw new Exception('unexpected result');
	}
	
	/**
	 * @param array $infos
	 * @param DOMNode $node
	 */
	private function getInfos(&$infos, $node)
	{
		if ($node instanceof DOMElement)
		{
			if ($node->childNodes instanceof DOMNodeList && $node->childNodes->length > 1)
			{
				$value = array();
				foreach ($node->childNodes as $child)
				{
					$this->getInfos($value, $child);
				}
			}
			else
			{
				$value = ($node->firstChild) ? $node->firstChild->nodeValue : null;
			}
			$infos[$node->tagName] = $value;
		}
	}
	
	public function checkInitModuleInfos()
	{
		$result = array();
		$preference = ModuleService::getInstance()->getPreferencesDocument('twitterconnect');
		if ($preference === null)
		{
			$service = twitterconnect_PreferencesService::getInstance();
			$preference = $service->getNewDocumentInstance();
			$service->save($preference);
		}
		
		if (f_util_StringUtils::isEmpty($preference->getConsumerKey()) || f_util_StringUtils::isEmpty($preference->getConsumerSecret()))
		{
			$result['accountNotSet'] = true;
			$result['id'] = $preference->getId();
		}
		else
		{
			$result['accountKey'] = $preference->getConsumerKey();
		}
		return $result;
	}
}