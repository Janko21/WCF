<?php
namespace wcf\system\html\output\node;
use wcf\system\application\ApplicationHandler;
use wcf\system\html\node\AbstractHtmlNode;
use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\request\RouteHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Processes quotes.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2016 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Output\Node
 * @since       3.0
 */
class HtmlOutputNodeBlockquote extends AbstractHtmlNode {
	/**
	 * @inheritDoc
	 */
	protected $tagName = 'blockquote';
	
	/**
	 * @inheritDoc
	 */
	public function process(array $elements, AbstractHtmlNodeProcessor $htmlNodeProcessor) {
		/** @var \DOMElement $element */
		foreach ($elements as $element) {
			$nodeIdentifier = StringUtil::getRandomID();
			$htmlNodeProcessor->addNodeData($this, $nodeIdentifier, [
				'author' => ($element->hasAttribute('data-author')) ? $element->getAttribute('data-author') : '',
				'url' => ($element->hasAttribute('data-url')) ? $element->getAttribute('data-url') : ''
			]);
			
			$htmlNodeProcessor->renameTag($element, 'wcfNode-' . $nodeIdentifier);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function replaceTag(array $data) {
		$externalQuoteLink = (!empty($data['url'])) ? !ApplicationHandler::getInstance()->isInternalURL($data['url']) : false;
		if (!$externalQuoteLink) {
			$data['url'] = preg_replace('~^https://~', RouteHandler::getProtocol(), $data['url']);
		}
		
		$quoteAuthorObject = null;
		if ($data['author'] && !$externalQuoteLink) {
			$quoteAuthorLC = mb_strtolower(StringUtil::decodeHTML($data['author']));
			foreach (MessageEmbeddedObjectManager::getInstance()->getObjects('com.woltlab.wcf.quote') as $user) {
				if (mb_strtolower($user->username) == $quoteAuthorLC) {
					$quoteAuthorObject = $user;
					break;
				}
			}
		}
		
		WCF::getTPL()->assign([
			'quoteLink' => $data['url'],
			'quoteAuthor' => $data['author'],
			'quoteAuthorObject' => $quoteAuthorObject,
			'isExternalQuoteLink' => $externalQuoteLink
		]);
		return WCF::getTPL()->fetch('quoteMetaCode');
	}
}
