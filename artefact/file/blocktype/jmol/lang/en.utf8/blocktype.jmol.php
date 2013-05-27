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
 * http://jmol.sf.net
 */

defined('INTERNAL') || die();

$string['title'] = 'Jmol';
$string['description'] = 'Select chemical structure files for embedded viewing with Jmol';
$string['showdescription'] = 'Show file description?';
$string['width'] = 'Width';
$string['height'] = 'Height';
$string['jmolscript'] = 'Jmol startup script';
$string['jmolscriptdescription'] = 'Optional <a target="blank" title="Jmol interactive scripting documentation" href="http://chemapps.stolaf.edu/jmol/docs/">Jmol script</a> to be loaded with Jmol applet to customise initial display';
$string['controlscript'] = 'Jmol.js JavaScript';
$string['controlscriptdescription'] = 'Optional <a target="blank" title="Jmol.js JavaScript Library" href="http://jmol.sourceforge.net/jslibrary/">Jmol.js JavaScript</a> to add custom controls below Jmol applet<br />
e.g. <a target="blank" href="http://jmol.sourceforge.net/jslibrary/#jmolButton">jmolButton</a>, <a target="blank" href="http://jmol.sourceforge.net/jslibrary/#jmolLink">jmolLink</a>, <a target="blank" href="http://jmol.sourceforge.net/jslibrary/#jmolCheckbox">jmolCheckbox</a>, <a target="blank" href="http://jmol.sourceforge.net/jslibrary/#jmolRadioGroup">jmolRadioGroup</a>, <a target="blank" href="http://jmol.sourceforge.net/jslibrary/#jmolMenu">jmolMenu</a>, <a target="blank" href="http://jmol.sourceforge.net/jslibrary/#jmolHtml">jmolHtml</a> and <a target="blank" href="http://jmol.sourceforge.net/jslibrary/#jmolBr">jmolBr</a>.';
$string['jmol'] = 'Jmol';
$string['Style'] = 'Style';
$string['Stick'] = 'Stick';
$string['Wireframe'] = 'Wireframe';
$string['Ball and stick'] = 'Ball and stick';
$string['Spacefill'] = 'Spacefill';
$string['Display style'] = 'Display style';
$string['Hydrogens'] = 'Hydrogens';
$string['Show/hide hydrogen atoms'] = 'Show/hide hydrogen atoms';
$string['Spin'] = 'Spin';
$string['Toggle spin on/off'] = 'Toggle spin on/off';
$string['typeremoved'] = 'This block points to a chemical structure type that has been disallowed by the administrator';
$string['configdesc'] = 'Configure which file types users can embed into this block.  If you disable a filetype that has already been used in a block, it will not be rendered any more';
?>
