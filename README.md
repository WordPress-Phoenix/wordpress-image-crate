# WordPress Image Crate
Connects to external image APIs to display and download images via the core WordPress media modal.

Version 3 of the plugin has been completely rewritten to replicate core behavior within the modal.

## Adding a Provider
Two things need to happen to add a provider. First, a JS controller needs to be added to `assets/js/controllers` and a
 new state has to be added to `image-crate.manifest.js` in the `createStates()` function.

Next, a provider needs to extend the abstract provider class and supply body for the required methods. 

## Notes
### Tracking
Tracking can be enabled via class constant on a per provider basis. This will track image downloads and usage in post 
content. This is stored on the master site as a attachment post with tracking in post_meta. The meta_key for tracking in 
{provider}_usage.

### WordPress hooks
* `save_post` - The Usage_Tracking class is hooked in here to track if an image is used in post content and update 
it's attachment meta on the master site. 