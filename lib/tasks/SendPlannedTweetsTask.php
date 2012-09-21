<?php
/**
 * @author intportg
 * @package modules.twitterconnect
 */
class twitterconnect_SendPlannedTweetsTask extends task_SimpleSystemTask
{
	/**
	 * @see task_SimpleSystemTask::execute()
	 */
	protected function execute()
	{
		twitterconnect_PeriodicplannerService::getInstance()->sendTweets();
		twitterconnect_TweetService::getInstance()->sendPlannedTweets();
	}
}