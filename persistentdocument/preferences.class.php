<?php
/**
 * @package twitterconnect
 */
class twitterconnect_persistentdocument_preferences extends twitterconnect_persistentdocument_preferencesbase 
{
	/**
	 * Define the label of the tree node of the document.
	 * By default, this method returns the label property value.
	 * @return string
	 */
	public function getTreeNodeLabel()
	{
		return LocaleService::getInstance()->trans('m.twitterconnect.bo.general.module-name', array('ucf'));
	}
}