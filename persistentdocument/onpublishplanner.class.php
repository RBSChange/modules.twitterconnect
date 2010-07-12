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
		return f_Locale::translateUI('&modules.twitterconnect.bo.general.planner-types.On-publish;');
	}
}