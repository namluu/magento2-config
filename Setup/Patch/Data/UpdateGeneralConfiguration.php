<?php declare(strict_types=1);

namespace Namluu\Config\Setup\Patch\Data;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\WebsiteRepository;

class UpdateGeneralConfiguration implements DataPatchInterface
{
    private const XML_COUNTRY_ALLOW = 'general/country/allow';
    private const XML_LOCALE_TIMEZONE = 'general/locale/timezone';
    private const XML_LOCALE_CODE = 'general/locale/code';
    private const XML_LOCALE_WEIGHT = 'general/locale/weight_unit';
    private const XML_LOCALE_FIRSTDAY = 'general/locale/firstday';

    private const FIRSTDAY_MONDAY = '1';

    /** @var ModuleDataSetupInterface */
    private $moduleDataSetup;

    /** @var WriterInterface */
    private $configWriter;

    private $websiteRepository;

    private $websiteAUId;

    private $websiteNZId;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        WriterInterface $configWriter,
        WebsiteRepository $websiteRepository
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->configWriter = $configWriter;
        $this->websiteRepository = $websiteRepository;
    }

    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $this->getWebsiteIds();
        $this->updateCountryAllowed();
        $this->updateLocale();

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    private function getWebsiteIds()
    {
        try {
            $websiteAU = $this->websiteRepository->get(InitializeStoresAndWebsites::AU_WEBSITE_CODE);
            $this->websiteAUId = $websiteAU->getId();
            $websiteNZ = $this->websiteRepository->get(InitializeStoresAndWebsites::NZ_WEBSITE_CODE);
            $this->websiteNZId = $websiteNZ->getId();
        } catch (NoSuchEntityException $e) {
            throw $e;
        }
    }

    private function updateCountryAllowed()
    {
        $this->configWriter->save(
            self::XML_COUNTRY_ALLOW,
            'AU,NZ'
        );

        $this->configWriter->save(
            self::XML_COUNTRY_ALLOW,
            'AU',
            ScopeInterface::SCOPE_WEBSITES,
            $this->websiteAUId
        );

        $this->configWriter->save(
            self::XML_COUNTRY_ALLOW,
            'NZ',
            ScopeInterface::SCOPE_WEBSITES,
            $this->websiteNZId
        );
    }

    private function updateLocale()
    {
        $this->configWriter->save(
            self::XML_LOCALE_TIMEZONE,
            'Australia/Melbourne'
        );

        $this->configWriter->save(
            self::XML_LOCALE_TIMEZONE,
            'Pacific/Auckland',
            ScopeInterface::SCOPE_WEBSITES,
            $this->websiteNZId
        );

        $this->configWriter->save(
            self::XML_LOCALE_CODE,
            'en_AU'
        );

        $this->configWriter->save(
            self::XML_LOCALE_WEIGHT,
            'kgs'
        );

        $this->configWriter->save(
            self::XML_LOCALE_FIRSTDAY,
            self::FIRSTDAY_MONDAY
        );
    }

    /**
     * @return string[]
     */
    public static function getDependencies(): array
    {
        return [InitializeStoresAndWebsites::class];
    }

    /**
     * @return string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}
