<?php
/**
 * @author    Tigren Solutions <info@tigren.com>
 * @copyright Copyright (c) 2019 Tigren Solutions <https://www.tigren.com>. All rights reserved.
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Tigren\CmsGraphQl\Plugin\Model\Resolver\DataProvider;

use Closure;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Widget\Model\Template\FilterEmulate;
use Tigren\Core\Helper\Data;

/**
 * Class Page
 * @package Tigren\CmsGraphQl\Plugin\Model\Resolver\DataProvider
 */
class Page
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var FilterEmulate
     */
    private $widgetFilter;

    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * Page constructor.
     * @param Data $helper
     * @param PageRepositoryInterface $pageRepository
     * @param FilterEmulate $widgetFilter
     */
    public function __construct(
        Data $helper,
        PageRepositoryInterface $pageRepository,
        FilterEmulate $widgetFilter
    ) {
        $this->helper = $helper;
        $this->pageRepository = $pageRepository;
        $this->widgetFilter = $widgetFilter;
    }

    /**
     * @param \Magento\CmsGraphQl\Model\Resolver\DataProvider\Page $subject
     * @param Closure $proceed
     * @param $pageId
     * @return array
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function aroundGetDataByPageId(
        \Magento\CmsGraphQl\Model\Resolver\DataProvider\Page $subject,
        Closure $proceed,
        $pageId
    ) {
        $page = $this->pageRepository->getById($pageId);

        return $this->convertPageData($page);
    }

    /**
     * Convert page data
     *
     * @param PageInterface $page
     * @return array
     * @throws NoSuchEntityException
     */
    private function convertPageData(PageInterface $page)
    {
        if (false === $page->isActive()) {
            throw new NoSuchEntityException();
        }
        $renderedContent = $page->getIdentifier() == 'home' ? '' : $this->widgetFilter->filter($page->getContent());
        $pageData = [
            'url_key' => $page->getIdentifier(),
            PageInterface::TITLE => $page->getTitle(),
            PageInterface::CONTENT => $renderedContent,
            PageInterface::CONTENT_HEADING => $page->getContentHeading(),
            PageInterface::PAGE_LAYOUT => $page->getPageLayout(),
            PageInterface::META_TITLE => $page->getMetaTitle(),
            PageInterface::META_DESCRIPTION => $page->getMetaDescription(),
            PageInterface::META_KEYWORDS => $page->getMetaKeywords(),
            PageInterface::PAGE_ID => $page->getId(),
            PageInterface::IDENTIFIER => $page->getIdentifier(),
        ];
        return $this->helper->applyMetaConfig($pageData, 'cms');
    }
}
