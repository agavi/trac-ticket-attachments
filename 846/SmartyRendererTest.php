<?php
class TSRTestWrapperRenderer extends AgaviSmartyRenderer
{
	public function getEngine()
	{
		return parent::getEngine();
	}
}

class SmartyRendererTest extends AgaviTestCase
{
	protected $_r = null;
	
	public function setUp()
	{
		$this->_r = new TSRTestWrapperRenderer();
		$this->_r->initialize(AgaviContext::getInstance('test'));
	}

	public function testGetEngineWithoutSmartyParameter()
	{
		$this->_r->removeParameter('smarty');
		$this->assertType('Smarty', $this->_r->getEngine());
	}

	/**
	 * @expectedException AgaviConfigurationException
	 * @dataProvider invalidSmartyParameterProvider
	 */
	public function testGetEngineWithInvalidSmartyParameter($parameter)
	{
		$this->_r->setParameter('smarty', $parameter);
		$this->_r->getEngine();
	}

	public static function invalidSmartyParameterProvider()
	{
		return array(
			array(array('undefined_variable'=>1)),
			array(array('a')),
			array(array('_smarty_vars'=>array())),
			array(array('_undefined_private_variable'=>1)),
			array(array('template_dir'=>'/hoge', 'undefined_variable'=>1)),
			);
	}

	public function testGetEngine()
	{
		$this->_r->setParameter('smarty', array('template_dir'=>'/foo/var', 'plugins_dir'=>array('/baz/plugins'), 'trusted_dir'=>'/foo/trusted'));
		$smarty = $this->_r->getEngine();
		$this->assertType('Smarty', $smarty);
		$this->assertEquals('/foo/var', $smarty->template_dir);
		$this->assertEquals(array('plugins', 'plugins_local', '/baz/plugins'), $smarty->plugins_dir);
		$this->assertEquals(array('/foo/trusted'), $smarty->trusted_dir);
	}
}
?>