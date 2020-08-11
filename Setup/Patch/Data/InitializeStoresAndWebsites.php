<?php declare(strict_types=1);

namespace Namluu\Config\Setup\Patch\Data;

use Magento\Catalog\Helper\DefaultCategory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Helper\DefaultCategoryFactory;
use Magento\Store\Model\WebsiteFactory;
use Magento\Store\Model\GroupFactory;
use Magento\Store\Model\StoreFactory;

class InitializeStoresAndWebsites implements DataPatchInterface
{
    public const ENABLE_STATUS = 1;
    public const DEFAULT_WEBSITE_ID = 1;
    public const DEFAULT_GROUP_ID = 1;
    public const DEFAULT_STORE_ID = 1;
    public const AU_WEBSITE_NAME = 'Hard Yakka AU';
    public const AU_WEBSITE_CODE = 'hy_au';
    public const AU_STORE_NAME = 'Australia Store';
    public const AU_STORE_CODE = 'hy_au_store';
    public const AU_STORE_VIEW_NAME = 'English AU';
    public const AU_STORE_VIEW_CODE = 'au_en';
    public const NZ_WEBSITE_NAME = 'Hard Yakka NZ';
    public const NZ_WEBSITE_CODE = 'hy_nz';
    public const NZ_STORE_NAME = 'New Zealand Store';
    public const NZ_STORE_CODE = 'hy_nz_store';
    public const NZ_STORE_VIEW_NAME = 'English NZ';
    public const NZ_STORE_VIEW_CODE = 'nz_en';

    /** @var WebsiteFactory  */
    private $websiteFactory;

    /** @var GroupFactory  */
    private $groupFactory;

    /** @var StoreFactory  */
    private $storeFactory;

    /** @var DefaultCategory */
    private $defaultCategory;

    /** @var DefaultCategoryFactory  */
    private $defaultCategoryFactory;

    /**
     * InitializeStoresAndWebsites constructor function.
     */
    public function __construct(
        WebsiteFactory $websiteFactory,
        GroupFactory $groupFactory,
        StoreFactory $storeFactory,
        DefaultCategoryFactory $defaultCategoryFactory
    ) {
        $this->websiteFactory = $websiteFactory;
        $this->groupFactory = $groupFactory;
        $this->storeFactory = $storeFactory;
        $this->defaultCategoryFactory = $defaultCategoryFactory;
    }

    /**
     * @return DataPatchInterface|void
     * @throws \Exception
     */
    public function apply()
    {
        try {
            // reduce issue with default website
            $this->updateMainWebsite();
            $this->updateMainWebsiteStore();
            $this->updateDefaultStoreView();

            $this->addNewWebsiteAndStoreview();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    private function updateMainWebsite(): void
    {
        $website = $this->websiteFactory->create()->load(self::DEFAULT_WEBSITE_ID);
        if ($website->getId()) {
            $website->setCode(self::AU_WEBSITE_CODE);
            $website->setName(self::AU_WEBSITE_NAME);
            $website->save();
        }
    }

    /**
     * @throws \Exception
     */
    private function updateMainWebsiteStore(): void
    {
        $group = $this->groupFactory->create()->load(self::DEFAULT_GROUP_ID);
        if ($group->getId()) {
            $group->setCode(self::AU_STORE_CODE);
            $group->setName(self::AU_STORE_NAME);
            $group->save();
        }
    }

    /**
     * @throws LocalizedException
     */
    private function updateDefaultStoreView(): void
    {
        $storeview = $this->storeFactory->create()->load(self::DEFAULT_STORE_ID);
        if ($storeview->getId()) {
            $storeview->setCode(self::AU_STORE_VIEW_CODE);
            $storeview->setName(self::AU_STORE_VIEW_NAME);
            $storeview->save();
        }
    }

    /**
     * @throws \Exception
     */
    private function addNewWebsiteAndStoreview(): void
    {
        /** @var \Magento\Store\Model\Website $website */
        $website = $this->createNewWebsite();
        if ($websiteId = (int)$website->getId()) {
            /** @var \Magento\Store\Model\Group $group */
            $group = $this->createNewWebsiteStore($websiteId);
            if ($groupId = (int)$group->getId()) {
                $this->createNewStoreview($websiteId, $groupId);
            }
        }
    }

    /**
     * @throws \Exception
     */
    private function createNewWebsite(): \Magento\Store\Model\Website
    {
        $websiteData = [
            'name' => self::NZ_WEBSITE_NAME,
            'code' => self::NZ_WEBSITE_CODE,
            'sort_order' => 1,
            'default_group' => 0,
            'is_default' => 0
        ];
        $website = $this->websiteFactory->create();
        $website->setData($websiteData);
        return $website->save();
    }

    /**
     * @throws \Exception
     */
    private function createNewWebsiteStore(int $websiteId): \Magento\Store\Model\Group
    {
        $groupData = [
            'website_id' => $websiteId,
            'name' => self::NZ_STORE_NAME,
            'code' => self::NZ_STORE_CODE,
            'root_category_id' => $this->getDefaultCategory()->getId(),
            'default_store_id' => 0
        ];
        $group = $this->groupFactory->create();
        $group->setData($groupData);
        return $group->save();
    }

    /**
     * @throws \Exception
     */
    private function createNewStoreview(int $websiteId, int $groupId): void
    {
        $storeviewData = [
            'website_id' => $websiteId,
            'group_id' => $groupId,
            'name' => self::NZ_STORE_VIEW_NAME,
            'code' => self::NZ_STORE_VIEW_CODE,
            'sort_order' => 1,
            'is_active' => self::ENABLE_STATUS
        ];
        $storeview = $this->storeFactory->create();
        $storeview->setData($storeviewData)->save();
    }

    /**
     * @return DefaultCategory|void
     */
    private function getDefaultCategory()
    {
        if ($this->defaultCategory === null) {
            $this->defaultCategory = $this->defaultCategoryFactory->create();
        }
        return $this->defaultCategory;
    }
}
