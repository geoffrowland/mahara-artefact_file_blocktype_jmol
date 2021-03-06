<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage blocktype-jmol
 * @author     James Kerrigan
 * @author     Geoffrey Rowland 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2011 James Kerrigan and Geoffrey Rowland geoff.rowland@yeovil.ac.uk
 *
 * This plugin uses the Jmol Java applet for interactive 3D rendering of
 * chemical structures
 * http://jmol.sourceforge.net
 * 
 * Updated for JSmol (JavaScript/HTML5 version of Jmol) Geoffrey Rowland 2013 growland@strode-college.ac.uk
 * http://sourceforge.net/projects/jsmol/
 *
 * Examples of chemical structure file formats, viewable with Jmol/JSmol, are available from
 * http://jmol.svn.sourceforge.net/viewvc/jmol/trunk/Jmol-datafiles/
 */

defined('INTERNAL') || die();

class PluginBlocktypeJmol extends PluginBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.file/jmol');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.file/jmol');
    }

    public static function get_categories() {
        return array('fileimagevideo');
    }

    public static function has_config() {
        return true;
    }

    public static function postinst($oldversion) {
        if ($oldversion == 0) {
            set_config_plugin('blocktype', 'jmol', 'enabledtypes', serialize(array('alc', 'cif', 'cml', 'gz', 'hin', 'mcif', 'mol', 'mol2', 'pdb', 'pse', 'sdf', 'xyz')));
        }
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        $configdata = $instance->get('configdata');

        if (empty($configdata['artefactid'])) {
            return '';
        }
        $result = self::get_js_source();             
        require_once(get_config('docroot') . 'artefact/lib.php');
        $artefact = $instance->get_artefact_instance($configdata['artefactid']);
        if (!file_exists($artefact->get_path())) {
           return;
        }
        $description = $artefact->get('description');
        $width  = (!empty($configdata['width'])) ? hsc($configdata['width']) : '300';
        $height = (!empty($configdata['height'])) ? hsc($configdata['height']) : '300';
        $jmolscript  = (!empty($configdata['jmolscript'])) ? clean_html($configdata['jmolscript']) : null;
        $initscript = str_replace('"', "'", str_replace("\n", "", trim($jmolscript)));
        $controlscript  = (!empty($configdata['controlscript'])) ? clean_html($configdata['controlscript']) : null;
        $controls = str_replace(";\n", "+", trim($controlscript));
        $controls = str_replace("\n", "", trim($controlscript));
        $mimetype = $artefact->get('filetype');
        $mimetypefiletypes = self::get_allowed_mimetype_filetypes();
        if (!isset($mimetypefiletypes[$mimetype])) {
            return get_string('typeremoved', 'blocktype.file/jmol');
        }
        $callbacks = self::get_all_filetype_players();
        $result .= '<div class="jmol-container center"><div class="jmol">' . call_static_method('PluginBlocktypeJmol', $callbacks[$mimetypefiletypes[$mimetype]], $artefact, $instance, $width, $height, $initscript, $controls) . '</div></div>';
        if (!empty($configdata['showdescription']) && !empty($description)) {
            $result .= '<p>'.$description.'</p>';
        }
        return $result;
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        $configdata = $instance->get('configdata');
        safe_require('artefact', 'file');
        $instance->set('artefactplugin', 'file');
        $defaultloadscript = 'set antialiasDisplay on;';
        $defaultcontrolscript = '
jmolMenu([
["#optgroup", "'.get_string('Style', 'blocktype.file/jmol').'"],
["wireframe only", "'.get_string('Wireframe', 'blocktype.file/jmol').'"],
["spacefill off; wireframe 0.15", "'.get_string('Stick', 'blocktype.file/jmol').'"],
["wireframe 0.15; spacefill 23%", "'.get_string('Ball and stick', 'blocktype.file/jmol').'", "selected"],
["spacefill on", "'.get_string('Spacefill', 'blocktype.file/jmol').'"],
["#optgroupEnd"]
],"","","'.get_string('Display style', 'blocktype.file/jmol').'")+
jmolHtml(" ")+
jmolCheckbox("set showHydrogens on", "set showHydrogens off", "'.get_string('Hydrogens', 'blocktype.file/jmol').'", "checked")+
jmolHtml(" ")+
jmolCheckbox("spin on", "spin off", "'.get_string('Spin', 'blocktype.file/jmol').'")
';
        return array(
            'artefactid' => self::filebrowser_element($instance, (isset($configdata['artefactid'])) ? array($configdata['artefactid']) : null),
            'showdescription' => array(
                'type'  => 'checkbox',
                'title' => get_string('showdescription', 'blocktype.file/jmol'),
                'defaultvalue' => !empty($configdata['showdescription']) ? true : false,
            ),
            'width' => array(
                'type' => 'text',
                'title' => get_string('width', 'blocktype.file/jmol'),
                'size' => 3,
                'defaultvalue' => (isset($configdata['width'])) ? $configdata['width'] : '300',
            ),
            'height' => array(
                'type' => 'text',
                'title' => get_string('height', 'blocktype.file/jmol'),
                'size' => 3,
                'defaultvalue' => (isset($configdata['height'])) ? $configdata['height'] : '300',
            ),
            'jmolscript' => array(
                'type' => 'textarea',
                'title' => get_string('jmolscript','blocktype.file/jmol'),
                'description' => get_string('jmolscriptdescription','blocktype.file/jmol'),
                'rows' => 5,
                'cols' => 60,
                'defaultvalue' => (!empty($configdata['jmolscript'])) ? $configdata['jmolscript'] : $defaultloadscript,
            ),
             'controlscript' => array(
                'type' => 'textarea',
                'title' => get_string('controlscript','blocktype.file/jmol'),
                'description' => get_string('controlscriptdescription','blocktype.file/jmol'),
                'rows' => 5,
                'cols' => 60,
                'defaultvalue' => (!empty($configdata['controlscript'])) ? $configdata['controlscript'] : $defaultcontrolscript,
            ),            
        );
    }

    public static function filebrowser_element(&$instance, $default=array()) {
        $element = ArtefactTypeFileBase::blockconfig_filebrowser_element($instance, $default);
        $element['title'] = get_string('jmol', 'blocktype.file/jmol');
        $element['name'] = 'artefactid';
        $element['config']['selectone'] = true;
        $element['filters'] = array(
            'artefacttype'    => array('file'),
            'filetype'        => self::get_allowed_mimetypes(),
        );
        return $element;
    }

    public static function artefactchooser_element($default=null) {
        return array(
            'name' => 'artefactid',
            'type'  => 'artefactchooser',
            'title' => get_string('jmol', 'blocktype.file/jmol'),
            'defaultvalue' => $default,
            'blocktype' => 'jmol',
            'limit' => 5,
            'selectone' => true,
            'artefacttypes' => array('file'),
            'template' => 'artefact:file:artefactchooser-element.tpl',
        );
    }

    public static function artefactchooser_get_element_data($artefact) {
        $artefact->icon = call_static_method(generate_artefact_class_name($artefact->artefacttype), 'get_icon', array('id' => $artefact->id));
        return $artefact;
    }
//  file type stuff
    public static function save_config_options($values) {
        $enabledtypes = array();
        foreach ($values as $type => $enabled) {
            if (!in_array($type, self::get_all_filetypes())) {
                continue;
            }
            if (!empty($enabled)) {
                $enabledtypes[] = $type;
            }
        }
        set_config_plugin('blocktype', 'jmol', 'enabledtypes', serialize($enabledtypes));
    }

    public static function get_config_options() {
        $elements = array();
        // Allowed file types
        $filetypes = array();
        $currenttypes = self::get_allowed_filetypes();

        foreach (self::get_all_filetypes() as $filetype) {
            $filetypes[$filetype] = array(
                'type'  => 'checkbox',
                'title' => get_string($filetype, 'artefact.file'),
                'defaultvalue' => in_array($filetype, $currenttypes),
            );
        }
        uasort($filetypes, create_function('$a, $b', 'return $a["title"] > $b["title"];'));
        $filetypes = array_merge(
            array(
                'description' => array(
                    'value' => get_string('configdesc', 'blocktype.file/jmol'),
                ),
            ),
            $filetypes
        );

        return array(
            'elements' => $filetypes,
            'renderer' => 'table'
        );
    }

    private static function get_allowed_filetypes() {
        if ($data = get_config_plugin('blocktype', 'jmol', 'enabledtypes')) {
            return unserialize($data);
        }
        return array();
    }

    private static function get_allowed_mimetypes() {
        return array_keys(self::get_allowed_mimetype_filetypes());
    }

    private static function get_allowed_mimetype_filetypes() {
        if ($data = self::get_allowed_filetypes()) {
            if ($mimetypes = get_records_sql_assoc('
                SELECT mimetype, description
                FROM {artefact_file_mime_types}
                WHERE description IN (' . join(',', array_map('db_quote', $data)) . ')', array())) {
                foreach ($mimetypes as &$m) {
                    $m = $m->description;
                }
                return $mimetypes;
            }
        }
        return array();
    }

    private static function get_all_filetypes() {
        return array_keys(self::get_all_filetype_players());
    }

    private static function get_all_filetype_players() {
        /* Keyed by the file type descriptions from the artefact_file_mime_types table */
        return array(
            'alc'       => 'jmol_player', // tested 
            'cif'       => 'jmol_player', // tested   
            'cml'       => 'jmol_player', // tested 
            'gz'        => 'jmol_player', // tested for pdb.gz
            'hin'       => 'jmol_player', // tested
            'mcif'      => 'jmol_player', // tested       
            'mol'       => 'jmol_player', // tested
            'mol2'      => 'jmol_player', // tested
            'pdb'       => 'jmol_player', // tested
            'pse'       => 'jmol_player', // tested
            'sdf'       => 'jmol_player', // tested
            'xyz'       => 'jmol_player', // tested
        );
    }
    
    // Jmol player
    public static function jmol_player($artefact, $block, $width, $height, $initscript, $controls) {
        static $count = 0;
        $count++;
        $id = time() . $count;
        $wwwroot = get_config('wwwroot');
        $url = self::get_download_link($artefact, $block);
        $molfile = explode(".", hsc($artefact->get('title')));

        // Use file extension to customise display options and binary file handling
        $molext = $molfile[1];        
        $molpath = $url . '&ext=.' . $molext;
        if ($molfile[2] == 'gz'){
	   $molpath = $molpath.'.gz';
	}	
        $html = '<div style="width:'.$width.'px">';
        $html .= '<a title="Download structure data file" href ="' . $url . '">' . hsc($artefact->get('title')) . '</a><br />';
        $html .= '</div>';
        $html .= '<div style="position:relative; width:'.$width.'px; height:'.$height.'px">'; // Container div to allow Jmol/JSmol z-index to be set        
        $html .= '<div id="jmoldiv'.$id.'" style="position:absolute; z-index:0; width:'.$width.'px; height:'.$height.'px; border: 1px solid lightgray; background-color: lightgray; background-image:url(\''.get_config('wwwroot').'artefact/file/blocktype/jmol/Jmol_icon_94.png\'); background-repeat: no-repeat"></div>';
        $html .= '</div>';       
        $html .= '<div id="controls'.$id.'" style="text-align:left; width:'.$width.'px; border: 1px solid lightgray"></div>';
        $html .= '<script type="text/javascript">';
        $html .= 'var info'.$id.' = {';
        $html .= 'color: "white",';
        $html .= 'width: '.$width.',';
        $html .= 'height: '.$height.',';
        $html .= 'debug: false,';
        $html .= 'serverURL: "'.$wwwroot.'artefact/file/blocktype/jmol/jsmol/php/jsmol.php",';
        $html .= 'use: "HTML5",';
        $html .= 'jarPath: "'.$wwwroot.'artefact/file/blocktype/jmol/jsmol/java",';
        $html .= 'j2sPath: "'.$wwwroot.'artefact/file/blocktype/jmol/jsmol/j2s",';
        $html .= 'jarFile: "JmolAppletSigned0.jar",';
        $html .= 'isSigned: true,';
        $html .= 'disableJ2SLoadMonitor: true,';
        $html .= 'disableInitialConsole: true,';
        $html .= 'addSelectionOptions: false,';
        $html .= 'allowjavascript: true,';
        if ($molext == "cif"){
            // fill 1 x 1 x 1 unit cell by default for CIF files (typically used for mineral crystal structures)
            $html .= 'script: "load \"CIF::'.$molpath.'\" {1 1 1} PACKED; '.$initscript.'"';
        }else if ($molext == "pdb"){
            // add hydrogens by default to PDB files (typically used for biological macromolecules)
            // $html .= 'script: "set pdbAddHydrogens true; load \"PDB::'.$molpath.'\"; select protein or nucleic; cartoons Only; color structure; select *; '.$initscript.'"';
            $html .= 'script: "set pdbAddHydrogens true; load \"PDB::'.$molpath.'\"; '.$initscript.'"';   
        }else{
            $html .= 'script: "load \"'.$molpath.'\"; '.$initscript.'"';
        }
        $html .= '};';
        
        // converts Jmol.js scripts to JSmol JSO syntax.
        $controls = str_replace("jmolCheckbox(", "Jmol.jmolCheckbox(jmol".$id.", ", $controls);
        $controls = str_replace("jmolButton(", "Jmol.jmolButton(jmol".$id.", ", $controls);
        $controls = str_replace("jmolMenu(", "Jmol.jmolMenu(jmol".$id.", ", $controls);
        $controls = str_replace("jmolCommandInput(", "Jmol.jmolCommandInput(jmol".$id.", ", $controls);
        $controls = str_replace("jmolRadioGroup(", "Jmol.jmolRadioGroup(jmol".$id.", ", $controls);
        $controls = str_replace("jmolRadio(", "Jmol.jmolRadio(jmol".$id.", ", $controls);
        $controls = str_replace("jmolHtml(", "Jmol._documentWrite(", $controls);
        $controls = str_replace("jmolBr()", "Jmol._documentWrite('<br />')", $controls);

        // display Jmol/JSmol. 
        // Uncomment the following  two lines if you are using MathJax to display formulae.
        // Delays display of JSmol until after MathJax has finished rendering and avoids Math Errors.
        //$html .= 'MathJax.Hub.Queue(function () {';               
        $html .= 'Jmol.setDocument(0);';
        $html .= 'Jmol._alertNoBinary = false;';
        $html .= 'Jmol.getApplet("jmol'.$id.'", info'.$id.');';
        $html .= 'jQuery("#jmoldiv'.$id.'").html(Jmol.getAppletHtml(jmol'.$id.'));';
        $html .= 'jQuery("#controls'.$id.'").html('.$controls.')';
        //$html .= '});';
        $html .= '</script>';
        return $html;
    }

    private static function get_download_link(ArtefactTypeFile $artefact, BlockInstance $instance) {

        return '../artefact/file/blocktype/jmol/download.php?file='
            .  $artefact->get('id')
            . '&view=' . $instance->get('view')
            . '&download=1';
    }
    
    private static function get_js_source() {
        if (defined('BLOCKTYPE_INTERNALMEDIA_JS_INCLUDED')) {
            return '';
        }
        define('BLOCKTYPE_INTERNALMEDIA_JS_INCLUDED', true);
        return '<script src="'.get_config('wwwroot').'artefact/file/blocktype/jmol/jsmol/JSmol.min.nojq.js"></script>';
    }
    
    public static function default_copy_type() {
        return 'full';
    }

}

?>
