<?php
/**
 * Class where to put your custom methods for document twitterconnect_persistentdocument_onpublishplanner
 * @package modules.twitterconnect.persistentdocument
 */
class twitterconnect_persistentdocument_onpublishplanner extends twitterconnect_persistentdocument_onpublishplannerbase 
{
	/**
	 * @return string
	 */
	public function getPlannerTypeLabel()
	{
		return LocaleService::getInstance()->trans('m.twitterconnect.bo.general.planner-types.on-publish', array('ucf'));
	}
}