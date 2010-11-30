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
		);
		$this->_settings[$model->alias] = array_merge($defaults, $config);
		
		$this->_configure($model, $this->_settings[$model->alias]['duration']);
	}
	
	/**
	 * Sets up the cache configurations for cacheable
	 *
	 * @param string $model 
	 * @param string $type 
	 * @param string $duration 
	 * @return void
	 * @author Dean
	 */
	protected function _configure(&$model, $duration = null) {
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
		Cache::config('cacheable', array(
			'engine' => $this->_settings[$model->alias]['engine'],
			'path' => CACHE . 'cacheable' . DS . $model->alias . DS,
			'duration' => $duration,
			'prefix' => '',
		));
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
		
		if (!$data = $this->getCache($model, $key)) {
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
		return $type . '_' . Security::hash(serialize($queryOptions));
	}
	
	public function deleteCache(&$model, $key = null) {
		App::import('Libs', 'ClearCache.ClearCache');
		$ClearCache = new ClearCache();
		
		if ($key) {
			return Cache::delete($model, $key, 'cacheable');
		} else {
			return $ClearCache->files('cacheable' . DS . $model->alias);
		}
	}
	
	public function getCache(&$model, $key) {
		return Cache::read($key, 'cacheable');
	}
	
	public function setCache(&$model, $key, $data) {
		return Cache::write($key, $data, 'cacheable');
	}
	
	public function afterSave(&$model, $created) {
		$this->deleteCache($model);
	}
	
	public function afterDelete(&$model) {
		$this->deleteCache($model);
	}

}