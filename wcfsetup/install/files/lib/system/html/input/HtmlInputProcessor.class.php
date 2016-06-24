<?php
namespace wcf\system\html\input;
use wcf\system\bbcode\HtmlBBCodeParser;
use wcf\system\html\input\filter\IHtmlInputFilter;
use wcf\system\html\input\filter\MessageHtmlInputFilter;
use wcf\system\html\input\node\HtmlInputNodeProcessor;
use wcf\system\html\input\node\IHtmlInputNodeProcessor;
use wcf\util\StringUtil;

/**
 * Reads a HTML string, applies filters and parses all nodes including bbcodes.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2016 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Input
 * @since       3.0
 */
class HtmlInputProcessor {
	/**
	 * list of embedded content grouped by type
	 * @var array
	 */
	protected $embeddedContent = [];
	
	/**
	 * @var	IHtmlInputFilter
	 */
	protected $htmlInputFilter;
	
	/**
	 * @var	IHtmlInputNodeProcessor
	 */
	protected $htmlInputNodeProcessor;
	
	/**
	 * Processes input HTML by applying filters and parsing all nodes
	 * including bbcodes.
	 * 
	 * @param       string          $html           html string
	 */
	public function process($html) {
		// enforce consistent newlines
		$html = StringUtil::unifyNewlines($html);
		
		// transform bbcodes into metacode markers
		$html = HtmlBBCodeParser::getInstance()->parse($html);
		
		// filter HTML
		$html = $this->getHtmlInputFilter()->apply($html);
		
		// pre-parse HTML
		$this->getHtmlInputNodeProcessor()->load($html);
		$this->getHtmlInputNodeProcessor()->process();
		$this->embeddedContent = $this->getHtmlInputNodeProcessor()->getEmbeddedContent();
	}
	
	public function validate() {
		// TODO
	}
	
	/**
	 * Returns the parsed HTML ready to store.
	 * 
	 * @return      string  parsed html
	 */
	public function getHtml() {
		return $this->getHtmlInputNodeProcessor()->getHtml();
	}
	
	/**
	 * Returns the all embedded content data.
	 *
	 * @return array
	 */
	public function getEmbeddedContent() {
		return $this->embeddedContent;
	}
	
	/**
	 * @return	IHtmlInputFilter
	 */
	protected function getHtmlInputFilter() {
		if ($this->htmlInputFilter === null) {
			$this->htmlInputFilter = new MessageHtmlInputFilter();
		}
		
		return $this->htmlInputFilter;
	}
	
	/**
	 * @return IHtmlInputNodeProcessor
	 */
	protected function getHtmlInputNodeProcessor() {
		if ($this->htmlInputNodeProcessor === null) {
			$this->htmlInputNodeProcessor = new HtmlInputNodeProcessor();
		}
		
		return $this->htmlInputNodeProcessor;
	}
}
