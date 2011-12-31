<?php
/**
 * Context class for testing
 *
 * You can store mock model.
 */
class TestingContext extends AgaviContext
{
    protected $mockmodels = array();

    /**
     * Enqueue mock object.
     *
     *
     * For non-singleton model, 4th argument specify how many times the object could be retrieved.
     * null means anytime and queued mock after that will be ignored.
     * mock's initialize method will be called whenever it's retreived.
     *
     * For singleton model, 3th argument will be stored into singleton instance pool 
     * even if other instance in the pool for the model.
     * initialize method will not be called never.
     *
     * @param string $modelName the model name should be mocked.
     * @param string $moduleName the module name for model which should be mocked.
     * @param AgaviIModel $mock the mock object
     * @param mixed $count
     */
    public function addMockModel($modelName, $moduleName, AgaviIModel $mock, $count = null)
    {
        if($mock instanceof AgaviISingletonModel) {

            $this->singletonModelInstances[self::getModelClassName($modelName, $moduleName)] = $mock;
        }
        $this->mockmodels[self::getModelClassName($modelName, $moduleName)][] = array($mock, $count);
    }

    public function getModel($modelName, $moduleName = null, array $parameters = array()) {
        $class = self::getModelClassName($modelName, $moduleName);
        if(array_key_exists($class, $this->mockmodels)) {
            if($mockItem = reset($this->mockmodels[$class])) {
                list($mock, $count) = $mockItem;
                if($count!==1) $mock = clone $mock; // XXX: I DON'T KNOW this works correct.
                $mock->initialize($this, $parameters);
                if($count!==null && --$count === 0) {
                    array_shift($this->mockmodels[$class]);
                }
                return $mock;
            }
        }
        return  parent::getModel($modelName, $moduleName, $parameters);
    }

    static protected function getModelClassName($modelName, $moduleName)
    {
        $modelName = AgaviToolkit::canonicalName($modelName);
        $class = str_replace('/', '_', $modelName) . 'Model';
        if($moduleName!==null) {
            $class = $moduleName . '_' . $class;
        }
        return $class;
    }
}
?>
