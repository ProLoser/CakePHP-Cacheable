<?php
/**
 * Cacheable Model Behavior
 * 
 * Stores model queries in cache
 *
 * @package Cacheable
 * @author Dean Sofer
 * @version $Id$
 * @copyright 
 * @dependencies Clear_Cache Plugin by Ceeram https://github.com/ceeram/clear_cache
 **/
class CacheableBehavior extends ModelBehavior {

	/**
	 * Contains configuration settings for use with individual model objects.
	 * Individual model settings should be stored as an associative array, 
	 * keyed off of the model name.
	 *
	 * @var array
	 * @access public
	 * @see Model::$alias
	 */
	var $_settings = array();

	/**
	 * Initiate Cacheable Behavior
	 *
	 * @param object $model
	 * @param array $config
	 * @return void
	 * @access public
	 */
	function setup(&$model, $config = array()) {
		$defaults = array(
			'engine' => 'File',
			'duration' => '+1 hour',
			'configured' => false,
		);
		$this->_settings[$model->alias] = array_merge($defaults, $config);
	}
	
	/**
	 * Sets up the cache configurations for cacheable and checks directory existence.
	 * Runs a maximum of 1 time per model per request to reduce overhead!
	 *
	 * @param string $model 
	 * @param string $type 
	 * @param string $duration 
	 * @return void
	 * @author Dean
	 */
	protected function _configure(&$model, $duration = null) {
		if (!$this->_settings[$model->alias]['configured']) {
			if ($this->_settings[$model->alias]['engine'] == 'File') {
				if (!is_dir(CACHE . 'cacheable')) {
					mkdir(CACHE . 'cacheable');
				}
				if (!is_dir(CACHE . 'cacheable' . DS . $model->alias)) {
					mkdir(CACHE . 'cacheable' . DS . $model->alias);
				}
			}
			if (!$duration) {
				$duration = $this->_settings[$model->alias]['duration'];
			}
			Cache::config('cacheable' . $model->alias, array(
				'engine' => $this->_settings[$model->alias]['engine'],
				'path' => CACHE . 'cacheable' . DS . $model->alias . DS,
				'duration' => $duration,
				'prefix' => '',
			));
			$this->_settings[$model->alias]['configured'] = true;
		}
	}

	/**
	 * Checks the cache for query results, if none are found a new query is made
	 * and the results are stored to a unique key for the query. Also works with
	 * complex operations by using Model methods called cache<QueryName>($options)
	 *
	 * @param string $model 
	 * @param string $type 
	 * @param string $query 
	 * @param string $options 
	 * @return void
	 * @author Dean Sofer
	 */
	public function cache(&$model, $type, $queryOptions = array(), $options = array()) {
		$options = array_merge(array(
			'duration' => null,
			'update' => false,
		), $options);
		
		$key = $this->generateCacheKey($model, $type, $queryOptions);
		
		if ($options['update']) {
			$this->deleteCache($model, $key);
		}
		
		$data = $this->getCache($model, $key);
		if ($data === false) {
			$data = $model->find($type, $queryOptions);
			$this->setCache($model, $key, $data);
		}
		return $data;
	}
	
	/**
	 * Generates a unique key based on the find type and query parameters
	 *
	 * @param string $type 
	 * @param string $queryOptions 
	 * @return void
	 * @author Dean
	 */
	public function generateCacheKey(&$model, $type, $queryOptions = array()) {
		// Just in case the model gets imported too early
		App::import('Core', 'Security');
		if (isset($model->locale)) {
			return $model->locale . '_' . $type . '_' . Security::hash(serialize($queryOptions));
		} else {
			return $type . '_' . Security::hash(serialize($queryOptions));
		}
	}
	
	public function deleteCache(&$model, $key = null) {
		$this->_configure($model);
		App::import('Libs', 'ClearCache.ClearCache');
		$ClearCache = new ClearCache();
		
		if ($key) {
			return Cache::delete($key, 'cacheable' . $model->alias);
		} else {
			return $ClearCache->files('cacheable' . DS . $model->alias);
		}
	}
	
	public function getCache(&$model, $key) {
		$this->_configure($model);
		return Cache::read($key, 'cacheable' . $model->alias);
	}
	
	public function setCache(&$model, $key, $data) {
		$this->_configure($model);
		return Cache::write($key, $data, 'cacheable' . $model->alias);
	}
	
	public function afterSave(&$model, $created) {
		$this->deleteCache($model);
	}
	
	public function afterDelete(&$model) {
		$this->deleteCache($model);
	}

}