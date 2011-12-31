<?php

class ProjectBaseAction extends AgaviAction
{
	/**
	 * Execute any business logic for Console output types
	 *
	 * Ensures that executeConsole exists and console requests implement it
	 *
	 * @param      AgaviRequestDataHolder The action's request data holder.
	 *
	 * @author     Ross Lawley <ross.lawley@gmail.com>
	 * @since      0.11.RC4
	 *
	 * @version    $Id$
	 */
	public function executeConsole(AgaviRequestDataHolder $rd)
	{
		throw new AgaviViewException(sprintf(
			'The Action "%1$s" does not implement an "executeConsole()" method to '.
			"manage the business logic for the action. \r\n".
			'It is recommended that you change the code of the method "executeConsole()"'.
			'in the base Action "%4$s" that is throwing this exception to deal with this '.
			"situation in a more appropriate manner. \r\n".
			"This ensures console / cron jobs are handled correctly \r\n",
			get_class($this),
			get_class()
		));
	}
}

?>