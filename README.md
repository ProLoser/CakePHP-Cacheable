# Cacheable Behavior

Cacheable is a wrapper method for standard queries. It stores each query under a
unique key in the cache when performed. 

## Installation

App_Model or Model:
<pre><code>var $actsAs = array('Cacheable.Cacheable');</code></pre>

## Basic Usage

<pre><code>$data = $this->Model->cache($type, [$query = array(), [$options = array()]])</code></pre>

The <code>Model->cache()</code> method takes 3 arguments:

- $type: the type of Model->find() [first, all, list, etc]
- $query: the options to pass to the Model->find()
- $options: the Model->cache() options 
	- duration: cache duration (default is '10 years')
	- update: if the cache should be updated regardless if it's already set (default false)
		it's best to use this argument when issuing a Model->delete() or save()

## Advanced Usage

You can create custom query methods to be called if you would like to perform complex
operations to the query data. Even your custom methods will have the data stored uniquely
dependent on the parameters passed to it. Simply name your method <code>cache<CustomName>()</code> with
a <code>$params</code> argument and pass the <CustomName> as the query type.

Create your custom method:
<pre><code>MyModel extends AppModel {
	var $name = 'MyModel';
	var $actsAs = array('Cacheable.Cacheable');
	
	function cacheMyQuery($params = array()) {
		// Do Queries
		// Mess with data
		return $data;
	}
}</code></pre>

Pass your custom method name as the $type:
<pre><code>$data = $this->Model->cache('MyQuery', [$myQueryParams, [$options]])</code></pre>