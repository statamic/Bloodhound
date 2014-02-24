<?php
/**
 * Bloodhound: Tasks
 * Go get the content, boy! Go get it! Get the content! Good boy!
 * http://statamic.com/add-ons/bloodhound
 */
class Tasks_bloodhound extends Tasks
{
	/**
	 * Looks up a given $query with the given $config
	 *
	 * @param string  $query  String to look up in data
	 * @param array  $config  Array of configuration options
	 * @return array
	 */
	public function lookUp($query, $config)
	{
		// require the class
		require_once('comb.php');

		// create a hash for this search
		$hash = 'searches/' . $this->getSearchHash($config);

		// while we're here, clean up old unusable cached searches
		if ($config['query_cache_length'] == "on cache update") {
			$this->cache->purgeFromBefore(Cache::getLastCacheUpdate(), 'searches');
		} else {
			$this->cache->purgeOlderThan($config['query_cache_length'] * 60, 'searches');
		}

		// perform the search
		if ($this->cache->exists($hash)) {
			// use cached results, they're still relevant
			$output = unserialize($this->cache->get($hash));

			// record search
			if (isset($output['not_enough_characters'])) {
				// do nothing
			} elseif (isset($output['no_query']) || isset($output['no_results'])) {
				$this->logSearch($config['query'], 0);
			} elseif (isset($output['too_many_results'])) {
				$this->logSearch($config['query'], 'too many');
			} else {
				$this->logSearch($config['query'], count($output));
			}
		} else {
			// get data
			$data = $this->getData($config);

			try {
				$comb = Comb::create($data, $config);

				// look up query
				$results = $comb->lookUp($query);

				// fix results
				$output = array();
				$i      = 1;
				foreach ($results['data'] as $result) {
					$item = array(
						'_score'        => $result['score'],
						'_category'     => $result['category'],
						'_query'        => $results['info']['raw_query'],
						'_parsed_query' => $results['info']['parsed_query'],
						'_query_time'   => $results['info']['query_time']
					);

					array_push($output, array_merge($item, $result['data']));

					$i++;
				}

				// save this
				$this->cache->put($hash, serialize($output));

				// record successful search
				$this->logSearch($config['query'], count($output));
			} catch (CombNoResultsFoundException $e) {
				$output = array(array('no_results' => true, '_query' => htmlentities($query)));
				$this->cache->put($hash, serialize($output));

				// record failed search
				$this->logSearch($config['query'], 0);
			} catch (CombNoQueryException $e) {
				$this->log->error($e->getMessage());
				$output = array(array('no_query' => true, '_query' => htmlentities($query)));
				$this->cache->put($hash, serialize($output));

				// record failed search
				$this->logSearch($config['query'], 0);
			} catch (CombTooManyResultsException $e) {
				$this->log->error($e->getMessage());
				$output = array(array('too_many_results' => true, '_query' => htmlentities($query)));
				$this->cache->put($hash, serialize($output));

				// record failed search
				$this->logSearch($config['query'], 'too many');
			} catch (CombNotEnoughCharactersException $e) {
				$output = array(array('not_enough_characters' => true, 'no_query' => true, 'no_results' => true, '_query' => htmlentities($query)));
				$this->cache->put($hash, serialize($output));
			} catch (CombException $e) {
				$this->log->error($e->getMessage());
				$output = array(array('no_results' => true, '_query' => htmlentities($query)));
				$this->cache->put($hash, serialize($output));

				// record failed search
				$this->logSearch($config['query'], 0);
			}
		}

		return $output;
	}


	/**
	 * Loads the data file and merges all configs together
	 *
	 * @param string  $dataset  Dataset to attempt to load
	 * @param array  $parameters  Parameters that were called on the plugin
	 * @return array
	 */
	public function loadDataset($dataset, $parameters=array())
	{
		// remove null values from parameters
		foreach ($parameters as $key => $value) {
			if (is_null($value)) {
				unset($parameters[$key]);
			}
		}

		// set some defaults if nothing is set
		$default_values = array(
			'use_stemming' => false,
			'use_alternates' => false,
			'stop_words' => array('the', 'a', 'an'),
			'query_cache_length' => 30,

			'log_successful_searches' => true,
			'log_failed_searches' => true,

			'folders' => URL::getCurrent(),
			'taxonomy' => false,
			'show_hidden' => false,
			'show_drafts' => false,
			'show_future' => false,
			'show_past' => true,
			'type' => 'all',

			'limit' => 10,
			'offset' => 0,
			'paginate' => true,
			'query_variable' => 'query',
			'include_content' => true,
            
            'include_404' => false
		);

		// a complete list of all possible config variables
		$config = array(
			'match_weights' => null,
			'min_characters' => null,
			'min_word_characters' => null,
			'score_threshold' => null,
			'property_weights' => null,
			'query_mode' => null,
			'use_stemming' => null,
			'use_alternates' => null,
			'include_full_query' => null,
			'enable_too_many_results' => null,
			'sort_by_score' => null,
			'exclude_properties' => null,
			'include_properties' => null,
			'stop_words' => null,

			'log_successful_searches' => null,
			'log_failed_searches' => null,

			'query_cache_length' => null,

			'folders' => null,
			'taxonomy' => null,
			'show_hidden' => null,
			'show_drafts' => null,
			'since' => null,
			'until' => null,
			'show_future' => null,
			'show_past' => null,
			'type' => null,
			'conditions' => null,

			'limit' => null,
            'page_limit' => null,
			'offset' => null,
			'paginate' => null,
			'query_variable' => null,
			'include_content' => null,
            
            'include_404' => null,
            'exclude' => null,

			'query' => null
		);

		$loaded_config = array();
		if ($dataset) {
			$dataset_file = $file = Config::getConfigPath() . '/add-ons/' . $this->addon_name . '/datasets/' . $dataset . '.yaml';
			if (File::exists($dataset_file)) {
				$loaded_config = YAML::parseFile($dataset_file);
			} else {
				$this->log->error("Could not use dataset `" . $dataset . "`, YAML file does not exist.");
			}
		}

		// load global config
		$global_config = Helper::pick($this->getConfig(), array());

		// merge config variables in order
		$all_config = array_merge($config, $default_values, $global_config, $loaded_config, $parameters);

		// get query
		if (!isset($parameters['query']) && is_null($all_config['query']) && $all_config['query_variable']) {
			$new_query = filter_input(INPUT_GET, $all_config['query_variable']);
			$all_config['query'] = $new_query;
		}

		// always add content to exclude properties, don't worry, content_raw will take care of it
		if (is_array($all_config['exclude_properties'])) {
			$all_config['exclude_properties'] = array_unique(array_merge($all_config['exclude_properties'], array('content')));
		} else {
			$all_config['exclude_properties'] = array('content');
		}

		return $all_config;
	}


	/**
	 * Gets the target data from the cache
	 *
	 * @param array  $config  Configuration array
	 * @return array
	 */
	public function getData($config)
	{
		// load data
		if ($config['taxonomy']) {
			$taxonomy_parts = Taxonomy::getCriteria(URL::getCurrent());
			$taxonomy_type  = $taxonomy_parts[0];
			$taxonomy_slug  = Config::get('_taxonomy_slugify') ? Slug::humanize($taxonomy_parts[1]) : urldecode($taxonomy_parts[1]);

			$content_set = ContentService::getContentByTaxonomyValue($taxonomy_type, $taxonomy_slug, $config['folders']);
		} else {
			$content_set = ContentService::getContentByFolders($config['folders']);
		}

		// filters
		$content_set->filter($config);
        
        // custom filter, remove the 404 page if needed
        if (!$config['include_404']) {
            $content_set->customFilter(function($item) {
                return ($item['url'] !== '/404');
            });
        }
        
        // custom filter, remove any excluded folders
        if ($config['exclude']) {
            $excluded = Parse::pipeList($config['exclude']);
            $content_set->customFilter(function($item) use ($excluded) {
                foreach ($excluded as $exclude) {
                    if ($exclude === "*" || $exclude === "/*") {
                        // exclude all
                        return false;
                    } elseif (substr($exclude, -1) === "*") {
                        // wildcard check
                        if (strpos($item['_folder'], substr($exclude, 0, -1)) === 0) {
                            return false;
                        }
                    } else {
                        // plain check
                        if ($exclude == $item['_folder']) {
                            return false;
                        }
                    }
                }
                
                return true;
            });
        }

		$content_set->supplement(array(
			'merge_with_data' => false
		));

		$content_set->prepare($config['include_content']);

		$data = $content_set->get();

		return $data;
	}


	/**
	 * Create a unique search hash based on all of the configuration options
	 *
	 * @param array  $config  Configuration array
	 * @return string
	 */
	public function getSearchHash($config)
	{
		return md5(serialize($config));
	}


	/**
	 * Supplements a list of results with first, last, count, and total_results
	 *
	 * @param array  $output  The data array to supplement
     * @param array  $other_data  Other data to add to each record
	 * @return mixed
	 */
	public function supplement($output, $other_data=array())
	{
		$i = 1;
		$length = count($output);
        
        $other_data = Helper::ensureArray($other_data);

		foreach ($output as $key => $item) {
			$stats = array(
				'first'         => ($i === 1),
				'last'          => ($i === $length),
				'count'         => $i,
				'total_results' => $length
			);

			$output[$key] = $other_data + $stats + $item;

			$i++;
		}

		return $output;
	}


	/**
	 * Logs that a search was performed
	 *
	 * @param string  $query  The string that was queried
	 * @param string|int  $results  The number of results
	 * @return void
	 */
	public function logSearch($query, $results)
	{
		$searches = Helper::pick($this->session->get('searches'), array());
		$plural   = ($results === 1) ? '' : 's';
		$query    = htmlentities($query);

		// check to see if this session has performed this search before
		// this prevents multiple page views from being tagged as multiple searches
		if (!isset($searches[$query])) {
			// mark as stored
			$searches[$query] = true;
			$this->session->set('searches', $searches);

			// make a note in the log
			if ($results) {
				// log `info` message
				if ($this->fetchConfig('log_successful_searches', true, null, true, false)) {
					$this->log->info('Someone searched for *' . $query . '* and found ' . $results . ' result' . $plural . '.');
				}

				// set file to update
				$file = 'successful_searches.yaml';
			} else {
				// log `warn` message
				if ($this->fetchConfig('log_successful_searches', true, null, true, false)) {
					$this->log->warn('Someone searched for *' . $query . '* and no results were found.');
				}

				// set file to update
				$file = 'failed_searches.yaml';
			}

			$search_list = $this->cache->getYAML($file, array());

			if (isset($search_list[$query])) {
				$search_list[$query] = $search_list[$query] + 1;
			} else {
				$search_list[$query] = 1;
			}

			$this->cache->putYAML($file, $search_list);
		}
	}
}