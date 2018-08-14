<?php
/*
 *
 */
namespace FishPig\WordPress\Model\App;

use FishPig\WordPress\Model\App\Path as WordPressPath;

class WPConfig
{   
	protected $path;
	protected $data;
	
	public function __construct(WordPressPath $wpPath)
	{
		$this->path = $wpPath->getPath();
		
		if ($this->path) {
			$this->initialise();
		}
	}

	protected function initialise()
	{
		if (is_null($this->data)) {
			$this->data = false;
			
			$wpConfig = file_get_contents($this->path . '/wp-config.php');

			# Cleanup comments
			$wpConfig = str_replace("\n", "\n\n", $wpConfig);
			$wpConfig = preg_replace('/\n\#[^\n]{1,}\n/', "\n", $wpConfig);
			$wpConfig = preg_replace('/\n\\/\/[^\n]{1,}\n/', "\n", $wpConfig);
			$wpConfig = preg_replace('/\n\/\*.*\*\//Us', "\n", $wpConfig);

			if (!preg_match_all('/define\([\s]*["\']{1}([A-Z_0-9]+)["\']{1}[\s]*,[\s]*(["\']{1})([^\\2]*)\\2[\s]*\)/U', $wpConfig, $matches)) {
				\FishPig\WordPress\Model\App\Integration\Exception::throwException('Unable to extract values from wp-config.php');
			}

			$this->data = array_combine($matches[1], $matches[3]);
			
			if (preg_match_all('/define\([\s]*["\']{1}([A-Z_0-9]+)["\']{1}[\s]*,[\s]*(true|false|[0-9]{1,})[\s]*\)/U', $wpConfig, $matches)) {			
				$temp = array_combine($matches[1], $matches[2]);
				
				foreach($temp as $k => $v) {
					if ($v === 'true') {
						$this->data[$k] = true;
					}
					else if ($v === 'false') {
						$this->data[$k] = false;
					}
					else {
						$this->data[$k] = $v;
					}
				}
			}

			if (preg_match('/\$table_prefix[\s]*=[\s]*(["\']{1})([a-zA-Z0-9_]+)\\1/', $wpConfig, $match)) {
				$this->data['DB_TABLE_PREFIX'] = $match[2];
			}
			else {
				$this->data['DB_TABLE_PREFIX'] = 'wp_';
			}

			foreach($this->data as $key => $value) {
				$key = 'FISHPIG_' . $key;
				
				if (!defined($key))	{
					define($key, $value);
				}
			}
		}
	}
	
	/*
	 *
	 *
	 * @param  string|null $key = null
	 * @return mixed
	 */
	public function getData($key = null)
	{
		if (is_null($key)) {
			return $this->data;
		}
		
		return isset($this->data[$key]) ? $this->data[$key] : false;
	}
}
