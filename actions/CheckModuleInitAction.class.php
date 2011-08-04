<?php
/**
 * twitterconnect_CheckModuleInitAction
 * @package modules.twitterconnect.actions
 */
class twitterconnect_CheckModuleInitAction extends change_JSONAction
{
	/**
	 * @param change_Context $context
	 * @param change_Request $request
	 */
	public function _execute($context, $request)
	{
		$result = twitterconnect_ModuleService::getInstance()->checkInitModuleInfos();	
		return $this->sendJSON($result);
	}
}