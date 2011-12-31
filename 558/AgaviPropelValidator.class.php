<?php

/**
 * AgaviPropelValidator allows you to use propel validators for validation.
 * 
 * Parameters:
 *   'column'  the table column that does validate this input
 *   'model'   the model owner of this column
 *
 * @package    oe3
 * @subpackage validators
 *
 * @author     André Fiedler <kontakt@visualdrugs.net>
 * @copyright  André Fiedler
 *
 * @since      3.0
 */
class AgaviPropelValidator extends AgaviValidator
{
	/**
	 * We need to return true here when this validator is required, because
	 * otherwise the is*ValueEmpty check would make empty but set fields not
	 * reach the validate method.
	 *
	 * @see        AgaviValidator::checkAllArgumentsSet
	 * @author     André Fiedler <kontakt@visualdrugs.net>
	 * @since      3.0
	 */
	protected function checkAllArgumentsSet($throwError = true)
	{
		return true;
	}

	/**
	 * Validates the input.
	 *
	 * @return     bool True if the column-validation is valid according to the given parameters
	 *
	 * @author     André Fiedler <kontakt@visualdrugs.net>
	 * @since      3.0
	 */
	protected function validate()
	{
		if(!$this->hasParameter('model')) throw new AgaviException('No model found for argument ' . $this->getArgument());
		if(!$this->hasParameter('column')) throw new AgaviException('No column found for argument ' . $this->getArgument());

		$str = $this->getParameter('model') . '';
		$model = new $str;
		$model_peer = $model->getPeer();
		$column = $this->getParameter('column');
		
		$method = 'set' . $model_peer->translateFieldName($column, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_PHPNAME);
		$model_column = $model_peer->translateFieldName($column, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_COLNAME);

		$class = new ReflectionClass($model);
		$class->getMethod($method)->invoke($model, $this->getData($this->getArgument()));
		
		$result = $model_peer->doValidate($model, $model_column);
		if($result !== true)
		{
			$index = array_search($this->getArgument() ,$this->arguments);
			$this->errorMessages[$index] = $result[$model_column]->getMessage();
			$this->throwError($index);
			return false;
		}
		
		$this->export($this->getData($this->getArgument()));

		return true;
	}
}

?>