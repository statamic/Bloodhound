# Bloodhound

Bloodhound is a highly-customizable search add-on for Statamic. It's been configured so that it should be great out-of-the-box, but if you want to get your hands dirty you can be very particular about what it finds.


## Requirements

Bloodhound requires Statamic v1.6.5 or higher.  
Previous versions of Statamic are not supported.


## Installing

Installing Bloodhound is easy.

1. Unzip the `bloodhound` add-on zip file
2. In this unzipped folder, drag the following folders into your site's Statamic installation:
  - `_add-ons/bloodhound` goes in your installation's `_add-ons` folder
  - `_config/add-ons/bloodhound ` goes in your installation's `_config/add-ons` folder

Bloodhound is now installed and is ready to use.


## Configuring

Bloodhound is *highly* customizable, but comes with many sensible default settings so that you shouldn't need to do too much to get it running on your site. There are four places that Bloodhound configuration values can be set, and those are arranged in a hierarchy so that values can override one another in a sensible way.

The four places that configuration values can be set:

1. Default values within the add-on itself
2. The master `bloodhound.yaml` file found in `_config/add-ons/bloodhound`
3. Dataset files found in `_config/add-ons/bloodhound/datasets`
4. Parameters on Bloodhound tags themselves

This list is also the hierarchy order in which values get overridden, from start to finish. Default values are read, then any values set in the `bloodhound.yaml` file override those, then any values set in the corresponding dataset override those, and finally any values set as parameters on the tags themselves override those.

Being able to configure variables in so many places can make things confusing quick, so we recommend one of the two following methods based on how you're going to use Bloodhound:

1. If you're only going to be searching one set of data (which is true for most set ups), do all of your configuring in the `bloodhound.yaml` file found in `_config/add-ons/bloodhound`. This way Bloodhound acts as most other add-ons with configuration files act.
2. If you're going to be searching multiple sets of data, configure any "global" settings in the `bloodhound.yaml` file found in `_config/add-ons/bloodhound`, and then configure specific per-dataset settings in a dataset file within `_config/add-ons/bloodhound/datasets`.

**Note:** most configuration values can be set as parameters, but any variable that wants a list cannot. To set configuration values that want lists, you *must* use either the `bloodhound.yaml` file or a dataset.


### Using Datasets

*Datasets* let you configure more than one set of data to search over per-site. This probably won't happen on most sites, but if it comes up, Bloodhound can handle it. As an example of how datasets work, let's create a `my-search` dataset.

1. Create a `my-search.yaml` file within `_config/add-ons/bloodhound/datasets`
2. Set any configuration settings in here as you would in the master `bloodhound.yaml` file found in `_config/add-ons/bloodhound`
3. In any of your Bloodhound tags, add the `dataset` parameter and set the value to `my-search`

Setting the `dataset` parameter on a Bloodhound tag looks like this:

```html
{{ bloodhound:results dataset="my-search" }}
   <!-- search results here -->
{{ /bloodhound:results }}

<!-- optionally, add pagination -->
{{ bloodhound:pagination dataset="my-search" }}
   <!-- pagination code here -->
{{ /bloodhound:pagination }}
```

This results in much cleaner template code than if you had set configuration variables as parameters on the tag itself.

### Available Settings

The list of available settings below are broken up the same way they are in the master `bloodhound.yaml` file for convenience purposes. You can define them in any order that you'd like.

#### Search Settings

<dl>
  <dt><code>match_weights</code></dt>
  <dd>
    Strength-multipliers for each of the nine ways that Bloodhound can match things. The nine ways are:
    <ul>
      <li><code>partial_word</code>: when the query (either an individual word of the query or the entire query depending upon <code>query_mode</code>) matches any part of a variable's value; default: <code>1</code></li>
      <li><code>partial_first_word</code>: when the query (either an individual word of the query or the entire query depending upon <code>query_mode</code>) matches somewhere in the first word a variable's value; default: <code>2</code></li>
      <li><code>partial_word_start</code>: when the query (either an individual word of the query or the entire query depending upon <code>query_mode</code>) matches any word of a variable's value from the word's start; default: <code>1</code></li>
      <li><code>partial_first_word_start</code>: when the query (either an individual word of the query or the entire query depending upon <code>query_mode</code>) matches the start of a variable's value from the first word's start; default: <code>2</code></li>
      <li><code>whole_word</code>: when the query (either an individual word of the query or the entire query depending upon <code>query_mode</code>) matches a full word in a variable's value; default: <code>5</code></li>
      <li><code>whole_first_word</code>: when the query (either an individual word of the query or the entire query depending upon <code>query_mode</code>) matches the first full word in a variable's value; default: <code>5</code></li>
      <li><code>partial_whole</code>: when the query (either an individual word of the query or the entire query depending upon <code>query_mode</code>) matches text found in a variable's value, this becomes more important qhen <code>query_mode</code> is set to `whole`; default: <code>2</code></li>
      <li><code>partial_whole_start</code>: when the query (either an individual word of the query or the entire query depending upon <code>query_mode</code>) matches text starting at the beginning of a variable's value, this becomes more important qhen <code>query_mode</code> is set to `whole`; default: <code>2</code></li>
      <li><code>whole</code>: when the query (either an individual word of the query or the entire query depending upon <code>query_mode</code>) matches a variable's value fully from start to finish, this is more uncommon and as such has a higher default strength multiplier; default: <code>10</code></li>
    </ul>
  </dd>
  
  <dt><code>min_characters</code></dt>
  <dd>The minimum number of characters a query must be to trigger a search. If a query is entered that doesn't have enough characters in it, the <code>no_results</code>, <code>no_query</code>, and <code>not_enough_characters</code> variables will be set to <code>true</code> in the returned results. Default: <code>3</code></dd>
  
  <dt><code>min_word_characters</code></dt>
  <dd>The minimum number of characters a work must contain to be included in a search. This only applies when <code>query_mode</code> is set to either <code>boolean</code> or <code>words</code>. If a word is entered that doesn't have enough characters in it, it will be ignored for the search. Default: <code>2</code></dd>
  
  <dt><code>score_threshold</code></dt>
  <dd>The minimum score something must receive to be included in the search results. This score is a measurement of matches bloodhoundined with all strength multipliers — it isn't on a set scale with a maximum. Finding the right threshold is usually a matter of doing searches with multipliers in place and seeing what scores are delivering bad results. Default: <code>1</code></dd>
  
  <dt><code>property_weights</code></dt>
  <dd>Strength-multipliers for each variable in which a match is found. This should be a list of variable names and the multiplier to apply to matches found in that variable's value. (And variable not included in this list will have a multiplier of <code>1</code>.) Default: <code>null</code></dd>
  
  <dt><code>query_mode</code></dt>
  <dd>
    The type of query to run, of which there are currently three options:
    <ul>
      <li><code>boolean</code>: searches against each word, allows users to set *required* words by prepending words with a <code>+</code> as well as setting *disallowed* words by prepending words with a <code>-</code>; this is the default value</li>
      <li><code>words</code>: searches against each word</li>
      <li><code>whole</code>: searches against the entire phrase queried, this mode is the most strict and will result in the fewest matches</li>
  </dd>
  
  <dt><code>use_stemming</code></dt>
  <dd>When <code>true</code>, will attempt to search for singularized versions of pluralized words (English-only); default: <code>false</code></dd>
  
  <dt><code>use_alternates</code></dt>
  <dd>When <code>true</code>, will attempt to search for alternate versions of words (*and* and *&*) as well as punctuation (*'*, *’*, and *‘*); default: <code>false</code></dd>
  
  <dt><code>include_full_query</code></dt>
  <dd>When <code>true</code>, will also test the query as a whole against the searched data, only applies when more than one word is used and is helpful for finding relevant exact matches; default: <code>true</code></dd>
  
  <dt><code>sort_by_score</code></dt>
  <dd>When <code>true</code>, will return results sorted by score, highest-to-lower, otherwise returns results in the order they appeared in the data set; default: <code>true</code></dd>
  
  <dt><code>exclude_properties</code></dt>
  <dd>A list of variables to not search through, should not be used at the same time as <code>include_properties</code>; default: <code>null</code></dd>
  
  <dt><code>include_properties</code></dt>
  <dd>A list of variables to search through while ignoring all others, should not be used at the same time as <code>exclude_properties</code>; default: <code>null</code></dd>
  
  <dt><code>stop_words</code></dt>
  <dd>A list of words to ignore in queries, generally consists of common words that don't really help to filter down results because they match data too easily; default: list of <code>the</code>, <code>an</code>, and <code>a</code></dd>
</dl>

#### Data Settings

<dl>
  <dt><code>folders</code></dt>
  <dd>One or more folders to look through for search results. This works the same way as `{{ entries:listing }}` works, letting you list multiple folders by pipe-delimiting them, and using the <code>*</code> character to set wildcards. Default: the current page (not the current folder)</dd>

  <dt><code>taxonomy</code></dt>
  <dd>Should the set of data to search through be based upon a taxonomy value parsed from the current URL? This makes the most sense if people can search through a taxonomy list within a given folder. Default: <code>false</code></dd>

  <dt><code>show_hidden</code></dt>
  <dd>Should Bloodhound look through and return files that are hidden in the result list? Default: <code>false</code></dd>

  <dt><code>show_drafts</code></dt>
  <dd>Should Bloodhound look through and return files that are drafts in the result list? Default: <code>false</code></dd>

  <dt><code>since</code></dt>
  <dd>Only search through date-based-entries that are from after this time. Default: <code>null</code></dd>

  <dt><code>until</code></dt>
  <dd>Only search through date-based-entries that are from before this time. Default: <code>null</code></dd>

  <dt><code>show_future</code></dt>
  <dd>Should this search include date-based-entries that are in the future? If you use <code>until</code>, you don't need to also use this field. Default: <code>false</code></dd>

  <dt><code>show_past</code></dt>
  <dd>Should this search include date-based-entries that are in the past? If you use <code>since</code>, you don't need to also use this field. Default: <code>true</code></dd>

  <dt><code>type</code></dt>
  <dd>The type of content files this search should look through. Options are <code>entries</code>, <code>pages</code>, or <code>all</code>. Default: <code>all</code></dd>

  <dt><code>conditions</code></dt>
  <dd>Add additional limitations to the content that should be searched through. Works exactly as it does for <code>{{ entries:listing }}</code>. Default: <code>null</code></dd>

  <dt><code>include_content</code></dt>
  <dd>Should each content file's <code>{{ content }}</code> field be included in the fields searched through? This setting being <code>true</code> results in slower query times than when its set to <code>false</code>. Default: <code>true</code></dd>
</dl>


#### Logging Settings

<dl>
  <dt><code>log_successful_searches</code></dt>
  <dd>When set to <code>true</code>, any search made that returns at least one result will be logged on the system log as an <em>info</em>-level message. Default: <code>true</code></dd>

  <dt><code>log_failed_searches</code></dt>
  <dd>When set to <code>true</code>, any search made that returns no results will be logged on the system log as a <em>warn</em>-level message. Default: <code>true</code></dd>
</dl>


#### Input Settings

<dl>
  <dt><code>query_variable</code></dt>
  <dd>The name of the <code>GET</code> variable to look for that contains the query to search with. This is the what your input field's <code>name</code> attribute is set to. Default: <code>query</code></dd>
</dl>

#### Output Settings

<dl>
  <dt><code>limit_results</code></dt>
  <dd>Limits the maximum number of results returned, or when used along with <code>paginate</code>, sets the number of results to return per page. Default: <code>10</code></dd>

  <dt><code>offset</code></dt>
  <dd>The number of search results found to not show before showing the first result. Don't set this if you want to use <code>paginate</code>. Default: <code>0</code></dd>

  <dt><code>paginate</code></dt>
  <dd>Should the results be paginated? When <code>true</code>, this tag will automatically grab the appropriate page to load from the URL. Default: <code>true</code></dd>
</dl>

#### Result-Caching Settings

<dl>
  <dt><code>query_cache_length</code></dt>
  <dd>The number of minutes to cache search results for per query. Cached search results are much, much faster than having to load them from scratch, but they will only find search results that were available at the time the original search was performed. This makes this setting a balancing act. This setting can also be set to <code>on cache update</code>, which ties cached results to the last time the content cache was updated. Only use this if you upload-to-publish, as any content uploaded with a timestamp in the future won't trigger a re-cache when time passes and it becomes "live." Default: <code>30</code></dd>
</dl>

## Usage

Bloodhound comes with two tags for displaying results: `{{ bloodhound:results }}` and `{{ bloodhound:pagination }}`. These work similarly to the `{{ entries:listing }}` and `{{ entries:pagination }}` tags (respectively) in most ways. That's the most important thing to remember. What follows is *how* Bloodhound works behind the scenes, but know that to output search results, you'll be doing almost the exact same thing as `{{ entries:listing }}`. 

OK, let's cover how Bloodhound works.

### How It Works

When a Bloodhound tag is on a page being rendered, it checks through the `GET` variables for a variable named whatever `query_variable` has been configured to be. If that variable is found, Bloodhound is ready to search. First, it will look into its internal cache to see if this exact query has been made before, and if that query was made within the last so many minutes (determined by the `query_cache_length` setting). If a cache is found, Bloodhound grabs those search results. If one isn't found, it performs a search over the data.

Bloodhound grabs the relevant content files as filtered down by the configuration settings (based on `folder`, `conditions`, etc.). It then parses the query to figure out what to search for. Once it has those values, Bloodhound looks through your content trying to match the query in different ways. As it finds matches, it makes notes of the types of matches matching and the configured strength multipliers that are contextually relevant. It assigns a relevancy score to each piece of content.

Next, if the configuration allows, Bloodhound sorts the results based on the relevancy score, trims off the ones that don't meet the `score_threshold`, and chops the list down to the configured `limit_results` value if one was set. This whole process can be intense, so Bloodhound caches the search results for reuse later on. While an initial search may take a second or two to perform, once the search result has been cached results show up almost instantaneously.

Finally, Bloodhound loops over the results found, displaying results or pagination information as needed.

### Where to Put Your Tags

The `{{ bloodhound:search }}` and `{{ bloodhound:pagination }}` tags should be put on a page at which you can point your search forms. Be sure to use the `get` method in your form. When your form submits to this page, the tags will check for the appropriate `query_variable` and will perform the search as needed.

#### <code>{{ bloodhound:search }}</code>
Using `{{ bloodhound:search }}` is just like `{{ entries:listing }}`. Each result will use the template defined between the tags to create a loop of results. You can use `{{ first }}`, `{{ last }}`, and `{{ count }}` just like in `{{ entries:listing }}`, as well as any tags tag are available in the result being looped over. If there's a `jerky` variable in the found content's front-matter, the `{{ jerky }}` tag will be available when its result is being printed. You'll probably only want to use variables that you're sure will appear on all results (`{{ title }}` and `{{ url }}`, for example), but you can always check to see if variables exist and display them if they do.

Also, you don't have to display all results the same way. Did a search find a staff member's profile and a blog post? You can use `{{ _folder }}` or other variables to determine where the results are coming from and change the way each result looks accordingly. Maybe you pull in a headshot for the profile result, or a thumbnail from the blog post if one is available.

One thing to note, in addition to the content's variables being available, a couple of special result-related variables are also available for use:

<dl>
  <dt><code>{{ _score }}</code></dt>
  <dd>The relevancy score this result earned, helpful for debugging but useless to the general public.</dd>
  
  <dt><code>{{ _query }}</code></dt>
  <dd>The raw query (safe for HTML printing) that was searched for.</dd>
  
  <dt><code>{{ _parsed_query }}</code></dt>
  <dd>Three lists of words that were parsed from the query: <code>{{ _parsed_query:chunks }} is a list of words searched for, <code>{{ _parsed_query:required }}</code> are words required by boolean searching, and <code>{{ _parsed_query:disallowed }}</code> are words disallowed by boolean searching.</dd>
  
  <dt><code>{{ _query_time }}</code></dt>
  <dd>The amount of time it took (in seconds) to search through the data. Note that gathering the data to search over usually takes longer than the query itself, and that data-gathering time is *not* included in this calculation.</dd>
</dl>


#### <code>{{ bloodhound:pagination }}</code>

Using `{{ bloodhound:pagination }}` works exactly like `{{ entries:pagination }}` does. It uses all of the same variables except that the error tags will be included here as well. Because this tag usually appears on the same page as the corresponding `{{ bloodhound:search }}` tag, it will use the cached search results to make its calculations. Also, going from page-to-page is lightning quick as each page will also use the cached search results (as long as going from page-to-page is done in less time than the configured `query_cache_length` variable allows).


## Tips & Tricks

As mentioned, Bloodhound's default configuration values are intended to help you quickly get started, but it will really shine when you've fine-tuned it to work best for your site.

### Use Result Caching As Much As Possible

Regardless of your set-up, cached results are *always* going to be must faster having to perform the search live. For best results, you should push to have your `query_cache_length` time set as high as possible while still being allowed to re-cache when new content is posted.

As mentioned in that setting's description, if you "upload to publish," you can use `on cache update` to invalidate all search caches each time your content cache updates. This won't work well if you schedule content to go live (as the content cache doesn't update when time passes and content becomes live, only when you make actual changes to the files themselves).

If you don't "upload to publish," try setting your `query_cache_length` to be half the time between your average postings. If you post every day, set it to half a day. If you post hourly, keep it at 30 minutes.


### Use a Summary Field Instead of Truncating Content

Although truncating `{{ content }}` is the *easiest* way to show a description in a list view, it's bad content strategy. Force content authors to take the time to summarize their content into a *summary* (or *description*, or whatever) field that can be pulled in for list views. This is good Statamic strategy in general, as it will result in faster rendering all around, not just for search results, but in listings everywhere.


### *Score* is a Relative Term

Each result is given a score that's calculated based on match-type weights, property-weights, and the number of times a query matches the variables searched over. It's important to remember that score isn't some number on a scale of 1-100, and that there's no way to determine was a "good score" is until you try it out. Remember that the more a result matches a query, the higher the score.

When setting up and tuning your Bloodhound search, display `{{ _score }}` in your result lists. Try a bunch of different searches and see what types of numbers are returning relevant results from what you know of the content. If you find that Bloodhound is finding a lot of useless results with low scores (but is still showing them), set the `score_threshold` to be a little higher than those results' scores.

We don't want *more* search results, we want *better* search results.


### Avoiding `include_content` For a Faster, Better Search

Most people's first instinct will be to keep `include_content` set to `true`. In fact, it's `true` by default because that will probably return more results, which makes it seems like it's working better for people first installing it. The truth is that this will greatly slow down your search, especially if you have many, many items to search over. (Remember, it's still flat file, and each piece of content is *a lot* to look through.)

Believe it or not, there's a way to make a better, faster search without including your `{{ content }}` data. It takes time, cultivating, and a bit of effort, but in the end it's worth it.

#### Configuring Bloodhound

For this set up, first set `include_content` to `false`. This will speed up the process of loading the data to look over (which is the slowest part of the process), as well as speed up the search itself.

The next step is the most time-consuming. Add keyword variables to your searchable content. These variables will never display publicly, but will be used by Bloodhound to determine relevancy based upon the keywords that you set. For this example, let's set up four new keyword fields in each of your content files:

```yaml
title: Your Page Title
summary: A description of this content file.
# other variables as you want
positive_keywords:
negative_keywords:
super_positive_keywords:
super_negative_keywords:
---
Your content
```

These variables don't need to be included as taxonomies or anything, they only exist to help Bloodhound find what it needs. The content of these new variables can be either a space-separated-string or a YAML-list of words (helpful if you're using the Control Panel and want to do something like a *Suggest* field).

Next, limit the variables that Bloodhound searches through to determine relevancy. This is done with the `include_properties` setting. You might limit things like so:

```yaml
include_properties:
  - title
  - summary
  - positive_keywords
  - negative_keywords
  - super_positive_keywords
  - super_negative_keywords
```

Feel free to include more fields that are relevant to your content files. This example list just continues our example from above. Once set, these are the only fields that will be searched over.

Now it's time for the hard work. Fill in each of the four keyword fields with content that's relevant to that page's content. Positive keywords are words you think people might search for to find this, whereas super-positive keywords are words that should almost definitely bring up this result when searched for. The same goes with the negative versions, but in the opposite direction: negative keywords will lower this page's score when a word is matched, and super-negative keywords will push this result for a word way down the list.

Next, we need to set how matches in each of these variables will affect the overall *score* of a search result. We want `positive_keywords` matches to bump up a result a little bit, `negative_keywords` matches to nudge down a result a little bit, `super_positive_keywords` matches to really bump up a result, and `super_negative_keywords` matches to really move down a result. We set `property_weights` like so:

```yaml
property_weights:
  positive_keywords: 3
  negavite_keywords: -3
  super_positive_keywords: 8
  super_negative_keywords: -8
```

Property weights will multiply and add each find by the number you set. This means that any query matches in `positive_keywords` will add the number of finds times the match-type weights times 3. Similarly, `super_negative_keywords` will add the number of finds times the match-type weights time -8 — and when you add a negative number to a positive number, the total goes down.

If you've done a good job filling out all of the fields correctly, you should find that your search results are pretty darn good. However, you won't think of everything, that's where fine-tuning Bloodhound over time comes in.

#### Fine Tuning

Be sure to set both `log_successful_searches` and `log_failed_searches` to `true`. This will let you keep an eye on what people are searching for. Once you launch Bloodhound on your site, check back into your logs twice a day looking for the searches happening. Did something fail that shouldn't have? Add that term to the list of keywords for the appropriate content pieces. Did something succeed but return way a lot of results? Run that search yourself to make sure the results are coming up in the order you expected. If they aren't, add positive and negative keywords to your content as needed.

After the first week or two, you should be in the swing of things. Your search results have probably homed-in a bit, and you can start checking your logs less. Continue this overtime and you'll have a great, fast, and super-relevant search on your hands.


### Never Print `{{ get:query }}` to the Screen

You'll probably want to tell people what they've searched for, but make sure you don't print `{{ get:query }}` right to the screen. This is a security hole, and should be avoided. Use `{{ _query }}` in your `{{ bloodhound:results }}` tag to display the query sent. This will always be available, even when one or more of the error flags are set.


### Use Stop Words

Stop words are a list of words that will automatically be excluded from a query if they're typed in. You can set stop words with the `stop_words` setting, it's expecting a YAML-list of words to ignore. 

You should use them because words like *the* and *an* probably don't contribute to the actual relevancy a search will bring back. Setting those as stop words will strip those out for you.

If you take a look around the Internet, there are a bunch of big lists of common stop words that can be used. We looked through them, but didn't like any as a default list to include because some of them just had too many words.