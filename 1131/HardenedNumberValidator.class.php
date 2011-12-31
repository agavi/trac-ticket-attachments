<?php

/**
 * HardenedNumberValidator verifies that a parameter is a number and allows you
 * to apply size constraints.
 * 
 * Parameters:
 *   'type'       number type (int, integer, float or double)
 *   'type_error' error message if number has wrong type
 *   'min'        number must be at least this
 *   'min_error'  error message if number less then 'min'
 *   'max'        number must not be greater then this
 *   'max_error'  error message if number greater then 'max'
 *   'no_locale'  disables the localization
 *   'in_locale'  defines the locale which should be used (leave empty to use the default locale)
 *   'sign_plus'  allows or disallows the use of the plus sign, values are
 *                'forbidden', 'required' and 'optional'
 *   'strict'     whether or not to do strict validation
 *   'cast_to'    cast the value to int/integer or float/double before exporting
 *
 * Defaults:
 *   type      = 'int'         # agavi's default: 'int'
 *   no_locale = 'true'        # agavi's default: 'false'
 *   in_locale = not defined   # agavi's default: not defined
 *   strict    = 'true'        # agavi does not support strict mode
 *                             # use 'false' to be backwards compatible
 *   sign_plus = 'forbidden'   # agavi does not support sign_plus;
 *                             # use 'optional' to be backwards compatible
 *
 * Hint:
 *   Strict mode throws an error when scientific notation is used. When
 *   NumberValidator operates in strict modes only the following characters
 *   are allowed: ^[0-9\-\+\.,]$ - even whitespaces are disallowed!
 *
 *   HardenedNumberValidator is not full hardended because the validator itself
 *   depends on the AgaviDecimalFormatter, which has a bug. For more information
 *   see ticket #xyz on "trac.agavi.org".
 *
 * @package    hardened-agavi
 * @subpackage validator
 *
 * @author     Dennis Meckel <meckel@datenschuppen.de>
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 * @copyright  Agavi Hardened Project (agavi.datenschuppen.de)
 *
 * @since      0.11.0
 *
 * @version    hardened-1.0.1-0
 */
class HardenedNumberValidator extends AgaviValidator
{
	/**
	 * Validates the input
	 * 
	 * @return     bool The input is valid number according to given parameters.
	 *
	 * @author     Dennis Meckel <meckel@datenschuppen.de>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function validate()
	{
		$value = $this->getData($this->getArgument());

		if(!is_scalar($value)) {
			$this->throwError();
			return false;
		}

		// sign_plus validation
		if($value[0] == '+' && 'forbidden' == $this->getParameter('sign_plus', 'forbidden')
		or $value[0] != '+' && 'required'  == $this->getParameter('sign_plus')) {
				$this->throwError('type');
				return false;
		}

		// strict validation
		if($this->getParameter('strict', true)) {
			// should be faster than a regular expression (?)
			if('' != $tmp = strtr($value, array_fill_keys(array(1,2,3,4,5,6,7,8,9,0,'.',',','-','+'), ''))) {
				$this->throwError('type');
				return false;
			}
		}

		if(AgaviConfig::get('core.use_translation') && !$this->getParameter('no_locale', true)) {
			$locale = $this->getContext()->getTranslationManager()->getLocale(
				$this->getParameter('in_locale', $this->getContext()->getTranslationManager()->getCurrentLocaleIdentifier())
			);

			$hasExtraChars_REFERENCE = false;
			$value = AgaviDecimalFormatter::parse($value, $locale, $hasExtraChars_REFERENCE);

			if($hasExtraChars_REFERENCE) {
				$this->throwError('type');
				return false;
			}
		} else {
			if(is_numeric($value)) {
				if(((int) $value) == $value) {
					$value = (int) $value;
				} else {
					$value = (float) $value;
				}
			}
		}

		switch(strtolower($this->getParameter('type'))) {
			case 'int':
			case 'integer':
				if(!is_int($value)) {
					$this->throwError('type');
					return false;
				}

				break;
			
			case 'float':
			case 'double':
				if(!is_float($value) && !is_int($value)) {
					$this->throwError('type');
					return false;
				}

				break;
		}

		switch(strtolower($this->getParameter('cast_to'))) {
			case 'int':
			case 'integer':
				$value = (int) $value;
				break;

			case 'float':
			case 'double':
				$value = (float) $value;
				break;
		}

		if($this->hasParameter('min') && $value < $this->getParameter('min')) {
			$this->throwError('min');
			return false;
		}

		if($this->hasParameter('max') && $value > $this->getParameter('max')) {
			$this->throwError('max');
			return false;
		}

		return true;
	}
}

?>