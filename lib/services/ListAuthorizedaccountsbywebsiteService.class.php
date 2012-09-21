<?php
/**
 * @package module.twitterconnect
 * @method twitterconnect_ListAuthorizedaccountsbywebsiteService getInstance()
 */
class twitterconnect_ListAuthorizedaccountsbywebsiteService extends change_BaseService implements list_ListItemsService
{
	/**
	 * @return list_Item[]
	 */
	public final function getItems()
	{
		try 
		{
			$request = change_Controller::getInstance()->getContext()->getRequest();
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