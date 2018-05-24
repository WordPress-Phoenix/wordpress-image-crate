#### 3.0.6
* Hot fix: check legacy tracking meta is an array

[New Relic Error](https://rpm.newrelic.com/accounts/1358368/applications/19652122/filterable_errors#/show/4a46219e-5edc-11e8-ae1e-0242ac11000b_2329_4877/stack_trace?top_facet=transactionUiName&primary_facet=error.class&barchart=barchart)

#### 3.0.5
Escape spaces in Getty images fetch URL
* This was breaking the image search request with a multi word search term

#### 3.0.4
* Add Getty access type option for Premium access

#### 3.0.3
* Only make Getty API request if search term is set. The request fails if search term is empty.

#### 3.0.2
* Add additional image URL filters from VIP to fix broken image URLs in the media modal

#### 3.0.1
* Add legacy image URL filters from VIP

#### 3.0.0
Rewrite plugin and add Getty Images as a provider
* Set up scaffolding for image providers
* Add backbone controllers to media modal
* Add content loading for state changes
* Connect controller state changes to backend
* Update plugin base
* Remove need for duplicate provider loader
* Add documentation for javascript files
* Add a Getty image service
* Enhance UI and UX
* Track image usage
* Update readme
* Add CircleCI config and deploy

#### 2.0.0
* Migrates plugin to new abstract plugin base

#### 1.1.1
* Fixes downloading issues with previously downloaded images

#### 1.1.0
* Add USA Today SIPA (entertainment) images
* Add ability to filter results by vertical

#### 1.0.0
* Alter search mode type for better query results

#### 0.1.6
* Fixed an error where switch to blog wasn't changing table for lookup, which resulted in duplicate images being downloaded
* Captions will are set by automagically if not empty when clicking on a thumbnail in the media modal.

#### 0.1.5
* Hide url field from search results attachment view 
* HTML encode descriptions and captions

#### 0.1.4
* Pagination is fixed. Search results will now return more recent images 
* Excluded api image calls from the WordPress-Phoenix/wordpress-rest-cache plugin

#### 0.1.3
* Updated option function name

#### 0.1.2
* Swapped search mode
* Fixing fixed issue where download button prevented image from downloaded 

#### 0.1.1
* Plugin Release

#### 0.1
* Initial plugin as boilerplate from https://github.com/scarstens/worpress-plugin-boilerplate-redux