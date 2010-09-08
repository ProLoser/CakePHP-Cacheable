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
	var $settings = array();

/**
 * Allows the mapping of preg-compatible regular expressions to public or
 * private methods in this class, where the array key is a /-delimited regular
 * expression, and the value is a class method.  Similar to the functionality of
 * the findBy* / findAllBy* magic methods.
 *
 * @var array
 * @access public
 */
	var $mapMethods = array();


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
		$this->settings[$model->name] = array_merge($defaults, $config);
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
	public function cache(&$model, $type, $query = array(), $options = array()) {
		$options = array_merge(array(
			'duration' => '10 years',
			'update' => false,
		), $options);
		
		$this->__config($model, $type, $options['duration']);
		
		$key = Security::hash(serialize($query));
		
		if ($options['update']) {
			Cache::delete($key, 'cacheable');
		}
		
		if (!$data = Cache::read($key)) {
			if (method_exists($model, 'cache' . $type)) {
				$data = call_user_func_array('cache' . $type, $query);
			} else {
				$data = $model->find($type, $query);
			}
			Cache::write($key, $data, 'cacheable');
		}
		return $data;
	}
	
	private function __config(&$model, $type, $duration = '10 years') {
		if (!is_dir(CACHE . 'cacheable')) {
			mkdir(CACHE . 'cacheable');
		}
		Cache::config('cacheable', array(
			'engine' => $this->settings[$model->name]['engine'],
			'path' => CACHE . 'cacheable' . DS,
			'duration' => $duration,
			'prefix' => $model->name . '_' . $type,
		));
	}

}