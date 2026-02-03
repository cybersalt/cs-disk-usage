# CS Disk Usage

A Joomla 5 administrator component that displays disk usage by folder, similar to cPanel's disk usage tool.

## Features

- **Visual disk usage display** - See folder sizes with colored progress bars
- **Folder navigation** - Click through folders to explore disk usage at any level
- **Quick access** - Jump directly to common Joomla directories
- **Breadcrumb navigation** - Always know where you are in the folder structure
- **Summary statistics** - View total size, file count, and folder count
- **Sorted by size** - Largest folders appear first for easy identification

## Requirements

- Joomla 5.0 or later
- PHP 8.1 or later

## Installation

1. Download the latest release ZIP file
2. In Joomla Administrator, go to System > Install > Extensions
3. Upload the ZIP file
4. The component will appear in the administrator menu as "CS Disk Usage"

## Usage

After installation, access the component from the administrator menu. You'll see:

1. **Breadcrumb navigation** at the top showing your current location
2. **Summary card** showing total size, files, and folders for the current directory
3. **Subfolder list** with usage bars showing relative size
4. **Quick access panel** with links to common Joomla directories

Click any folder name to navigate into it and see its contents.

## Building from Source

Use 7-Zip to create the installation package:

```powershell
& 'C:\Program Files\7-Zip\7z.exe' a -tzip com_csdiskusage_v1.0.0.zip com_csdiskusage.xml script.php admin
```


## License

This project is licensed under the GNU General Public License v3.0 (GPL-3.0).
See the LICENSE file for details.

## Author

- **Developer:** Tim Davis
- **Company:** Cybersalt Consulting Ltd.
- **Website:** https://cybersalt.com
- **Support:** support@cybersalt.com
