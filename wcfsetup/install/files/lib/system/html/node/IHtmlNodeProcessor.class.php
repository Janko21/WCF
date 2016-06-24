<?php
namespace wcf\system\html\node;

/**
 * Default interface for html node processors.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2016 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Node
 * @since       3.0
 */
interface IHtmlNodeProcessor {
	/**
	 * Returns the currently loaded DOM document.
	 * 
	 * @return      \DOMDocument    active DOM document
	 */
	public function getDocument();
	
	/**
	 * Returns the final HTML for storage or display.
	 * 
	 * @return      string  parsed HTML
	 */
	public function getHtml();
	
	/**
	 * Loads a HTML string for processing.
	 * 
	 * @param       string  $html   HTML string
	 */
	public function load($html);
	
	/**
	 * Processes the HTML and transforms it depending on the output type.
	 */
	public function process();
}
