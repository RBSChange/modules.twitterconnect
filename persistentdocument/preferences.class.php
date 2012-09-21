<?php
/**
 * Class where to put your custom methods for document twitterconnect_persistentdocument_preferences
 * @package modules.twitterconnect.persistentdocument
 */
class twitterconnect_persistentdocument_preferences extends twitterconnect_persistentdocument_preferencesbase 
{
	/**
	 * @retrun String
	 */
	public function getLabel()
	{
		return LocaleService::getInstance()->trans('m.twitterconnect.document.preferences.document-name', array('ucf'));
	}
}