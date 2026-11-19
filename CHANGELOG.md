# Changelog

## [Unreleased]

### 🔧 Improvements
- **Folder filter and sorting**: The subfolder table has a name filter box and sortable Folder, Usage/Size and Items columns.
- **Lock-out modal**: A non-dismissable modal names the operation ("Measuring folder sizes in ...") while the next folder is being measured.
- **Dark mode**: Usage bars use Bootstrap contextual colours instead of fixed hex values, so they follow the admin theme.
- **Page title**: The admin page title is now the language constant "Cybersalt Disk Usage Dashboard".
- **Support details**: New Support options (email, URL, label) and a support helper whose "please contact ..." sentence is used in the component's error messages.

### 🐛 Fixes
- **Options button**: The Options toolbar button now opens a real screen (Support and Permissions tabs); `config.xml` and `access.xml` were missing.
- **Folder links**: Links are URL-encoded so paths always survive the round trip.
- **Styles and scripts now install**: the manifest listed the CSS/JS folders in the wrong place, so Joomla never copied them and the page showed no usage bars. Moved the media block to the top level of the manifest.

### 🔍 Security
- **Folder access check**: The folder-containment check is now separator-aware (the site root itself or a real sub-folder only) and paths are resolved before the check.
- **Symbolic links**: Symbolic links are no longer followed when measuring folders.
- **Access control**: The component declares its permissions (manage, options, admin) and the screen re-checks the manage permission.

## 🚀 Version 1.0.0 (February 3, 2026)

Initial release.

### 📦 New Features
- **Disk usage browser**: Navigate the Joomla folder tree from the administrator with breadcrumb navigation, sizes, file and folder counts per folder.
- **Usage bars**: Visual bars show each subfolder's share of the current folder.
- **Quick Access**: One-click links to common Joomla folders (administrator, cache, components, images, media, modules, plugins, templates, tmp).

### 📝 Documentation
- Licence updated to GNU General Public License version 3 or later.
