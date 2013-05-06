<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Assets
 *
 * @package     Assets
 * @subpackage  Libraries
 * @category    Asset Management
 * @author      Jack Boberg
 * @link        https://github.com/jackboberg/CodeIgniter-Assets
 * @license        http://creativecommons.org/licenses/BSD/
 */

include_once(APPPATH.'libraries/Assets.class.php');
class Assets extends Assets_class {
	
	/**
     * get links to stored assets for this group, of specified type
     *
     * @access  public
     * @param   string  $group      name of the group
     * @param   array   $config     optional settings
     * @param   string  $type       asset type
     *
     * @return  string
     **/
    public function get_assets($group = NULL, $config = array(), $type = NULL, $raw = false)
    {
		log_message('error', 'type:'.print_r($type,true));
        if (is_null($group))
        {
            $group = 'main';
        }
        if (empty($this->current_group)) 
        {
            $this->current_group = $group;
        }
        $output = '';
        if (is_null($type))
        {
            $output .= $this->get_assets($group, $config, 'css', $raw);
            $output .= $this->get_assets($group, $config, 'js', $raw);
            return $output;
        }
        // do we have assets of this type?
        $assets = $this->get_group_assets($group);
        if (empty($assets[$type]))
        {
            return $output;
        }
        // setup config options
        extract($this->get_config_options($config));
        // get the output
		
        if ($raw) {
            switch ($type)
            {
                case 'css':
                    $output .= "\n\t<!-- CSS Assets -->\n\t";
                    // is there a specified media type?
                    $media = isset($config['media'])
                        ? $config['media']
                        : 'all'
                        ;
                    $output .= $this->get_links('css', $assets['css'], false, false, $media);
                    break;
                case 'js':
                    
                    $output .= "\n\t<!-- JS Assets -->\n\t";
                    $output .= $this->get_links('js', $assets['js'], false, false);
                    
                    break;
            }
        }
        
        else {
            switch ($type)
            {
                case 'css':
                    $output .= "\n\t<!-- CSS Assets -->\n\t";
                    // is there a specified media type?
                    $media = isset($config['media'])
                        ? $config['media']
                        : 'all'
                        ;
                    $output .= $this->get_links('css', $assets['css'], $combine_css, $minify_css, $media);
                    break;
                case 'js':
                    $output .= "\n\t<!-- JS Assets -->\n\t";
                    $output .= $this->get_links('js', $assets['js'], $combine_js, $minify_js);
                    break;
            }
        }
        
			
        $this->current_group = NULL;
        return $output;
    }
	
}
