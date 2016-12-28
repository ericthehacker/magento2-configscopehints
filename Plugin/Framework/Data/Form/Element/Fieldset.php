<?php

namespace EW\ConfigScopeHints\Plugin\Framework\Data\Form\Element;

use \Magento\Framework\Data\Form\Element\Fieldset as OriginalFieldset;

class Fieldset
{
    /**
     * @var \EW\ConfigScopeHints\Helper\Data
     */
    protected $helper;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Fieldset constructor.
     * @param \EW\ConfigScopeHints\Helper\Data $helper
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \EW\ConfigScopeHints\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request
    )
    {
        $this->helper = $helper;
        $this->request = $request;
    }

    /**
     * If field is overwritten at more specific scope(s),
     * set field hint with this info.
     *
     * @param OriginalFieldset $subject
     * @param callable $proceed
     * @param string $elementId
     * @param string $type
     * @param array $config
     * @param bool $after
     * @param bool $isAdvanced
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function aroundAddField(OriginalFieldset $subject, callable $proceed, $elementId, $type, $config, $after = false, $isAdvanced = false) {
        $path = $config['field_config']['path'] . '/' . $config['field_config']['id'];
        $scope = $config['scope'];
        $scopeId = $config['scope_id'];
        $section = $this->request->getParam('section'); //@todo: don't talk to request directly

        $overriddenLevels = $this->helper->getOverriddenLevels(
            $path,
            $scope,
            $scopeId
        );

        if(!empty($overriddenLevels)) {
            $config['hint'] = $this->helper->formatOverriddenScopes($section, $overriddenLevels);
        }

        return $proceed($elementId, $type, $config, $after, $isAdvanced);
    }
}