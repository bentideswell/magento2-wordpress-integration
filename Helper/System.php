<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Helper_System extends Fishpig_Wordpress_Helper_Abstract
{
	/**
	 * Useragent for CURL request
	 *
	 * @var string
	 */
	const CURL_USERAGENT = 'FishPig-MagentoWordPressIntegration';
	
	/**
	 * Cache for the integration results
	 *
	 * @var array
	 */
	protected $_integrationTestResults = null;
	
	/**
	 * Generate and retrieve the integration test results
	 *
	 * @return array
	 */
	public function getIntegrationTestResults()
	{
		if (!Mage::helper('wordpress')->isEnabled()) {
			return false;
		}
		
		if ($this->_integrationTestResults !== null) {
			return $this->_integrationTestResults;
		}

		$this->_integrationTestResults = array();

		Mage::dispatchEvent('wordpress_integration_tests_before', array('helper' => $this));
		
		if ($this->applyTest('_validateDatabaseConnection')) {
			if (Mage::helper('wordpress')->isFullyIntegrated()) {
				$this->applyTest('_validateHomeUrl');
				$this->applyTest('_validatePath');
				$this->applyTest('_validateTheme');
				$this->applyTest('_validatePlugins', array());
				$this->applyTest('_validatePermalinks');
				$this->applyTest('_validateHtaccess');
				$this->applyTest('_validateL10nPermissions');

				Mage::dispatchEvent('wordpress_integration_tests_after', array('helper' => $this));
			}
		}


		return $this->_integrationTestResults;
	}
	
	/**
	 * Check whether the database is connected
	 *
	 * @return void
	 */
	protected function _validateDatabaseConnection()
	{
		if (Mage::helper('wordpress/app')->getDbConnection() === false) {
			throw Fishpig_Wordpress_Exception::error(
				'Database Error',
				$this->__('Error establishing a database connection')
				. '. You can confirm your WordPress database details by opening the file wp-config.php, which is in your WordPress root directory.'
			);
		}
		
		return true;
	}
	
	/**
	 * Determine whether the blog route is valid
	 *
	 * @return Varien_Object
	 */
	protected function _validateHomeUrl()
	{
		$helper = Mage::helper('wordpress');

		$site = rtrim($helper->getWpOption('siteurl'), '/');
		$home = rtrim($helper->getWpOption('home'), '/');
		$mage = rtrim(($helper->getUrl()), '/');

		if ($site === $mage) {
			throw Fishpig_Wordpress_Exception::error('Site URL', 
				$this->__('Your integrated blog URL (%s) matches your WordPress Site URL. Either change your blog route below or move WordPress to a different sub-directory.', $mage)
			);
		}
		else if ($mage !== $home) {
			throw Fishpig_Wordpress_Exception::error('Home URL', 
				stripslashes(Mage::helper('wordpress')->__('Your WordPress home URL %s is invalid.  Please fix the <a href=\"%s\">home option</a>.', $home,  'http://codex.wordpress.org/Changing_The_Site_URL" target="_blank'))
				. $this->__(' Change to %s', $mage)
			);
		}

		if ($helper->getBlogRoute() && is_dir(Mage::getBaseDir() . DS . $helper->getBlogRoute())) {
			throw Fishpig_Wordpress_Exception::error('Home URL', 
				stripslashes(Mage::helper('wordpress')->__("A '%s' directory exists in your Magento root that will stop your integrated WordPress from displaying. You must delete this before your blog will display.", $helper->getBlogRoute()))
			);
		}
		
		return true;
	}
	
	/**
	 * Ensure the correct WordPress theme is installed
	 *
	 * @return bool
	 */
	protected function _validateTheme()
	{
		if (Mage::helper('wordpress')->getWpOption('template') !== 'twentytwelve') {
			throw Fishpig_Wordpress_Exception::error('Themes', 
				stripslashes(Mage::helper('wordpress')->__('You are using a non-supported WordPress theme that has not been tested. To improve your integration, enable the Twenty Twelve WordPress theme.'))
			);
		}
		
		return true;
	}
	
	/**
	 * Determine whether the WordPress path is valid
	 *
	 * @return void
	 */
	protected function _validatePath()
	{
		if (Mage::helper('wordpress')->getWordPressPath() === false) {
			throw Fishpig_Wordpress_Exception::error(
				'WordPress ' . $this->__('Path'), 
				$this->__("Unable to find a WordPress installation at '%s'", Mage::helper('wordpress')->getRawWordPressPath())
			);
		}
		
		return true;
	}
	
	/**
	 * Validate the plugins/extensions
	 *
	 * @param Varien_Object $params
	 * @return void
	 */
	protected function _validatePlugins(Varien_Object $params)
	{
		$file = Mage::getModuleDir('etc', 'Fishpig_Wordpress') . DS . 'fishpig.xml';

		if (!is_file($file)) {
			return $this;
		}
		
		$xml = simplexml_load_file($file);
		$results = $params->getResults();
		
		foreach((array)$xml->fishpig->extensions as $moduleName => $data) {
			$this->applyTest('_validatePlugin', array_merge(
				(array)$data, 
				array('current_version' => (string)Mage::getConfig()->getNode()->modules->$moduleName->version)
			));
		}
		
		$params->setResults($results);
		
		return $this;
	}
	
	/**
	 * Validate a single plugin
	 *
	 * @param Varien_Object $params
	 * @return void
	 */
	protected function _validatePlugin(Varien_Object $params)
	{
		if ($params->getCurrentVersion() && version_compare($params->getNewVersion(), $params->getCurrentVersion(), '>')) {
			throw Fishpig_Wordpress_Exception::warning($params->getName(), $this->__('You have version %s installed. Update to %s.', 
				$params->getCurrentVersion(), 
				sprintf('<a href="%s" target="_blank">%s</a>', $params->getUrl(), $params->getNewVersion())
			));
		}
		
		if ($params->getId() && !$params->getCurrentVersion()) {
			if (Mage::helper('wordpress')->isPluginEnabled($params->getId())) {
				throw Fishpig_Wordpress_Exception::warning(
					$params->getName(),
					$this->__('Extension required for plugin to work. ') . $this->__('Install %s', sprintf('<a href="%s" target="_blank">extension</a>.', $params->getUrl()))
				);
			}
		}
		
		return $this;
	}

	/**
	 * Ensure that custom permalinks are setup
	 *
	 * @return $this
	 */
	protected function _validatePermalinks()
	{
		Mage::helper('wordpress/app')->init();
		
		if (Mage::getModel('wordpress/post')->setPostType('post')->getTypeInstance()->useGuidLinks()) {
			throw Fishpig_Wordpress_Exception::warning(
				'Permalinks',
				'You are using the default permalinks. To stop potential duplicate content issues, change them to something else in the WordPress Admin.'
			);
		}	
		
		return $this;
	}

	/**
	 * Ensure the .htaccess file exists and doesn't reference the blog route
	 *
	 * @return $this
	 */
	protected function _validateHtaccess()
	{
		if (isset($_SERVER['SERVER_SOFTWARE']) && strpos(strtolower($_SERVER["SERVER_SOFTWARE"]), 'nginx') !== false) {
			return $this;
		}

		if (($path = Mage::helper('wordpress')->getWordPressPath()) !== false) {
			$file = rtrim($path, DS) . DS . '.htaccess';
			
			if (!is_file($file)) {
				throw Fishpig_Wordpress_Exception::warning(
					'.htaccess',
					'You do not have a WordPress .htaccess file.'
				);
			}
			
			if (is_readable($file) && ($data = @file_get_contents($file))) {
				$blogRoute = Mage::helper('wordpress')->getBlogRoute();

				if (preg_match('/\nRewriteBase \/' . preg_quote($blogRoute, '/') . '\//i', $data)) {
					throw Fishpig_Wordpress_Exception::warning(
						'.htaccess',
						'Your .htaccess file references your blog route but should reference your WordPress installation directory.'
					);
				}
			}
		}

		return $this;
	}
	
	/**
	 * Ensure the L10n file is writable if using add-on extensions that use Core
	 *
	 * @return $this
	 **/
	protected function _validateL10nPermissions()
	{
		if (($path = Mage::helper('wordpress')->getWordPressPath()) !== false) {
			$file = $path . 'wp-includes' . DS . 'l10n.php';

			if (Mage::getConfig()->getNode('wordpress/core/modules')) {
				if (is_file($file) && !is_writable($file)) {
					throw Fishpig_Wordpress_Exception::error(
						'Permissions',
						'The following file must be writable: ' . $file
					);
				}
			}
		}
		
		return $this;
	}
	
	/**
	 * Apply an integration test
	 *
	 * @param string $func
	 * @param array $results
	 * @param mixed $params = null
	 * @return mixed
	 */
	public function applyTest($func, $params = null)
	{
		$funcResult = false;
		
		try {
			if (is_array($params)) {
				$params = new Varien_Object($params);
				$params->setResults($this->_integrationTestResults);
			}
			else {
				$params = null;
			}

			if (is_array($func)) {
				$funcResult = call_user_func($func, $params);
			}
			else {
				$funcResult = $this->$func($params);
			}
			
			if ($params) {
				$results = $params->getResults();
			}
			
			return true;
		}
		catch (Fishpig_Wordpress_Exception $e) {
			switch($e->getCode()) {
				case 1: 
					$colour = '#00CC33';	
					break;
				case 2:
					$colour = 'yellow';
					break;
				case 3:
					$colour = '#FF3333';
					break;
				default:
					$colour = '#444';
			}

			$this->_integrationTestResults[] = new Varien_Object(array(
				'title' => Mage::helper('wordpress')->__($e->getMessage()),
				'message' => $e->getLongMessage(),
				'bg_colour' => $colour,
			));
		}
		catch (Exception $e) {
			$this->_integrationTestResults[] = new Varien_Object(array(
				'title' => Mage::helper('wordpress')->__('An unidentified error has occurred.'),
				'message' => $e->getMessage(),
				'bg_colour' => '#444',
			));
		}
		
		return $funcResult;
	}
	
	/**
	 * Attempt to login to WordPress
	 *
	 * @param string $username
	 * @param string $password
	 * @param string $destination
	 * @return bool
	 */
	public function loginToWordPress($username, $password, $destination = null, $redirect = true)
	{
		if (is_null($destination)) {
			$destination = Mage::helper('wordpress')->getAdminUrl('index.php');
		}
		
		// Required for some hosting companies (1&1)
		$result = $this->makeHttpGetRequest(Mage::helper('wordpress')->getBaseUrl('wp-login.php'));

		$result = $this->makeHttpPostRequest(Mage::helper('wordpress')->getBaseUrl('wp-login.php'), array(
			'log' => $username,
			'pwd' => $password,
			'rememberme' => 'forever',
			'redirect_to' => $destination,
		));

		if ($result !== false) {
			if (strpos($result, 'Location: ') === false) {
				throw new Exception('WordPress Auto Login Failed: ' . substr($result, 0, strpos($result, "\r\n\r\n")));
			}
	
			foreach(explode("\n", $result) as $line) {
				if (substr(ltrim($line), 0, 1) === '<' && strpos($line, ':') !== false) {
					break;
				}
	
				if ($redirect === false && strpos(ltrim($line), 'Location') === 0) {
					continue;
				}

				header($line, false);
			}
	
			return true;
		}
		
		return false;
	}
	
	public function makeHttpGetRequest($url)
	{
		if (!$this->hasValidCurlMethods()) {
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_USERAGENT, self::CURL_USERAGENT);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
			if (strpos($url, 'https://') !== false) {
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			}
		
			$response = curl_exec($ch);

			if (curl_errno($ch) || curl_error($ch)) {
				throw new Exception(Mage::helper('wordpress')->__('CURL (%s): %s', curl_errno($ch), curl_error($ch)));
			}

			curl_close($ch);	

			return $response;
		}

		$curl = new Varien_Http_Adapter_Curl();

		$curl->setConfig(array(
			'verifypeer' => strpos($url, 'https://') !== false,
			'header' => true,
			'timeout' => 15,
			'referrer' => Mage::helper('wordpress')->getBaseUrl('wp-login.php'),
		));
		
		$curl->addOption(CURLOPT_FOLLOWLOCATION, true);
		$curl->addOption(CURLOPT_USERAGENT, self::CURL_USERAGENT);
		$curl->addOption(CURLOPT_REFERER, true);

		$curl->write(Zend_Http_Client::GET, $url, '1.1');

		$response = $curl->read();

		if ($curl->getErrno() || $curl->getError()) {
			throw new Exception(Mage::helper('wordpress')->__('CURL (%s): %s', $curl->getErrno(), $curl->getError()));
		}

		$curl->close();
		
		return $response;
	}
		
	/**
	 * Send a HTTP Post request
	 *
	 * @param string $url
	 * @param array $data = array
	 * @return false|string
	 */
	public function makeHttpPostRequest($url, array $data = array())
	{
		if (!$this->hasValidCurlMethods()) {
			foreach($data as $key => $value) {
				$data[$key] = urlencode($key) . '=' . urlencode($value);
			}
		
			$body = implode('&', $data);
		
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, count($data));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
			curl_setopt($ch, CURLOPT_USERAGENT, self::CURL_USERAGENT);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			
			if (strpos($url, 'https://') !== false) {
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			}
			
			$response = curl_exec($ch);

			if (curl_errno($ch) || curl_error($ch)) {
				throw new Exception(Mage::helper('wordpress')->__('CURL (%s): %s', curl_errno($ch), curl_error($ch)));
			}

			curl_close($ch);	

			return $response;
		}

		$curl = new Varien_Http_Adapter_Curl();

		$curl->setConfig(array(
			'verifypeer' => strpos($url, 'https://') !== false,
			'header' => true,
			'timeout' => 15,
			'referrer' => Mage::helper('wordpress')->getBaseUrl('wp-login.php'),
		));
		
		$curl->addOption(CURLOPT_FOLLOWLOCATION, false);
		$curl->addOption(CURLOPT_USERAGENT, self::CURL_USERAGENT);
		$curl->addOption(CURLOPT_REFERER, true);

		$curl->write(Zend_Http_Client::POST, $url, '1.1', array('Expect:'), $data);

		$response = $curl->read();

		if ($curl->getErrno() || $curl->getError()) {
			throw new Exception(Mage::helper('wordpress')->__('CURL (%s): %s', $curl->getErrno(), $curl->getError()));
		}

		$curl->close();
		
		return $response;
	}

	/**
	 * Retrieve the extension version
	 *
	 * @param string $extension
	 * @return string
	 */
	public function getExtensionVersion($extension = 'Fishpig_Wordpress')
	{
		return (string)Mage::getConfig()->getNode('modules/' . $extension . '/version');
	}
	
	/**
	 * Has valid CURL methods
	 *
	 * @return bool
	 */
	public function hasValidCurlMethods()
	{
		return method_exists('Varien_Http_Adapter_Curl', 'addOption');
	}
}
