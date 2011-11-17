<?php
/**
 * twitterconnect_patch_0350
 * @package modules.twitterconnect
 */
class twitterconnect_patch_0350 extends patch_BasePatch
{
	/**
	 * Entry point of the patch execution.
	 */
	public function execute()
	{
		foreach (twitterconnect_PeriodicplannerService::getInstance()->createQuery()->find() as $planner)
		{
			$container = null;
			try 
			{
				$container = DocumentHelper::getDocumentInstance($planner->getContainerId());
				if (!preg_match('^[1-9][0-9]{0,2}(h|d|w|m|y)$', strval($planner->getPeriod())))
				{
					$planner->setPeriod('1m');
					$planner->save();
					$this->logWarning('Fixed planner period to "1m": '.$planner->getLabel().' ('.$planner->getId().') on container '.$container->__toString());
				}
			}
			catch (Exception $e)
			{
				if ($container === null)
				{
					$this->log('Delete planner on unexisting container: '.$planner->getLabel().' ('.$planner->getId().') on container '.$planner->getContainerId());
					$planner->delete();
				}
				else
				{
					$this->logError('Error fixing planner period: '.$planner->getLabel().' ('.$planner->getId().') on container '.$container->__toString());
					Framework::exception($e);
				}
			}
		}
	}
}