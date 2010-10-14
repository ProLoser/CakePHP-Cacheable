# Cacheable Behavior

Cacheable is a wrapper method for standard queries. It stores each query under a
unique key in the cache when performed. 

## Installation

App_Model or Model:
<pre><code>var $actsAs = array('Cacheable.Cacheable');</code></pre>

## Basic Usage

<pre><code>$data = $this->Model->cache($type, [$queryOptions = array(), [$options = array()]])</code></pre>

The <code>Model->cache()</code> method takes 3 arguments:

- $type: the type of Model->find() [first, all, list, custom, etc]
- $queryOptions: the options to pass to the Model->find()
- $options: the Model->cache() options 
	- duration: cache duration (default is '1 hour')
	- update: if the cache should be updated regardless if it's already set (default false)
		it's best to use this argument when issuing a Model->delete() or save()