<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2011-2014 Belavier Commerce LLC

  This source file is subject to Open Software License (OSL 3.0)
  License details is bundled with this package in the file LICENSE.txt.
  It is also available at this URL:
  <http://www.opensource.org/licenses/OSL-3.0>

 UPGRADE NOTE:
   Do not edit or add to this file if you wish to upgrade AbanteCart to newer
   versions in the future. If you wish to customize AbanteCart for your
   needs please refer to http://www.AbanteCart.com for more information.
------------------------------------------------------------------------------*/
if (! defined ( 'DIR_CORE' )) {
	header ( 'Location: static_pages/' );
}
/**
 * Class ALoader
 */
final class ALoader {
	/**
	 * @var Registry
	 */
	public $registry;

	/**
	 * @param $registry Registry
	 */
	public function __construct($registry) {
		$this->registry = $registry;
	}
	
	public function __get($key) {
		return $this->registry->get($key);
	}

	public function __set($key, $value) {
		$this->registry->set($key, $value);
	}

	/**
	 * @param string $library
	 * @throws AException
	 */
	public function library($library) {
		$file = DIR_CORE . 'lib/' . $library . '.php';
		
		if (file_exists($file)) {
			/** @noinspection PhpIncludeInspection */
			include_once($file);
		} else {
			throw new AException(AC_ERR_LOAD, 'Error: Could not load library ' . $library . '!');
		}
	}

	/**
	 * @param string $model
	 * @param string $mode
	 * @return bool
	 * @throws AException
	 */
	public function model($model, $mode = '') {

		//force mode alows to load models for ALL extensions to bypass extension enabled only status
		$force = '';
		if ($mode == 'force') {
			$force = 'all';
		}
		
        $file  = DIR_APP_SECTION . 'model/' . $model . '.php';
        if ( $this->registry->has('extensions') && $result = $this->extensions->isExtensionResource('M', $model, $force) ) {
            if ( is_file($file) ) {
                $warning = new AWarning("Extension <b>{$result['extension']}</b> override model <b>$model</b>" );
                $warning->toDebug();
            }
            $file = $result['file'];
        }

		$class = 'Model' . preg_replace('/[^a-zA-Z0-9]/', '', $model);

		if (file_exists($file)) {
			include_once($file);
			$this->registry->set('model_' . str_replace('/', '_', $model), new $class($this->registry));
		} else if ( $mode != 'silent' ) {
			throw new AException(AC_ERR_LOAD, 'Error: Could not load model ' . $model . ' from ' . $file);
		}else{
            return false;
        }
	}

	/**
	 * @param string $driver
	 * @param string $hostname
	 * @param string $username
	 * @param string $password
	 * @param string $database
	 * @param string | null $prefix
	 * @param string $charset
	 * @throws AException
	 */
	public function database($driver, $hostname, $username, $password, $database, $prefix = NULL, $charset = 'UTF8') {
		$file  = DIR_DATABASE . $driver . '.php';
		$class = 'Database' . preg_replace('/[^a-zA-Z0-9]/', '', $driver);
		
		if (file_exists($file)) {
			include_once($file);
			
			$this->registry->set(str_replace('/', '_', $driver), new $class());
		} else {
            throw new AException(AC_ERR_LOAD, 'Error: Could not load database ' . $driver . '!');
		}
	}

	/**
	 * @param string $helper
	 * @throws AException
	 */
	public function helper($helper) {
		$file = DIR_CORE . 'helper/' . $helper . '.php';
	
		if (file_exists($file)) {
			include_once($file);
		} else {
            throw new AException(AC_ERR_LOAD, 'Error: Could not load helper ' . $helper . '!');
		}
	}

	/**
	 * @param string $config
	 */
	public function config($config) {
		$this->config->load($config);
	}

	/**
	 * @param string $language
	 * @param string $mode
	 * @return array|null|void
	 */
	public function language($language, $mode = '') {
		return $this->language->load($language, $mode);
	}
}
