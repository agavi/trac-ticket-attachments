<?php

/**
 * SlotFormPopulationFilter Action Filter attempts to keep slots context,
 * so if a slot creates validation manager errors then it will save that information 
 * and redirect back to the context that it was originally displayed.
 *
 * This means that your views can be more independant and don't need to know
 * the context in which they are used.
 *
 * <b>Optional parameters:</b>
 *
 * # <b>disabled</b> - [true] - Toggle the SPF - allow slots to determine how to handle
 *                              their own errors.
 *
 * # <b>maintain_slot_context</b> - [false] - Attempt to maintain the slot context, even if
 *                                            there have been no validation errors
 *
 * # <b>slots_to_overwrite</b> - [array] - An slots information array (module, action & url)
 *                                         or an array of slot information arrays to overwrite.
 *                                         The Key is the name of the slot to be overridden
 *
 * # <b>slots_to_ignore</b> - [array] - An array of slot names to be ignored.
 *
 * # <b>slots_to_add</b> - [array] - An slots information $name => array (module, action & url)
 *
 * # <b>slot_data_to_remove</b> - [array] - An array of keys to be removed from the request data holder
 *
 * @author     Ross Lawley <ross.lawley@gmail.com>
 * @author     Zbigniew Swiderski <swidersk@gmail.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class SlotPopulationFilter extends AgaviFilter implements AgaviIGlobalFilter, AgaviIActionFilter
{
	/**
	 * @var        String  The current request's url
	 */
	protected $url = null;
	/**
	 * Execute this filter.
	 *
	 * @param      AgaviFilterChain        The filter chain.
	 * @param      AgaviExecutionContainer The current execution container.
	 *
	 * @throws     <b>AgaviFilterException</b> If an error occurs during execution.
	 *
	 * @author     Zbigniew Swiderski <swidersk@gmail.com>
	 * @author     Ross Lawley <ross.lawley@gmail.com>
	 * @since      ?
	 */
	public function executeOnce(AgaviFilterChain $filterChain, AgaviExecutionContainer $container)
	{
		$filterChain->execute($container);

		$context = $this->getContext();
		$controller = $context->getController();
		$response = $container->getResponse();
		$request = $context->getRequest();
		$routing = $context->getRouting();
		$storage = $context->getStorage();
		$this->url = $request->getUrl();
		$vm = $container->getValidationManager();

		$viewInstance = $container->getViewInstance();
		// Return if there is no view instance
		if (!$viewInstance) return;

		// Grab the configurations
		$cfg = array_merge($this->getParameters(), $request->getAttributes('org.agavi.filter.SlotPopulationFilter'));

		// Ensure we only action the correct output types
		$outputType = $container->getOutputType();
		if(is_array($cfg['output_types']) && !in_array($outputType->getName(), $cfg['output_types'])) {
			return;
		}

		// Get the slots from the layers and save their core information
		$slots = array();
		foreach($viewInstance->getLayers() as $layer) {
			$slots = array_merge($slots, $this->getLayersSlotsInformation($layer));
		}

		// Get the session SPF - if doesn't exist create it!
		$SPF = $storage->read('agavi.org.filter.SlotPopulationFilter');
		if (!$SPF) {
			$SPF = new AgaviParameterHolder;
		}

		// Get the current requests information
		$current = $SPF->getParameter('current', array());
		$current = array_merge($current, $slots);
		// Save the slots information!
		$SPF->setParameter('current', $current);

		// All execution Containers have run - lets process!
		// Remove any slots to be ignored! - As defined in the cfg or overwritten in your view
		if(is_array($cfg['slots_to_ignore'])){
			foreach($cfg['slots_to_ignore'] as $slot_name){
				$key = array_search($slot_name, $current);
				if($key !== FALSE) {
					unset($current[$key]);
				}
			}
		}

		// Overwrite any slots information with new information!
		if(is_array($cfg['slots_to_overwrite'])) {

			foreach($cfg['slots_to_overwrite'] as $slot_name => $slot_new_details) {
				$key = array_search($slot_name, $current);
				if($key !== FALSE) {
					$current[$key] = $slot_new_details;
				}
			}
		}

		if(($vm->hasErrors() || $cfg['maintain_slot_context']) && $cfg['disabled'] !== true) {
			// Check to see if the errors came from a slot!
			$slotErrored = false;

			if ($SPF->hasParameter('previous')) {
				$previous = $SPF->getParameter('previous');
				// Add a extra slot information to the previous list
				// i.e. your slot submits to another module / action which wasn't apart of the original
				if(is_array($cfg['slots_to_add'])) {
					// Allow the user to add a single slot array or an array of slot arrays 
					if (array_key_exists('module', $cfg['slots_to_add'])) {
						$cfg['slots_to_add'] = array($cfg['slots_to_add']);
					}
					foreach($cfg['slots_to_add'] as $name => $slot_new) {
						$previous[$name] = $slot_new;
					}
				}

				$moduleName =  $container->getModuleName();
				$actionName =  $container->getActionName();

				// Loop the previous actions slots and see if there was a slot that errored
				foreach($previous as $slot) {
					if($slot['module'] == $moduleName && $slot['action'] ==  $actionName){
						$slotErrored = true;
						break;
					}
				}
			}

			// A slot errored - we are kicking into action, saving cleaning data, saving vars
			if ($slotErrored) {
				// Collect the VM errors
				// Convert Errors into an array that the VM Manager can accept!
				$errors = array();
				foreach ($vm->getErrors() as $name => $error) {
					foreach ($error['messages'] as $message) {
						$errors[$name] = $message;
					}
				}

				$SPF->setParameter('slot_errors', $errors);

				// Save the request data
				$slotData = $request->getRequestData()->getParameters();
				// Clean the Slot data (i.e. remove PDO instances etc..)
				if(is_array($cfg['slot_data_to_remove'])) {
					foreach ($cfg['slot_data_to_remove'] as $key) {
						unset($slotData[$key]);
					}
				}

				// Save the original Request URI for use in FPF - hacky I know but tough
				$slotData['force_request_uri'] = $request->getRequestUri();
				$SPF->setParameter('slot_data', new AgaviParameterHolder($slotData));

				// Redirect back to the original action - where the slot was nested and the error occured
				$response->setRedirect($slot['url']);
			}
		} else {

			// Check if a previous slot errored and has passed its error information over
			if ($SPF->hasParameter('slot_errors')) {
				$slotData = $SPF->getParameter('slot_data', new AgaviParameterHolder);
				// Populate FPF
				$request->setAttribute('populate', $slotData, 'org.agavi.filter.FormPopulationFilter');
				if ($slotData->hasParameter('force_request_uri')) {
					// Override the uri
					$request->setAttribute('force_request_uri', $slotData->getParameter('force_request_uri'), 'org.agavi.filter.FormPopulationFilter');
				}
				// Pass the saved VM errors
				$vm->setErrors($SPF->getParameter('slot_errors', array()));
			}

			// Clean out old data
			$SPF->removeParameter('slot_errors');
			$SPF->removeParameter('slot_data');
		}

		// Store information of the current request for ze future
		$SPF->setParameter('previous', $SPF->getParameter('current'));
		$SPF->removeParameter('current');
		$storage->write('agavi.org.filter.SlotPopulationFilter', $SPF);
		return;
	}

	/**
	 * Initialize this filter.
	 *
	 * @param      AgaviContext The current application context.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @throws     <b>AgaviFilterException</b> If an error occurs during
	 *                                         initialization
	 *
	 * @author     Zbigniew Swiderski <swidersk@gmail.com>
	 * @author     Ross Lawley <ross.lawley@gmail.com>
	 * @since      ?
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		$this->setParameter('disabled', true);
		$this->setParameter('maintain_slot_context', false);
		$this->setParameter('slots_to_overwrite', array());
		$this->setParameter('slots_to_ignore', array());
		$this->setParameter('slots_to_add', array());
		$this->setParameter('slot_data_to_remove', array());

		// initialize parent
		parent::initialize($context, $parameters);
		$this->setParameter('methods', (array) $this->getParameter('methods'));
		if($ot = $this->getParameter('output_types')) {
			$this->setParameter('output_types', (array) $ot);
		}
	}

	/**
	 * Retrieves information about a slot and any nested layers within the slot
	 *
	 * @param      string The name of a slot
	 * @param      AgaviExecutionContainer a Slot
	 * @return     Array Returns an array of information about a slot
	 *
	 * @author     Ross Lawley <ross.lawley@gmail.com>
	 * @since      ?
	 */
	private function getSlotInformation($name, $slot)
	{
		$slotsInfo = array();
		$slotViewInstance = $slot->getViewInstance();
		if ($slotViewInstance) {
			$slotLayers = $slotViewInstance->getLayers();
			foreach ($slotLayers as $layer) {
				$slotsInfo = $this->getLayersSlotsInformation($layer);
			}
		}

		$slotsInfo[$name] = array(	'module' => $slot->getModuleName(), 
									'action' => $slot->getActionName(),
									'url' => $this->url);
		return $slotsInfo;
	}

	/**
	 * Retrieve a layers slots information
	 *
	 * @param      AgaviTemplateLayer An AgaviTemplateLayer instance
	 * @return     Array Returns an array of information about the layers slots
	 *
	 * @author     Ross Lawley <ross.lawley@gmail.com>
	 * @since      ?
	 */
	private function getLayersSlotsInformation($layer)
	{

		$slotsInfo = array();
		foreach($layer->getSlots() as $name => $slot){
			$slotsInfo = array_merge($slotsInfo, $this->getSlotInformation($name, $slot));
		}
		return $slotsInfo;
	}
}

?>
