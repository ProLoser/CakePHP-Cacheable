<?php
/**
 * Cacheable Model Behavior
 * 
 * Stores model queries in cache
 *
 * @package default
 * @author Dean
 * @version $Id$
 * @copyright 
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
		);
		$this->_settings[$model->name] = array_merge($defaults, $config);
		
		$this->_initialize($model, $options['duration']);
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
	protected function _configure(&$model, $duration = '+1 hour') {
		if (!is_dir(CACHE . 'cacheable')) {
			mkdir(CACHE . 'cacheable');
		}
		Cache::config('cacheable', array(
			'engine' => $this->_settings[$model->name]['engine'],
			'path' => CACHE . 'cacheable' . DS,
			'duration' => $duration,
			'prefix' => $model->name,
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
			'duration' => '+1 hour',
			'update' => false,
		), $options);
		
		$key = $this->generateKey($type, $queryOptions);
		
		if ($options['update']) {
			$this->deleteCache($key);
		}
		
		if (!$data = $this->getCache($key)) {
			$data = $model->find($type, $queryOptions);
			$this->setCache($key, $data);
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
	public function generateCacheKey($type, $queryOptions = array()) {
		return Security::hash($type . serialize($queryOptions));
	}
	
	public function deleteCache($key) {
		return Cache::delete($key, 'cacheable');
	}
	
	public function getCache($key) {
		return Cache::read($key, 'cacheable');
	}
	
	public function setCache($key, $data) {
		return Cache::write($key, $data, 'cacheable');
	}
	
	

}