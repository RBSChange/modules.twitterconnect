<?php
/**
 * @package modules.twitterconnect
 * @method twitterconnect_TweetService getInstance()
 */
class twitterconnect_TweetService extends f_persistentdocument_DocumentService
{
	const STATUS_SENT = 'sent';
	const STATUS_PLANNED = 'planned';
	const STATUS_PLANNED_ON_PUBLISH = 'onpublish';
	const STATUS_ERROR = 'error';
	const META_TWEET_ON_PUBLISH = 'modules.twitterconnect.tweetOnPublish';
	const META_TWEET_ON_PUBLISH_FOR_WEBSITE = 'modules.twitterconnect.tweetOnPublishForWebsite';

	/**
	 * @return twitterconnect_persistentdocument_tweet
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_twitterconnect/tweet');
	}

	/**
	 * Create a query based on 'modules_twitterconnect/tweet' model.
	 * Return document that are instance of modules_twitterconnect/tweet,
	 * including potential children.
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->getPersistentProvider()->createQuery('modules_twitterconnect/tweet');
	}
	
	/**
	 * Create a query based on 'modules_twitterconnect/tweet' model.
	 * Only documents that are strictly instance of modules_twitterconnect/tweet
	 * (not children) will be retrieved
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createStrictQuery()
	{
		return $this->getPersistentProvider()->createQuery('modules_twitterconnect/tweet', false);
	}
	
	/**
	 * @param f_persistentdocument_PersistentDocument $document
	 * @param change_BaseService $service
	 * @param integer $websiteId
	 * @return array
	 */
	public function getRelatedInfosById($document, $service, $websiteId)
	{
		return array('isPublished' => $document->isPublished());
	}
	
	/**
	 * @param f_persistentdocument_PersistentDocument $document
	 * @param change_BaseService $service
	 * @param integer $startIndex
	 * @param integer $pageSize
	 * @return array
	 */
	public function getTweetsInfosByDocumentId($document, $service, $startIndex, $pageSize)
	{
		$id = $document->getId();
		$orExp = Restrictions::orExp(Restrictions::eq('websiteId', $id), Restrictions::eq('containerId', $id), Restrictions::eq('relatedId', $id));
		$row = $this->createQuery()->add($orExp)->setProjection(Projections::rowCount('count'))->findUnique();
		$totalCount = $row['count'];
		
		$tweetsInfos = array();
		if ($totalCount > 0)
		{
			$tweets = $this->createQuery()->add($orExp)->setMaxResults($pageSize)->setFirstResult($startIndex)->addOrder(Order::desc('displaypriority'))->addOrder(Order::desc('sendingdate'))->find();
			$tweetsInfos = $this->getInfosForTweets($tweets);
		}
		return array('total' => $totalCount, 'startIndex' => $startIndex, 'tweets' => $tweetsInfos);
	}
	
	/**
	 * @param twitterconnect_persistentdocument_tweet[] $tweets
	 * @return array
	 */
	private function getInfosForTweets($tweets)
	{
		$ls = LocaleService::getInstance();
		
		$tweetsInfos = array();
		foreach ($tweets as $tweet)
		{
			$sendingStatus = $tweet->getSendingStatus();
			
			$tweetInfos = array();
			$tweetInfos['documentId'] = $tweet->getId();
			
			$tweetInfos['sendingStatus'] = $sendingStatus;
			$tweetInfos['sendingStatusLabel'] = $ls->trans('m.twitterconnect.bo.general.sending-statuses.' . $sendingStatus, array('ucf'));
			$tweetInfos['sendingStatusFullLabel'] = $tweetInfos['sendingStatusLabel'];
			$tweetInfos['accountLabel'] = $tweet->getAccount()->getLabel();
			$tweetInfos['contents'] = $tweet->getLabel();
			if ($tweet->getSendingDate())
			{
				$tweetInfos['sendingDate'] = date_Formatter::toDefaultDateTimeBO($tweet->getUISendingDate());
				$tweetInfos['sendingStatusFullLabel'] .= ' ' . $ls->trans('m.twitterconnect.bo.general.on') . ' ' . $tweetInfos['sendingDate'];
			}
			
			$tweetInfos['disableDelete'] = 'false';
			$tweetInfos['disableResend'] = 'true';
			$tweetInfos['errorMessage'] = null;
			switch ($sendingStatus)
			{
				case self::STATUS_SENT: 
					$tweetInfos['iconUrl'] = MediaHelper::getIcon('tweet', MediaHelper::SMALL);
					// TODO: view retweets.
					break;
					
				case self::STATUS_ERROR: 
					$tweetInfos['iconUrl'] = MediaHelper::getIcon('error', MediaHelper::SMALL);
					$tweetInfos['disableResend'] = 'false';
					$tweetInfos['errorMessage'] = $tweet->getErrorMessage();
					break;

				case self::STATUS_PLANNED:
				case self::STATUS_PLANNED_ON_PUBLISH:
					$tweetInfos['iconUrl'] = MediaHelper::getIcon('planned-tweet', MediaHelper::SMALL);
					break;
					
				default: 
					break;
			}
			
			$relatedDocument = DocumentHelper::getDocumentInstance($tweet->getRelatedId());
			$tweetInfos['relatedId'] = $relatedDocument->getId();
			$tweetInfos['relatedLabel'] = $relatedDocument->getLabel();
			$model = $relatedDocument->getPersistentModel();
			$tweetInfos['relatedIcon'] = $model->getIcon();
			$tweetInfos['relatedIconUrl'] = MediaHelper::getIcon($tweetInfos['relatedIcon'], MediaHelper::SMALL);
			$tweetInfos['relatedModelLabel'] = $ls->trans($model->getLabelKey());
			$tweetInfos['relatedCompleteLabel'] = $tweetInfos['relatedLabel'] . ' (' . $tweetInfos['relatedModelLabel'] . ')';
			
			$tweetsInfos[] = $tweetInfos;
		}
		return $tweetsInfos;
	}
	
	/**
	 * @param integer $documentId
	 */
	public function sendTweetsPlannedOnPublishByRelatedDocumentId($documentId, $websiteId)
	{
		foreach ($this->getTweetsPlannedOnPublishByRelatedId($documentId) as $tweet)
		{
			$tweet->planNow();
		}
		$this->removeTweetOnPublishMeta(DocumentHelper::getDocumentInstance($documentId), $websiteId);
	}
	
	/**
	 * @return void
	 */
	public function sendPlannedTweets()
	{
		$tweetsToSend = $this->createQuery()->add(Restrictions::eq('sendingStatus', self::STATUS_PLANNED))->add(Restrictions::le('sendingDate', date_Calendar::getInstance()->toString()))->find();
		foreach ($tweetsToSend as $tweet)
		{
			$this->sendTweet($tweet);
		}
	}
	
	/**
	 * @param twitterconnect_persistentdocument_tweet $tweet
	 */
	public function sendTweet($tweet)
	{
		$tms = twitterconnect_ModuleService::getInstance();
		$account = $tweet->getAccount();
		$token = $account->getAccessToken();
		$config = array('consumerKey' => $account->getConsumer()->getConsumerKey(),
						'consumerSecret' =>  $account->getConsumer()->getConsumerSecret());
		$client = $token->getHttpClient($config, null, change_HttpClientService::getInstance()->getHttpClientConfig());
		$client->setUri('http://twitter.com/statuses/update.' .  $tms->getResultFormat());
		$client->setMethod(\Zend\Http\Request::METHOD_POST);
		$client->setParameterPost(array('status' => $tweet->getLabel()));
		$request = $client->send();
		$infos = $tms->parseTwitterResult($request->getBody());
		$tweet->setSendingInfos($infos);
		$tweet->setSendingDate(date_Calendar::getInstance()->toString());
		if (array_key_exists('error', $infos))
		{
			if (Framework::isInfoEnabled())
			{
				Framework::info(__METHOD__ . ' KO accountId = ' . $account->getId() . ', error = ' . $infos['error']);
			}
			$tweet->setSendingStatus(twitterconnect_TweetService::STATUS_ERROR);
		}
		else
		{
			$tweetId = $infos['id'];
			if (Framework::isInfoEnabled())
			{
				Framework::info(__METHOD__ . ' OK tweet id = ' . $tweetId);
			}
			$tweet->setSendingStatus(twitterconnect_TweetService::STATUS_SENT);
			$tweet->setTweetId($tweetId);
		}
		$tweet->save();
	}
		
	/**
	 * @param integer $relatedId
	 * @return twitterconnect_persistentdocument_tweet[]
	 */
	public function getTweetsPlannedOnPublishByRelatedId($relatedId)
	{
		return $this->createQuery()->add(Restrictions::eq('relatedId', $relatedId))->add(Restrictions::eq('sendingStatus', self::STATUS_PLANNED_ON_PUBLISH))->find();
	}
	
	/**
	 * @param f_persistentdocument_PersistentDocument $document
	 * @param twitterconnect_persistentdocument_account $account
	 */
	public function hasTweetForDocumentAndAccount($document, $account)
	{
		$query = $this->createQuery()->add(Restrictions::eq('relatedId', $document->getId()));
		$query->add(Restrictions::eq('accountId', $account->getId()));
		$result = $query->setProjection(Projections::count('id', 'count'))->findUnique();
		return $result['count'] > 0;
	}
	
	/**
	 * @param twitterconnect_persistentdocument_tweet $document
	 * @param integer $parentNodeId Parent node ID where to save the document (optionnal => can be null !).
	 * @return void
	 */
	protected function preSave($document, $parentNodeId = null)
	{
		parent::preSave($document, $parentNodeId);
		if ($document->getSendingStatus() == self::STATUS_PLANNED_ON_PUBLISH)
		{
			$document->setDisplayPriority(10);
		}
		else
		{
			$document->setDisplayPriority(5);
		}
	}

	/**
	 * @param twitterconnect_persistentdocument_tweet $document
	 * @param integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
	protected function preInsert($document, $parentNodeId = null)
	{
		parent::preInsert($document, $parentNodeId);
		$document->setInsertInTree(false);
	}
	
	/**
	 * @param string $modelName
	 * @return integer[]
	 */
	public function getAlreadyTweetedPublishedIdsByModelName($modelName, $account)
	{
		$query = twitterconnect_TweetService::getInstance()->createQuery()->add(Restrictions::eq('accountId', $account->getId()));
		$query->createPropertyCriteria('relatedId', $modelName)->add(Restrictions::published());
		$query->setProjection(Projections::property('relatedId'));
		return $query->findColumn('relatedId');
	}

	/**
	 * @param twitterconnect_persistentdocument_tweet $document
	 * @param integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
	protected function postSave($document, $parentNodeId = null)
	{
		parent::postSave($document, $parentNodeId);
		if ($document->getSendingStatus() == self::STATUS_PLANNED_ON_PUBLISH)
		{
			$related = DocumentHelper::getDocumentInstance($document->getRelatedId());
			$this->setTweetOnPublishMeta($related, $document->getWebsiteId());
		}
	}

	/**
	 * @param twitterconnect_persistentdocument_tweet $document
	 */
	public function deleteFromDbAndTwitter($document)
	{
		if ($document->getSendingStatus() == self::STATUS_SENT && $document->getTweetId())
		{
			$tms = twitterconnect_ModuleService::getInstance();
			$account = $document->getAccount();
			$token = $account->getAccessToken();
			$config = array('consumerKey' => $account->getConsumer()->getConsumerKey(),
						'consumerSecret' =>  $account->getConsumer()->getConsumerSecret());
			$client = $token->getHttpClient($config, null, change_HttpClientService::getInstance()->getHttpClientConfig());
			$client->setUri('http://twitter.com/statuses/destroy.' .  $tms->getResultFormat());
			$client->setMethod(\Zend\Http\Request::METHOD_POST);
			$client->setParameterPost(array('id' => $document->getTweetId()));
			$request = $client->send();
			$infos = $tms->parseTwitterResult($request->getBody());
			if (array_key_exists('error', $infos))
			{
				Framework::warn(__METHOD__ . ' ERROR: "' . $infos['error'] . '" for twitter id '  . $document->getTweetId());
			}
		}
		$document->delete();
	}

	/**
	 * @param f_persistentdocument_PersistentDocument $document
	 * @param integer $websiteId
	 */
	private function setTweetOnPublishMeta($document, $websiteId)
	{
		$service = twitterconnect_ModuleService::getInstance()->getServiceForDocument($document);
		if (f_util_ClassUtils::methodExists($service, 'setTweetOnPublishMeta'))
		{
			$service->setTweetOnPublishMeta($document, $websiteId);
		}
		else 
		{
			$document->setMeta(self::META_TWEET_ON_PUBLISH, $document->getId());
			$document->saveMeta();
		}
	}
	
	/**
	 * @param f_persistentdocument_PersistentDocument $document
	 * @param integer $websiteId
	 */
	private function removeTweetOnPublishMeta($document, $websiteId)
	{
		$service = twitterconnect_ModuleService::getInstance()->getServiceForDocument($document);
		if (f_util_ClassUtils::methodExists($service, 'removeTweetOnPublishMeta'))
		{
			$service->removeTweetOnPublishMeta($document, $websiteId);
		}
		else 
		{
			$document->setMeta(self::META_TWEET_ON_PUBLISH, null);
			$document->saveMeta();
		}
	}
}