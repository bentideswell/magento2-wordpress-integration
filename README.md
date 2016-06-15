#FishPig_WordPress
WordPress Integration in Magento 2.

This module allows you to integrate WordPress into Magento 2. This module is currently in beta so is not 100% stable.

#Installation Guide
This installation guide will use http://www.example.com as your Magento URL and http://www.example.com/blog/ as your integrated blog URL. You will need to substitute in your own domain blog route (eg. /blog/).

#Install WordPress
- <a href="https://wordpress.org/" target="_blank">Download WordPress</a> and upload it to a sub-directory of your Magento 2 installation called 'wp'.
- Follow the installation instructions and when finished, login to the WordPress Admin and select Settings > General.
- In the first URL field, enter the WordPress installation URL (eg. http://www.example.com/wp)
- In the second URL, enter the integrated blog URL(eg. http://www.example.com/blog)
- Press the 'Save Changes' button at the bottom of the page

You should also setup custom permalinks in the Settings > Permalinks section.

#Install the Extension
- Either download the files from GitHub or fork/clone this repo.
- The files should be saved at app/code/FishPig/WordPress in your Magento 2 installation.
- Login to the Magento Admin and select the WordPress menu option.
- Enter 'wp' for the path configuration and save.

You should now see your blog at http://www.example.com/blog/

#Yoast SEO
You can and should install the Yoast SEO plugin in the WordPress Admin. This is a completely free WordPress plugin that gives you complete control over your blogs SEO data (page titles, meta tags, robots, canonicals etc). For this to work when integrated into Magento 2, you will need the following add-on extension:

<a href="https://github.com/bentideswell/magento2-wordpress-integration-yoastseo" target="_blank">FishPig_WordPress_Yoast</a>
