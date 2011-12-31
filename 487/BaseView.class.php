<?php
class BaseView extends AgaviView
{

	/**
	 * Extend the initialization of this view - so we automatically get the Flash
	 *
	 * @param      AgaviExecutionContainer This View's execution container.
	 *
	 * @author     Ross Lawley <ross.lawley@gmail.com>
	 * @since      0.11
	 */
	public function initialize(AgaviExecutionContainer $container)
	{
		parent::initialize($container);
		// Loads the Flash Messages and add them to the template
		$this->flash = new Flash($this->context);
		$this->setAttribute('flash', $this->flash);
	}

	/*
		Put your other base view logic here...
	*/
}

?>
