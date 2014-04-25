**3.1.4**
* **Fix** The default menu in other language has gone
* **Fix** Menu stuck on default language
* **Fix** Infinite loop in auto-adjust-ids
* **Fix** Translations lose association
* **Fix** The "This is a translation of" drop down wasn't filled for non-original content
* **Fix** Removed language breakdown for Spam comments
* **Fix** Pages with custom queries won't show 404 errors
* **Fix** Languages in directories produces 404 for all pages when HTTP is using non standard 80 port
* **Fix** Fixed icl_migrate_2_0_0() logic
* **Fix** When a database upgrade is required, it won't fail with invalid nonce
* **Fix** Error in all posts page, where there are no posts
* **Fix** php notice when adding a custom taxonomy to a custom post
* **Fix** The default uncategorized category doesn't appear in the default language
* **Fix** Fixed locale for Vietnamese (from "vn" to "vi")
* **Fix** Fixed languages.csv file for Portuguese (Portugal) and Portuguese (Brazilian) mixed up --this requires a manual fix in languages editor for existing sites using one or both languages--
* **Fix** Pre-existing untranslatable custom post types disappears once set as translatable
* **Fix** Languages settings -> Language per domain: once selected and page is reloaded, is the option is not properly rendered
* **Fix** Languages settings -> Language per domain: custom languages generate a notice
* **Fix** Updated translations
* **Fix** Custom Fields set to be copied won't get lost anymore
* **Fix** Scheduled posts won't lose their translation relationship
* **Fix** Excluded/included posts in paginated custom queries won't cause any notice message
* **Fix** Replace hardcoded references of 'sitepress-multilingual-cms' with ICL_PLUGIN_FOLDER
* **Fix** Replace hardcoded references of 'wpml-string-translation' with WPML_ST_FOLDER
* **Fix** Replace hardcoded references of 'wpml-translation-management' with WPML_TM_FOLDER
* **Improvement** Generated keys of cached data should use the smallest possible amount of memory
* **Improvement** The feature that allows to set orphan posts as source of other posts has been improved in order to also allow to set the orphan post as translation of an existing one
* **Improvement** Added support to users with corrupted settings
* **Improvement** Improved language detection from urls when using different domains
* **Improvement** Added admin notices for custom post types set as translatable and with translatable slugs when translated slugs are missing

**3.1.3**
* **Fix** In SitePress_Setup::languages_table_is_complete -> comparison between number of existing languages and number of built in languages changed from != to <
* **Fix** In SitePress_Setup::fill_languages -> added "$lang_locales = icl_get_languages_locales();" needed for repopulating language tables
* **Fix** Added cache clearing to icl_fix_languages logic on the troubleshooting page
* **Fix** Wording changes for the fix languages section on the troubleshooting page
* **Fix** Logic changes for the fix languages section on the troubleshooting page -> added checkbox and the button is enabled only when the checkbox is on
* **Fix** Added WPML capabilities to all roles with cap 'manage_options' when activate
* **Fix** Not remove WPML caps from super admin when deactivate

**3.1.2**
* **Fix** Fixed a potential issue when element source language is set to an empty string rather than null: when reading element translations, either NULL or '' will be handled as NULL.

**3.1.1**
* **Fix** Fixed an issue that occurs with some configurations, when reading WPML settings

**3.1**
* **Performances** Reduced number of queries to one per request when retrieving Admin language
* **Performances** Reduced the number of calls to *$sitepress->get_current_language()*, *$this->get_active_languages()* and *$this->get_default_language()*, to avoid running the same queries more times than needed
* **Performances** Dramatically reduced the amount of queries ran when checking if content is properly translated in several back-end pages
* **Performances** A lot of data is now cached, further reducing queries
* **Improvement** Improved javascripts code style
* **Improvement** Orphan content is now checked when (re)activating the plugin, rather than in each request on back-end side
* **Improvement** If languages tables are incomplete, it will be possible to restore them
* **Feature** When setting a value for "This is a translation of", and the current content already has translations in other languages, each translation gets properly synchronized, as long as there are no conflicts. In case of conflicts, translation won't be synchronized, while the current content will be considered as not linked to an original (in line with the old behavior)
* **Feature** Categories, tags and taxonomies templates files don't need to be translated anymore (though you can still create a translated file). Taxonomy templates will follow this hierarchy: '{taxonomy}-{lang}-{term_slug}-{lang}.php', '{taxonomy}-{term_slug}-{lang}.php', '{taxonomy}-{lang}-{term_slug}-2.php', '{taxonomy}-{term_slug}-2.php', '{taxonomy}-{lang}.php', '{taxonomy}.php'
* **Feature** Administrators can now edit content that have been already sent to translators
* **Feature** Ability to set, in the post edit page, an orphan post as source of translated post
* **Feature** Added WPML capabilities (see online documentation)
* **Feature** Add support to users with corrupted settings
* **Security** Improved security by using *$wpdb->prepare()* wherever is possible
* **Security** Database dump in troubleshooting page is now available to *admin* and *super admin* users only
* **Fix** Admin Strings configured with wpml-config.xml files are properly shown and registered in String Translation
* **Fix** Removed max length issue in translation editor: is now possible to send content of any length
* **Fix** Taxonomy Translation doesn't hang anymore on custom hierarchical taxonomies
* **Fix** Is now possible to translate content when displaying "All languages", without facing PHP errors
* **Fix** Fixed issues on moderated and spam comments that exceed 999 items
* **Fix** Changed "Parsi" to "Farsi" (as it's more commonly used) and fixed some language translations in Portuguese
* **Fix** Deleting attachment from post that are duplicated now deleted the duplicated image as well (if "When deleting a post, delete translations as well" is flagged)
* **Fix** Translated static front-page with pagination won't loose the template anymore when clicking on pages
* **Fix** Reactivating WPML after having added content, will properly set the default language to the orphan content
* **Fix** SSL support is now properly handled in WPML->Languages and when setting a domain per language
* **Fix** Empty categories archives does not redirect to the home page anymore
* **Fix** Menu and Footer language switcher now follow all settings in WPML->Languages
* **Fix** Post metas are now properly synchronized among duplicated content
* **Fix** Fixed a compatibility issue with SlideDeck2 that wasn't retrieving images
* **Fix** Compatibility with WP-Types repeated fields not being properly copied among translations
* **Fix** Compatibility issue with bbPress
* **Fix** Removed warnings and unneeded HTML elements when String Translation is not installed/active
* **Fix** Duplicated content retains the proper status
* **Fix** Browser redirect for 2 letters language codes now works as expected
* **Fix** Menu synchronization now properly fetches translated items
* **Fix** Menu synchronization copy custom items if String Translation is not active, or WPML default languages is different than String Translation language
* **Fix** When deleting the original post, the source language of translated content is set to null or to the first available language
* **Fix** Updated localized strings
* **Fix** Posts losing they relationship with their translations
* **Fix** Checks if string is already registered before register string for translation. Fixed because it wasn't possible to translate plural and singular taxonomy names in Woocommerce Multilingual
* **Fix** Fixed error when with hierarchical taxonomies and taxonomies with same names of terms.