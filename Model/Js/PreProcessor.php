<?php

namespace LuxTechnology\FixBug5630\Model\Js;

use Magento\Framework\App\AreaList;
use Magento\Framework\Filesystem;
use Magento\Framework\TranslateInterface;
use Magento\Framework\Translate\Js\Config;
use Magento\Framework\View\Asset\File\FallbackContext;
use Magento\Framework\View\Asset\PreProcessorInterface;
use Magento\Framework\View\Asset\PreProcessor\Chain;

/**
 * PreProcessor responsible for replacing translation calls in js files to translated strings
 */
class PreProcessor implements PreProcessorInterface
{
    private $data;

    /**
     * Javascript translation configuration
     *
     * @var Config
     */
    protected $config;

    /**
     * @var AreaList
     */
    protected $areaList;

    /**
     * @var TranslateInterface
     */
    protected $translate;
    
    /**
     * @param Config $config
     * @param AreaList $areaList
     * @param TranslateInterface $translate
     */
    public function __construct(Config $config, AreaList $areaList, TranslateInterface $translate)
    {
        $this->config = $config;
        $this->areaList = $areaList;
        $this->translate = $translate;
    }

    /**
     * Transform content and/or content type for the specified preprocessing chain object
     *
     * @param Chain $chain
     * @return void
     */
    public function process(Chain $chain)
    {
        if ($this->config->isEmbeddedStrategy()) {

            $context = $chain->getAsset()->getContext();

            $areaCode = \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE;

            if ($context instanceof FallbackContext) {
                $areaCode = $context->getAreaCode();
                $this->translate->setLocale($context->getLocale());
            }

            $area = $this->areaList->getArea($areaCode);
            $area->load(\Magento\Framework\App\Area::PART_TRANSLATE);

            $chain->setContent($this->translateFile($chain->getContent()));
        }
    }

    /**
     * Replace translation calls with translation result and return content
     *
     * @param string $content
     * @return string
     */
    public function translateFile($content)
    {
        $this->data = $this->translate->getData();

        foreach ($this->config->getPatterns() as $pattern) {
            if(strpos($pattern, 'i18n')) {
                $content = preg_replace_callback($pattern, [$this, 'i18nReplaceCallback'], $content);
            }
            else {
                $content = preg_replace_callback($pattern, [$this, 'replaceCallback'], $content);   
            }
        }

        return $content;
    }

    /**
     * Replace callback for preg_replace_callback function
     *
     * @param array $matches
     * @return string
     */
    protected function i18nReplaceCallback($matches)
    {
        if(array_key_exists($matches[2], $this->data)){
            return "text: '" . substr(json_encode(__($matches[2])),1,-1) . "'";
        }
        else
            return $matches[0];
    }

    /**
     * Replace callback for preg_replace_callback function
     *
     * @param array $matches
     * @return string
     */
    protected function replaceCallback($matches)
    {
        if(array_key_exists($matches[2], $this->data)){
            return "'" . substr(json_encode(__($matches[2])),1,-1) . "'";
        }
        else
            return $matches[0];
    }
}
