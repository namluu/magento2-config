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
    private const XML_COUNTRY_ALLOW = "general/country/allow";

    /** @var ModuleDataSetupInterface */
    private $moduleDataSetup;

    /** @var WriterInterface */
    private $configWriter;

    private $websiteRepository;

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

        $this->configWriter->save(
            self::XML_COUNTRY_ALLOW,
            'AU,NZ'
        );

        try {
            $websiteAU = $this->websiteRepository->get(InitializeStoresAndWebsites::AU_WEBSITE_CODE);
            $websiteAUId = $websiteAU->getId();
            $this->configWriter->save(
                self::XML_COUNTRY_ALLOW,
                'AU',
                ScopeInterface::SCOPE_WEBSITES,
                $websiteAUId
            );
        } catch (NoSuchEntityException $e) {
            throw $e;
        }

        try {
            $websiteNZ = $this->websiteRepository->get(InitializeStoresAndWebsites::NZ_WEBSITE_CODE);
            $websiteNZId = $websiteNZ->getId();
            $this->configWriter->save(
                self::XML_COUNTRY_ALLOW,
                'NZ',
                ScopeInterface::SCOPE_WEBSITES,
                $websiteNZId
            );
        } catch (NoSuchEntityException $e) {
            throw $e;
        }

        $this->moduleDataSetup->getConnection()->endSetup();
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
