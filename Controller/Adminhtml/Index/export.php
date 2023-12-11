<?php

namespace NodeSolutions\ExportCms\Controller\Adminhtml\Index;
use Magento\Framework\App\Filesystem\DirectoryList;

class export extends \Magento\Backend\App\Action
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
        \Magento\Cms\Api\BlockRepositoryInterface $blockRepository,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
     	$this->fileFactory = $fileFactory;
     	$this->blockRepository = $blockRepository;
    	$this->csvProcessor = $csvProcessor;
    	$this->directoryList = $directoryList;
    	$this->pageRepositoryInterface = $pageRepositoryInterface;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    	$this->resultRedirectFactory = $resultRedirectFactory;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function getCmsBlock() {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $cmsBlocks = $this->blockRepository->getList($searchCriteria)->getItems();
        return $cmsBlocks;
    }

    public function execute()
    {
    	$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/block.log');
		$logger = new \Zend_Log();
		$logger->addWriter($writer);

		$content[] = [
            'block_id' => __('Block ID'),
            'title' => __('Block Title'),
            'identifier' => __('Identifier'),
            'content' => __('Content'),
            'creation_time' => __('Creation Time'),
            'update_time' => __('Updated Time'),
            'is_active' => __('Is Active'),
            '_first_store_id' => __('First Store Id'),
            'store_code' => __('Store Code')
        ];
        $fileName = 'imported_cms_block_data.csv';
    	$cmsBlocksCollection = $this->getCmsBlock();
    	$filePath =  $this->directoryList->getPath(DirectoryList::MEDIA) . "/" . $fileName;
    	foreach ($cmsBlocksCollection as $value) {
			$content[] = [
	    		$blockId = $value->getBlockId(),
	    		$blockTitle = $value->getTitle(),
	    		$blockIdentifier = $value->getIdentifier(),
                $blockContent = $value->getContent(),
                $blockCreationTime = $value->getCreationTime(),
                $blockUpdateTime = $value->getUpdateTime(),
                $blockIsActive = $value->getIsActive(),
                $blockFirstStoreId = $value->getFirstStoreId(),
                $blockStoreCode = $value->getStoreCode()
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
		$resultRedirect->setPath('cms/block/index');
		return $resultRedirect;
    }
}