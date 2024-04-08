# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

### Fixed

- Tab links for settings in Multi-Site mode

## [1.2.1] - 2024-02-08

### Fixed

- ACF\LocalJson - fixed warning when reading non-ACF local JSON files

## [1.2.0] - 2024-02-08

### Added

- Added Acf\LocalJsonCustomFilename - syncs ACF JSON from a custom path and saves to a specific filename
- Added Acf\LocalJsonCustomFilenameByLocationParam - syncs ACF local JSON for groups with a specific param value in the location rules and saves to specific filename
- Added Acf\LocalJsonCustomFilenameCustomTemplate - syncs ACF local JSON for custom page templates and saves to custom filename

### Changed

- Acf\LocalJson - now syncs deletion regardless of filename (previously only synced deletion if file was named `group_*.json`)

## [1.1.0] - 2024-01-03

### Added

- Added Acf\LocalJsonCategorized - syncs ACF JSON from a custom path, using natural filenames and different sub-folders based on ACF type

## [1.0.0] - 2023-08-25

### Changed

- BREAKING: updated AcfSettingsOptionsSubPage, AcfToolsOptionsSubPage, AcfAirfleetOptionsSubPage, AcfSettingsLink to use plugin slug instead of ACF generated slug

## [0.6.0] - 2023-08-24

### Added

- Added Plugin\AcfSettingsOptionsSubPage - add ACF options sub-page under core "Settings" menu based on plugin config
- Added Plugin\AcfToolsOptionsSubPage - add ACF options sub-page under core "Tools" menu based on plugin config

### Changed

- Updated MenuTabsPage to support "tools" menu type

## [0.5.0] - 2023-07-14

### Added

- Added Helpers\Display::attributes and Helpers\Display::data_attributes - render attributes for HTML elements
- Added Acf\LocalJsonByLocationParam - sync local JSON for groups that match a specific location rule
- Added Acf\LocalJsonCustomTemplate - sync local JSON for a specific custom page template

## [0.4.0] - 2023-06-28

### Added

- Added Strings helper (Helpers\Strings) - convert between different cases

## [0.3.0] - 2023-04-13

#### Added

- Added ACF options feature classes (Acf\OptionsPage, Acf\OptionsSubPage, Acf\AirfleetOptionsSubPage)
- Added Plugin\AcfAirfleetOptionsSubPage - add ACF options sub-page under Airfleet menu based on plugin config
- Added Plugin\AcfSettingsLink - add settings link on Plugins admin table for ACF options sub-page based on plugin config

## [0.2.1] - 2023-04-12

### Changed

- Added login styles/scripts support to Assets\Enqueue

## [0.2.0] - 2023-04-12

### Added

- Added enqueue utilities (Assets\Enqueue)
- Added inline script and variables utilities (Assets\InlineScript, Assets\ScriptVariables, Assets\FrontendVariables, Assets\EditorVariables, Assets\AdminVariables, Assets\CriticalVariables)
- Added utility to add plugin action links on the Plugins table page (Plugin\ActionLinks)
- Added utility to add plugin settings link on the Plugins table page (Plugin\SettingsLink)
- Added Airfleet helper (Helpers\Airfleet) - detect Airfleet Starter Theme, Airfleet users, and post types/taxonomies
- Added Screen helper (Helpers\Screen) - detect current screen and post type
- Added Assets helper (Helpers\Assets) - get asset URL and get manifest
- Added Display helper (Helpers\Display) - pretty print, dump, and render reusable blocks
- Added plugin feature utilities (Features\PluginFeature, Features\BasePluginFeature and Features\PluginFeatures) - extra plugin specific hooks compared to Feature (activation, deactivation, uninstall)
- Added Hooks helper (Helper\Hooks) - remove class filter/action
- Added Acf\LocalJson utility

## [0.1.0] - 2023-04-06

### Added

- Created package
- Added Options utilities
- Added Features classes

[unreleased]: https://github.com/airfleet/airfleet-wordpress-framework-php/compare/1.2.1...main
[0.1.0]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/0.1.0
[0.2.0]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/0.2.0
[0.2.1]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/0.2.1
[0.3.0]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/0.3.0
[0.4.0]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/0.4.0
[0.5.0]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/0.5.0
[0.6.0]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/0.6.0
[1.0.0]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/1.0.0
[1.1.0]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/1.1.0
[1.2.0]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/1.2.0

[1.2.1]: https://github.com/airfleet/airfleet-wordpress-framework-php/releases/tag/1.2.1
