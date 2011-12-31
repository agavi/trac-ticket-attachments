<?php

/**
 * HardenedEqualsValidator verifies if a parameter equals to a given value.
 * 
 * The input is compared to a value and the validator fails if they differ.
 * When the parameter 'asparam' is true, the content in 'value' is taken as a
 * parameter name and the check is performed against it's value otherwise the
 * content in 'value' is taken.
 * 
 * Parameters:
 *   'value'   value which the input should equals to
 *   'asparam' whether the 'value' should be treated as a parameter name
 *   'strict'  whether or not to do strict type comparisons
 *
 * Defaults:
 *   strict  = 'true'          # agavi does not support strict mode yet
 *   asparam = 'false'         # agavi's default: 'false'
 *
 * @package    hardened-agavi
 * @subpackage validator
 *
 * @author     Dennis Meckel <meckel@datenschuppen.de>
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 * @copyright  Hardened Agavi Project (agavi.datenschuppen.de)
 *
 * @since      0.11.0
 *
 * @version    hardened-1.0.1-0
 */
class HardenedEqualsValidator extends AgaviValidator
{
	/**
	 * Validates the input.
	 * 
	 * @return     bool The input equals to given value.
	 * 
	 * @author     Dennis Meckel <meckel@datenschuppen.de>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function validate()
	{
		// if we have a value we compare all arguments to that value and report the 
		// individual arguments that failed
		if($this->hasParameter('value')) {
			$value = $this->getParameter('value');
			if($this->getParameter('asparam', false)) { 
				$value = $this->getData($value); 
			}
		} else {
			$value = $this->getData($this->getArgument());
		}

		foreach($this->getArguments() as $key => $argument) {
			if($this->getParameter('strict', true) && $this->getData($argument) !== $value
			or !$this->getParameter('strict', true) && $this->getData($argument) != $value) {
				$this->throwError();
				return false;
			}
		}

		$this->export($value);

		return true;
	}
}

?>