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
		return f_Locale::translateUI(parent::getLabel());
	}
	
	/**
	 * @param string $moduleName
	 * @param string $treeType
	 * @param array<string, string> $nodeAttributes
	 */
//	protected function addTreeAttributes($moduleName, $treeType, &$nodeAttributes)
//	{
//	}
	
	/**
	 * @param string $actionType
	 * @param array $formProperties
	 */
//	public function addFormProperties($propertiesNames, &$formProperties)
//	{	
//	}
}