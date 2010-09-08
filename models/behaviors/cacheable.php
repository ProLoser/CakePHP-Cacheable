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

	}


	public function cache(&$model, $type, $query = array(), $options = array()) {
		$options = array_merge(array(
			'duration' => '999 years',
			'update' => false,
		), $options);
		$key = Security::hash(serialize($query));
		Cache::config('cacheable', array(
			'engine' => 'File',
			'path' => CACHE . 'cacheable' . DS . $model->name . DS,
			'duration' => $options['duration'],
		    'prefix' => 'cake_query_',
		));
		if ($options['update']) {
			Cache::delete($type . '.' . $key, 'cacheable');
		}
		
		if (!$data = Cache::read($type . '.' . $key, 'cacheable')) {
			if (method_exists($model, 'cache' . $type)) {
				$data = call_user_func_array('cache' . $type, $query);
			} else {
				$data = $model->find($type, $query);
			}
			Cache::write($type . '.' . $key, $data, 'cacheable');
		}
		return $data;
	}

}