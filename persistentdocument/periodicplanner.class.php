<?php
/**
 * Class where to put your custom methods for document twitterconnect_persistentdocument_periodicplanner
 * @package modules.twitterconnect.persistentdocument
 */
class twitterconnect_persistentdocument_periodicplanner extends twitterconnect_persistentdocument_periodicplannerbase 
{
	/**
	 * @return string
	 */
	public function getPlannerTypeLabel()
	{
		$period = $this->getPeriod();
		$value = substr($period, 0, -1);
		$unit = substr($period, -1);
		if ($value == 1)
		{
			return LocaleService::getInstance()->trans('m.twitterconnect.bo.general.period-one-'.$unit, array('ucf'), array('period' => $value));
		}
		else
		{
			return LocaleService::getInstance()->trans('m.twitterconnect.bo.general.period-'.$unit, array('ucf'), array('period' => $value));
		}
	}
}