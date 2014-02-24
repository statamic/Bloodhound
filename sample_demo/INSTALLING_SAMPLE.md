# Installing the Demo

We've included the `sample_demo` folder with the files you'll need to implement search into the sample content and theme that comes with Statamic.
Here's how to install these files:

In the DENALI theme:

1. Drag the `_content/_search` folder into your site's `_content` folder
2. Drag the `_themes/denali/partials/search.html` file into your site's `_themes/denali/partials` folder
3. Drag the `_themes/denali/templates/search.html` file into your site's `_themes/denali/templates` folder
4. Open `_themes/denali/layouts/default.html` and go to the end of the line containing `{{ theme:partial src="nav" }}`; hit enter (or return) and add in the search partial with this code: `{{ theme:partial src="search" }}` then save this file

In the ACADIA theme:

1. Drag the `_content/_search` folder into your site's `_content` folder
2. Drag the `_themes/acadia/partials/search.html` file into your site's `_themes/acadia/partials` folder
3. Drag the `_themes/acadia/templates/search.html` file into your site's `_themes/acadia/templates` folder
4. Open `_themes/acadia/partials/common-sidebar.html` and go to the end of the line containing `{{ theme:partial src="calendar" }}`; hit enter (or return) and add in the search partial with this code: `{{ theme:partial src="search" }}` then save this file

You should be all set. Refresh the sample site in your browser.

## How Will I Know It Worked?

With the DENALI theme, you should see a search form in the left-column of the site, just under the navigation.
With the ACADIA theme, you should see a search form in the right-column of the site, just under the upcoming events list. 
Type something into that box (try "snare" or "wilderness") and hit *Go*.
This should take you to a `/search` page, with the results of your search displayed on the screen.