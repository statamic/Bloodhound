# Change Log

## v1.1
### February 25, 2014

- The `include_404` setting (`false` by default) lets you choose whether your site's 404 page will be included in your search results if your folders would normally collect and search through it
- The `exclude` setting lets you remove folders of content from the set of content being searched through, like a reverse-`folders` setting
- The `page_limit` setting lets you set a maximum number of pages that pagination should allow
- The `bloodhound:search` tag now has a `total_found` tag for successful searches; where `total_results` displays the number of results that will be displayed on the page, `total_found` is the total number of all results found that fit the search parameters
- The included sample templates and partials now include a set for the Acadia theme
- Boolean settings were sometimes not being correctly respected
- Squashed a bug where whole-query searching would sometimes result in an error
- Fixed issue where word-query searching wasn't properly parsing search query

## v1.0
### November 27, 2013

- Bloodhound is released.