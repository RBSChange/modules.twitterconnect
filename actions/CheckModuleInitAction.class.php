<?php
/**
 * twitterconnect_CheckModuleInitAction
 * @package modules.twitterconnect.actions
 */
class twitterconnect_CheckModuleInitAction extends f_action_BaseJSONAction
{
	/**
	 * @param Context $context
	 * @param Request $request
	 */
	public function _execute($context, $request)
	{
		$result = twitterconnect_ModuleService::getInstance()->checkInitModuleInfos();	
		return $this->sendJSON($result);
	}
}