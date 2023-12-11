<?php

namespace NodeSolutions\ExportCms\Controller\Adminhtml\Index;
use Magento\Framework\App\Filesystem\DirectoryList;

class Index extends \Magento\Backend\App\Action
{
    
    protected $resultPageFactory;
	protected $directoryList;
	protected $fileFactory;
    public function __construct(
    	\Magento\Framework\App\Response\Http\FileFactory $fileFactory,
    	\Magento\Framework\File\Csv $csvProcessor,
    	\Magento\Cms\Api\PageRepositoryInterface $pageRepositoryInterface,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
    	\Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
    	\Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
     	$this->fileFactory = $fileFactory;
    	$this->csvProcessor = $csvProcessor;
    	$this->directoryList = $directoryList;
    	$this->pageRepositoryInterface = $pageRepositoryInterface;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    	$this->resultRedirectFactory = $resultRedirectFactory;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function getPages() {
        $searchCriteria = $searchCriteria = $this->searchCriteriaBuilder->create();
        $pages = $this->pageRepositoryInterface->getList($searchCriteria)->getItems();
        return $pages;
    }

    public function execute()
    {
    	$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/cms.log');
		$logger = new \Zend_Log();
		$logger->addWriter($writer);

		$content[] = [
            'page_id' => __('Page ID'),
            'title' => __('Page Title'),
            'page_layout' => __('Page Layout'),
            'meta_keywords' => __('Meta Keywords'),
            'meta_description' => __('MetaDescription'),
            'identifier' => __('Identifier'),
            'content_heading' => __('Content Heading'),
            'content' => __('Content'),
            'creation_time' => __('Creation Time'),
            'update_time' => __('Updated Time'),
            'is_active' => __('Is Active'),
            'sort_order' => __('Sort Order'),
            'layout_update_xml' => __('Layout Update Xml'),
            'custom_theme' => __('Custom Theme'),
            'custom_root_template' => __('Custom Root Template'),
            'custom_layout_update_xml' => __('Custom Layout Update Xml'),
            'layout_update_selected' => __('Layout Update Selected'),
            'custom_theme_from' => __('Custom Theme From'),
            'custom_theme_to' => __('Custom Theme To'),
            'meta_title' => __('Meta Title'),
            '_first_store_id' => __('First Store Id'),
            'store_code' => __('Store Code'),
            'store_id' => __('Store Id')
        ];
        $fileName = 'imported_cms_page_data.csv';
    	$cmsPagesCollection = $this->getPages();
    	$filePath =  $this->directoryList->getPath(DirectoryList::MEDIA) . "/" . $fileName;
    	foreach ($cmsPagesCollection as $value) {
			$content[] = [
	    		$pageId = $value->getPageId(),
	    		$pageTitle = $value->getTitle(),
	    		$pagePageLayout = $value->getPageLayout(),
	    		$pageMetaKeywords = $value->getMetaKeywords(),
	    		$pageMetaDescription = $value->getMetaDescription(),
	    		$pageIdentifier = $value->getIdentifier(),
                $pageContentHeading = $value->getContentHeading(),
                $pageContent = $value->getContent(),
                $pageCreationTime = $value->getCreationTime(),
                $pageUpdateTime = $value->getUpdateTime(),
                $pageIsActive = $value->getIsActive(),
                $pageSortOrder = $value->getSortOrder(),
                $pageLayoutUpdateXml = $value->getLayoutUpdateXml(),
                $pageCustomTheme = $value->getCustomTheme(),
                $pageCustomRootTemplate = $value->getCustomRootTemplate(),
                $pageCustomLayoutUpdateXml = $value->getCustomLayoutUpdateXml(),
                $pageLayoutUpdateSelected = $value->getLayoutUpdateSelected(),
                $pageCustomThemeFrom = $value->getCustomThemeFrom(),
                $pageCustomThemeTo = $value->getCustomThemeTo(),
                $pageMetaTitle = $value->getMetaTitle(),
                $pageFirstStoreId = $value->getFirstStoreId(),
                $pageStoreCode = $value->getStoreCode()
	 		];
    	}
    	$this->csvProcessor->setEnclosure('"')->setDelimiter(',')->saveData($filePath, $content);
        return $this->fileFactory->create(
            $fileName,
            [
                'type'  => "filename",
                'value' => $fileName,
                'rm'    => true, // True => File will be remove from directory after download.
            ],
            DirectoryList::MEDIA,
            'text/csv',
            null
        );
    	
      	$resultRedirect = $this->resultRedirectFactory->create();
		$resultRedirect->setPath('cms/page/index');
		return $resultRedirect;
    }
}