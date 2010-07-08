<?php
/**
 * @package module.twitterconnect
 */
class twitterconnect_ListAuthorizedaccountsbywebsiteService extends BaseService
{
	/**
	 * @var twitterconnect_ListAccountsbywebsiteService
	 */
	private static $instance;
	private $items = null;

	/**
	 * @return twitterconnect_ListAccountsbywebsiteService
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = self::getServiceClassInstance(get_class());
		}
		return self::$instance;
	}

	/**
	 * @return array<list_Item>
	 */
	public final function getItems()
	{
		try 
		{
			$request = Controller::getInstance()->getContext()->getRequest();
			$websiteId = intval($request->getParameter('websiteId', 0));
			$website = DocumentHelper::getDocumentInstance($websiteId);
		}
		catch (Exception $e)
		{
			if (Framework::isDebugEnabled())
			{
				Framework::debug(__METHOD__ . ' EXCEPTION: ' . $e->getMessage());
			}
			return array();
		}
		
		$items = array();
		foreach (twitterconnect_AccountService::getInstance()->getAuthorizedByWebsite($website) as $account)
		{
			$items[] = new list_Item(
				$account->getLabel(),
				$account->getId()
			);
		}
		return $items;
	}
}