# Cacheable Behavior

Cacheable is a wrapper method for standard queries. It stores each query result under a
unique key based on the find type and parameters in the cache when performed. If the 
cached data already exists, it will use it instead of querying the database again.

Cache is automatically wiped out for a model during  the afterSave and afterDelete of
that model. You will have to manually clear the cache for deepfinds however.

## Requires

You must have the [clear_cache plugin by Ceeram](https://github.com/ceeram/clear_cache) installed in order for this to work.

## Installation

App_Model or Model:
<pre><code>var $actsAs = array('Cacheable.Cacheable');</code></pre>

## Basic Usage

To use, simply substitute any instance of <code>Model->find()</code> with <code>Model->cache()</code>. That's all there is to it.

<pre><code>$data = $this->Model->cache($type, [$queryOptions = array(), [$options = array()]])</code></pre>

- $type: the type of <code>Model->find()</code> [first, all, list, custom, etc]
- $queryOptions: the options to pass to the <code>Model->find()</code>
- $options: the <code>cache()</code> specific options 
	- duration: cache duration (default is '1 hour')
	- update: forces the cached data to refresh (default false)
	
## Todo

- **Place deepfind cache into subfolders.** When manually clearing the cache from a related model,
	this will allow only a subset of the cache to be cleared instead.