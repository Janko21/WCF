<?php
namespace wcf\system\package\plugin;
use wcf\data\page\menu\item\PageMenuItemEditor;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Installs, updates and deletes page page menu items.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.plugin
 * @category	Community Framework
 */
class PageMenuPackageInstallationPlugin extends AbstractMenuPackageInstallationPlugin {
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::$className
	 */
	public $className = 'wcf\data\page\menu\item\PageMenuItemEditor';
	
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::prepareImport()
	 */
	protected function prepareImport(array $data) {
		$result = parent::prepareImport($data);
		
		// position
		$result['menuPosition'] = (!empty($data['elements']['position']) && $data['elements']['position'] == 'footer') ? 'footer' : 'header';
		
		// class name
		if (!empty($data['elements']['classname'])) {
			$result['className'] = $data['elements']['classname'];
		}
		
		// validate controller and link (cannot be empty at the same time)
		if (empty($result['menuItemLink']) && empty($result['menuItemController'])) {
			throw new SystemException("Menu item '".$result['menuItem']."' neither has a link nor a controller given");
		}
		
		return $result;
	}
	
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::cleanup()
	 */
	protected function cleanup() {
		PageMenuItemEditor::updateLandingPage();
	}
	
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::import()
	 */
	protected function import(array $row, array $data) {
		if (!empty($row)) {
			// ignore show order if null
			if ($data['showOrder'] === null) {
				unset($data['showOrder']);
			}
			else if ($data['showOrder'] != $row['showOrder']) {
				$data['showOrder'] = $this->getMenuItemPosition($data);
			}
		}
		else {
			$data['showOrder'] = $this->getMenuItemPosition($data);
		}
		
		parent::import($row, $data);
	}
	
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::getShowOrder()
	 */
	protected function getShowOrder($showOrder, $parentName = null, $columnName = null, $tableNameExtension = '') {
		// will be recalculated anyway
		return $showOrder;
	}
	
	/**
	 * Returns menu item position.
	 * 
	 * @param	array		$data
	 * @return	integer
	 */
	protected function getMenuItemPosition(array $data) {
		file_put_contents(WCF_DIR.'__pageMenu.log', "Calculating show order for '{$data['menuItem']}'\n", FILE_APPEND);
		
		if ($data['showOrder'] === null) {
			// get greatest showOrder value
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("menuPosition = ?", array($data['menuPosition']));
			if ($data['parentMenuItem']) $conditions->add("parentMenuItem = ?", array($data['parentMenuItem']));
			
			$sql = "SELECT	MAX(showOrder) AS showOrder
				FROM	wcf".WCF_N."_".$this->tableName."
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			$maxShowOrder = $statement->fetchArray();
			//return (!$maxShowOrder) ? 1 : ($maxShowOrder['showOrder'] + 1);
			$showOrder = (!$maxShowOrder) ? 1 : ($maxShowOrder['showOrder'] + 1);
		}
		else {
			file_put_contents(WCF_DIR.'__pageMenu.log', "\t!!! INCREASING SHOW ORDER !!!\n", FILE_APPEND);
			
			// increase all showOrder values which are >= $showOrder
			$sql = "UPDATE	wcf".WCF_N."_".$this->tableName."
				SET	showOrder = showOrder + 1
				WHERE	showOrder >= ?
					AND menuPosition = ?
					AND parentMenuItem = ".($data['parentMenuItem'] ? "?" : "''");
			$statement = WCF::getDB()->prepareStatement($sql);
			
			$parameters = array(
				$data['showOrder'],
				$data['menuPosition']
			);
			if ($data['parentMenuItem']) $parameters[] = $data['parentMenuItem'];
			
			$statement->execute($parameters);
			
			file_put_contents(WCF_DIR.'__pageMenu.log', "\n\nSQL\n\n{$sql}\n\nPARAMETERS\n\n".print_r($parameters, true)."\n\n", FILE_APPEND);
			
			// return the wanted showOrder level
			//return $data['showOrder'];
			$showOrder = $data['showOrder'];
		}
		
		$sql = "SELECT	showOrder
			FROM	wcf".WCF_N."_page_menu_item
			WHERE	menuItem = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array('wcf.user.dashboard'));
		$row = $statement->fetchArray();
		if ($row) {
			file_put_contents(WCF_DIR.'__pageMenu.log', "  show order of dashboard is now {$row['showOrder']}\n\n", FILE_APPEND);
		}
		
		return $showOrder;
	}
}
