<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2008 the Agavi Project.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org/LICENSE.txt                   |
// |   vi: set noexpandtab:                                                    |
// |   Local Variables:                                                        |
// |   indent-tabs-mode: t                                                     |
// |   End:                                                                    |
// +---------------------------------------------------------------------------+

/**
 * A renderer produces the output as defined by a View
 *
 * @package    agavi
 * @subpackage renderer
 *
 * @author     Jordi Boggiano <j.boggiano@seld.be>
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id: AgaviSmartyRenderer.class.php 2258 2008-01-03 16:54:04Z david $
 */
class DwooRenderer extends AgaviRenderer implements AgaviIReusableRenderer
{
	/**
	 * @constant   string The directory inside the cache dir where templates will
	 *                    be stored in compiled form.
	 */
	const COMPILE_DIR = 'templates';

	/**
	 * @constant   string The subdirectory inside the compile dir where templates
	 *                    will be stored in compiled form.
	 */
	const COMPILE_SUBDIR = 'dwoo';

	/**
	 * @constant   string The directory inside the cache dir where cached content
	 *                    will be stored.
	 */
	const CACHE_DIR = 'dwoo';

	/**
	 * @var        Dwoo Dwoo template engine.
	 */
	protected $dwoo = null;

	/**
	 * @var        string A string with the default template file extension,
	 *                    including the dot.
	 */
	protected $defaultExtension = '.html';

	/**
	 * Pre-serialization callback.
	 *
	 * Excludes the Dwoo instance to prevent excessive serialization load.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function __sleep()
	{
		$keys = parent::__sleep();
		unset($keys[array_search('dwoo', $keys)]);
		return $keys;
	}

	/**
	 * Grab a cleaned up dwoo instance.
	 *
	 * @return     Dwoo A Dwoo instance.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	protected function getEngine()
	{
		if($this->dwoo) {
			return $this->dwoo;
		}

		if(!class_exists('Dwoo')) {
			if(defined('DWOO_DIR') ) {
				// if DWOO_DIR constant is defined, we'll use it
				require(DWOO_DIR . 'Dwoo.php');
			} else {
				// otherwise we resort to include_path
				require('Dwoo.php');
			}
		}

		$parentMode = fileperms(AgaviConfig::get('core.cache_dir'));

		$compileDir = AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR . self::COMPILE_DIR . DIRECTORY_SEPARATOR . self::COMPILE_SUBDIR;
		AgaviToolkit::mkdir($compileDir, $parentMode, true);

		$cacheDir = AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR . self::CACHE_DIR;
		AgaviToolkit::mkdir($cacheDir, $parentMode, true);

		$this->dwoo = new Dwoo($compileDir, $cacheDir);

		$this->dwoo->getLoader()->addDirectory(dirname(__FILE__).'/plugins');
//		$this->dwoo->getLoader()->addDirectory("plugins_local");

		return $this->dwoo;
	}

	/**
	 * Render the presentation and return the result.
	 *
	 * @param      AgaviTemplateLayer The template layer to render.
	 * @param      array              The template variables.
	 * @param      array              The slots.
	 * @param      array              Associative array of additional assigns.
	 *
	 * @return     string A rendered result.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function render(AgaviTemplateLayer $layer, array &$attributes = array(), array &$slots = array(), array &$moreAssigns = array())
	{
		$engine = $this->getEngine();

		$data = array();
		if($this->extractVars) {
			$data = $attributes;
		} else {
			$data[$this->varName] = &$attributes;
		}

		$data[$this->slotsVarName] =& $slots;

		foreach($this->assigns as $key => $getter) {
			$data[$key] = $this->context->$getter();
		}

		foreach($moreAssigns as $key => &$value) {
			if(isset($this->moreAssignNames[$key])) {
				$key = $this->moreAssignNames[$key];
			}
			$data[$key] =& $value;
		}

		return $engine->get($layer->getResourceStreamIdentifier(), $data);
	}
}

?>