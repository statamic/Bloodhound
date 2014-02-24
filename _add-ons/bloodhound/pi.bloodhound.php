<?php
/**
 * Bloodhound: Plugin
 * Go get the content, boy! Go get it! Get the content! Good boy!
 * http://statamic.com/add-ons/bloodhound
 */
class Plugin_bloodhound extends Plugin
{
	/**
	 * Search over data
	 * 
	 * @return string
	 */
	public function search()
	{
		// determine configuration
		$dataset     = $this->fetch('dataset', null, false, false, false);
		$parameters  = $this->gatherParameters();
		$config      = $this->tasks->loadDataset($dataset, $parameters);

		// short-circuit this if no query
		if (!trim($config['query'])) {
			return Parse::tagLoop($this->content, array(array('no_query' => true, '_query' => '')));
		}

		// do search
		$output = $this->tasks->lookUp($config['query'], $config);
        $total_found = count($output);

		// limit if we need to
		if ($config['limit'] || $config['offset']) {
			if ($config['limit'] && $config['paginate'] && !$config['offset']) {
				// pagination requested, isolate the appropriate page
				$count = count($output);
				$page  = URL::getCurrentPaginationPage();

				// return the last page of results if $page is out of range
				if (Config::getFixOutOfRangePagination()) {
                    if ($config['page_limit'] && $page > $config['page_limit']) {
                        $page = $config['page_limit'];
                    } elseif ($config['limit'] * $page > $count) {
						$page = ceil($count / $config['limit']);
					} elseif ($page < 1) {
						$page = 1;
					}
				}

				$offset = ($page - 1) * $config['limit'];
				$output = array_slice($output, $offset, $config['limit']);
			} else {
				// just limit or offset
				$output = array_slice($output, $config['offset'], $config['limit']);
			}
		}
		
		// supplement with first, last, etc.
		$output = $this->tasks->supplement($output, array('total_found' => $total_found));
		
		return Parse::tagLoop($this->content, $output);
	}


	/**
	 * Paginate search results
	 * 
	 * @return string
	 */
	public function pagination()
	{
		// determine configuration
		$dataset = $this->fetch('dataset', null, false, false, false);
		$parameters = $this->gatherParameters();
		$config  = $this->tasks->loadDataset($dataset, $parameters);

		// short-circuit this if no query
		if (!trim($config['query'])) {
			return Parse::tagLoop($this->content, array(array('no_query' => true, '_query' => '')));
		}

		// do search
		$output = $this->tasks->lookUp($config['query'], $config);

		// get limit
		$limit  = ($config['limit']) ? $config['limit'] : 10;
		
		// get the query variables
		$query_var = $config['query_variable'];
		$query     = urlencode($config['query']);

		// count the content available
		$count = count($output);
        
        // take page_limit into account
        if ($config['page_limit'] && $count > $config['page_limit'] * $limit) {
            $count = $config['page_limit'] * $limit;
        }
		
		// check for errors
		if (isset($output[0]['no_results']) || isset($output[0]['no_query'])) {
			return Parse::tagLoop($this->content, $output);
		}

		$pagination_variable  = Config::getPaginationVariable();
		$page                 = Request::get($pagination_variable, 1);

		$data = array(
			'total_items' => (int) max(0, $count),
			'items_per_page' => (int) max(1, $limit),
			'total_pages' => (int) ceil($count / $limit),
			'current_page' => (int) min(max(1, $page), max(1, $page)),
			'current_first_item' => (int) min((($page - 1) * $limit) + 1, $count)
		);
		
		$data['current_last_item'] = (int) min($data['current_first_item'] + $limit - 1, $count);
		$data['previous_page']     = ($data['current_page'] > 1) ? "?{$pagination_variable}=" . ($data['current_page'] - 1) . "&{$query_var}={$query}" : FALSE;
		$data['next_page']         = ($data['current_page'] < $data['total_pages']) ? "?{$pagination_variable}=" . ($data['current_page'] + 1) . "&{$query_var}={$query}" : FALSE;
		$data['first_page']        = ($data['current_page'] === 1) ? FALSE : "?{$pagination_variable}=1&{$query_var}={$query}";
		$data['last_page']         = ($data['current_page'] >= $data['total_pages']) ? FALSE : "?{$pagination_variable}=" . $data['total_pages'] . "&{$query_var}={$query}";
		$data['offset']            = (int) (($data['current_page'] - 1) * $limit);

		return Parse::template($this->content, $data);
	}


	/**
	 * Gathers all parameters entered on plugin tags and returns a standardized array
	 * 
	 * @return array
	 */
	private function gatherParameters()
	{
		// get folders to look through
		$folders = $this->fetchParam('folder', $this->fetchParam('folders', null));
		$folders = ($folders === "/") ? "" : $folders;

		// return parameters
		return array(
			'min_characters' => $this->fetchParam('min_characters', null, 'is_numeric', false, false),
			'min_word_characters' => $this->fetchParam('min_word_characters', null, 'is_numeric', false, false),
			'score_threshold' => $this->fetchParam('score_threshold', null, 'is_numeric', false, false),
			'query_mode' => $this->fetchParam('query_mode', null, null, false, true),
			'use_stemming' => $this->fetchParam('use_stemming', null, null, true, false),
			'use_alternates' => $this->fetchParam('use_alternates', null, null, true, false),
			'include_full_query' => $this->fetchParam('include_full_query', null, null, true, false),
			'enable_too_many_results' => $this->fetchParam('enable_too_many_results', null, null, true, false),
			'sort_by_score' => $this->fetchParam('sort_by_score', null, null, true, false),

			'log_successful_searches' => $this->fetchParam('log_successful_searches', null, null, true, false),
			'log_failed_searches' => $this->fetchParam('log_failed_searches', null, null, true, false),

			'query_cache_length' => $this->fetchParam('query_cache_length', null, 'is_numeric', false, false),

			'folders' => $folders,
			'taxonomy' => $this->fetchParam('taxonomy', null, null, true, false),
			'show_hidden' => $this->fetchParam('show_hidden', null, null, true, false),
			'show_drafts' => $this->fetchParam('show_drafts', null, null, true, false),
			'since' => $this->fetchParam('since', null, null, false, false),
			'until' => $this->fetchParam('until', null, null, false, false),
			'show_future' => $this->fetchParam('show_future', null, null, true),
			'show_past' => $this->fetchParam('show_past', null, null, true),
			'type' => $this->fetchParam('content_type', null, null, false, true),
			'conditions' => $this->fetchParam('conditions', null, false, false, false),

			'limit' => $this->fetchParam('limit', null, 'is_numeric'),
            'page_limit' => $this->fetchParam('page_limit', null, 'is_numeric'),
			'offset' => $this->fetchParam('offset', null, null, true, false),
			'paginate' => $this->fetchParam('paginate', null, null, true, false),
			'query_variable' => $this->fetchParam('query_variable', null, null, false, false),
			'include_content' => $this->fetchParam('include_content', null, null, true, false),
            
            'include_404' => $this->fetchParam('include_404', null, null, true, false),
            'exclude' => $this->fetchParam('exclude', null, null, false, false),

			'query' => $this->fetchParam('query', null, null, false, false)
		);
	}
}