<?php

namespace Combodo\iTop\Test\UnitTest\Application;

use Combodo\iTop\Test\UnitTest\ItopTestCase;
use iTopWebPage;

class csvimportTest extends ItopTestCase
{
	protected function setUp(): void
	{
		parent::setUp();
	}
	/**
	 * @return void
	 */
	public function testCSVImport() {
		$oPage = new iTopWebPage("BulkImport");
		$oPage->SetBreadCrumbEntry('ui-tool-bulkimport', 'CSVImportMenu', 'BulkImport', '', 'fas fa-file-import', iTopWebPage::ENUM_BREADCRUMB_ENTRY_ICON_TYPE_CSS_CLASSES);
		ProcessCSVData($oPage);

	}

}