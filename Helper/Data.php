<?php

namespace EW\ConfigScopeHints\Helper;
use \Magento\Store\Model\Website;
use \Magento\Store\Model\Store;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const STORE_VIEW_SCOPE_CODE = 'stores';
    const WEBSITE_SCOPE_CODE = 'websites';

    /** @var \Magento\Framework\App\Helper\Context */
    protected $context;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $storeManager;

    /**
     * Url Builder
     *
     * @var \Magento\Backend\Model\Url
     */
    protected $urlBuilder;
    /**
     * @var \Magento\Config\Model\Config\Structure\SearchInterface
     */
    protected $configStructure;
    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Backend\Model\Url $urlBuilder
     * @param \Magento\Config\Model\Config\Structure\SearchInterface $configStructure
     * @param \Magento\Framework\Escaper $escaper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Backend\Model\Url $urlBuilder,
        \Magento\Config\Model\Config\Structure\SearchInterface $configStructure,
        \Magento\Framework\Escaper $escaper
    ) {
        parent::__construct($context);

        $this->storeManager = $storeManager;
        $this->context = $context;
        // Ideally we would just retrieve the urlBuilder using $this->content->getUrlBuilder(), but since it retrieves
        // an instance of \Magento\Framework\Url instead of \Magento\Backend\Model\Url, we must explicitly request it
        // via DI.
        $this->urlBuilder = $urlBuilder;
        $this->configStructure = $configStructure;
        $this->escaper = $escaper;
    }

    /**
     * Gets store tree in a format easily walked over
     * for config path value comparison
     *
     * @return array
     */
    public function getScopeTree() {
        $tree = array(self::WEBSITE_SCOPE_CODE => array());

        $websites = $this->storeManager->getWebsites();

        /* @var $website Website */
        foreach($websites as $website) {
            $tree[self::WEBSITE_SCOPE_CODE][$website->getId()] = array(self::STORE_VIEW_SCOPE_CODE => array());

            /* @var $store Store */
            foreach($website->getStores() as $store) {
                $tree[self::WEBSITE_SCOPE_CODE][$website->getId()][self::STORE_VIEW_SCOPE_CODE][] = $store->getId();
            }
        }

        return $tree;
    }

    /**
     * Wrapper method to get config value at path, scope, and scope code provided
     *
     * @param string $path
     * @param string $contextScope
     * @param string|int $contextScopeId
     * @return string
     */
    protected function _getConfigValue($path, $contextScope, $contextScopeId) {
        return $this->context->getScopeConfig()->getValue($path, $contextScope, $contextScopeId);
    }

    /**
     * Gets human-friendly display value(s) for given config path
     *
     * @param string $path
     * @param string $contextScope
     * @param string|int $contextScopeId
     * @return array
     */
    public function getConfigDisplayValue($path, $contextScope, $contextScopeId) {
        $value = $this->_getConfigValue($path, $contextScope, $contextScopeId);

        $labels = [$value]; //default labels to raw value

        /** @var \Magento\Config\Model\Config\Structure\Element\Field $field */
        $field = $this->configStructure->getElement($path);

        if($field->getOptions()) {
            $labels = []; //reset labels so we can add human-friendly labels

            $optionsByValue = [];
            foreach($field->getOptions() as $option) {
                if (isset($option['value'])) {
                    $optionsByValue[$option['value']] = $option;
                }
            }

            $values = explode(',', $value);

            foreach($values as $valueInstance) {
                $labels[] = isset($optionsByValue[$valueInstance])
                    ? $optionsByValue[$valueInstance]['label'] : $valueInstance;

            }
        }

        return $labels;
    }

    /**
     * Gets array of scopes and scope IDs where path value is different
     * than supplied context scope and context scope ID.
     * If no lower-level scopes override the value, return empty array.
     *
     * @param $path
     * @param $contextScope
     * @param $contextScopeId
     * @return array
     */
    public function getOverriddenLevels($path, $contextScope, $contextScopeId) {
        $tree = $this->getScopeTree();

        $currentValue = $this->_getConfigValue($path, $contextScope, $contextScopeId);

        $overridden = array();

        switch($contextScope) {
            case self::WEBSITE_SCOPE_CODE:
                $stores = array_values($tree[self::WEBSITE_SCOPE_CODE][$contextScopeId][self::STORE_VIEW_SCOPE_CODE]);
                foreach($stores as $storeId) {
                    $value = $this->_getConfigValue($path, self::STORE_VIEW_SCOPE_CODE, $storeId);
                    if($value != $currentValue) {
                        $overridden[] = array(
                            'scope'     => 'store',
                            'scope_id'  => $storeId,
                            'value' => $value,
                            'display_value' => $this->getConfigDisplayValue($path, self::STORE_VIEW_SCOPE_CODE, $storeId)
                        );
                    }
                }
                break;
            case 'default':
                foreach($tree[self::WEBSITE_SCOPE_CODE] as $websiteId => $website) {
                    $websiteValue = $this->_getConfigValue($path, self::WEBSITE_SCOPE_CODE, $websiteId);
                    if($websiteValue != $currentValue) {
                        $overridden[] = array(
                            'scope'     => 'website',
                            'scope_id'  => $websiteId,
                            'value' => $websiteValue,
                            'display_value' => $this->getConfigDisplayValue($path, self::WEBSITE_SCOPE_CODE, $websiteId)
                        );
                    }

                    foreach($website[self::STORE_VIEW_SCOPE_CODE] as $storeId) {
                        $value = $this->_getConfigValue($path, self::STORE_VIEW_SCOPE_CODE, $storeId);
                        if($value != $currentValue && $value != $websiteValue) {
                            $overridden[] = array(
                                'scope'     => 'store',
                                'scope_id'  => $storeId,
                                'value' => $value,
                                'display_value' => $this->getConfigDisplayValue($path, self::STORE_VIEW_SCOPE_CODE, $storeId)
                            );
                        }
                    }
                }
                break;
        }

        return $overridden;
    }

    /**
     * Get HTML formatted value label(s)
     *
     * @param array $labels
     * @return string
     */
    protected function getFormattedValueLabels(array $labels) {
        if(count($labels) == 1) {
            //if only one value, simply return it
            return '<span class="override-value-hint-label">' .
                nl2br($this->escaper->escapeHtml($labels[0])) .
                '</span>';
        }

        $formattedLabels = '';

        foreach($labels as $label) {
            $formattedLabels .= '<li class="override-value-hint-label">' .
                nl2br($this->escaper->escapeHtml($label)) .
            '</li>';
        }

        return '<ul class="override-value-hint-labels">' . $formattedLabels . '</ul>';
    }

    /**
     * Get HTML output for override hint UI
     *
     * @param string $section
     * @param array $overridden
     * @return string
     */
    public function formatOverriddenScopes($section, array $overridden) {
        $formatted = '<div class="overridden-hint-wrapper">' .
            '<p class="lead-text">' . __('This config field is overridden at the following scope(s):') . '</p>' .
            '<dl class="overridden-hint-list">';

        foreach($overridden as $overriddenScope) {
            $scope = $overriddenScope['scope'];
            $scopeId = $overriddenScope['scope_id'];
            $value = $overriddenScope['value'];
            $valueLabel = $overriddenScope['display_value'];
            $scopeLabel = $scopeId;

            $url = '#';
            switch($scope) {
                case 'website':
                    $url = $this->urlBuilder->getUrl(
                        '*/*/*',
                        array(
                            'section' => $section,
                            'website' => $scopeId
                        )
                    );
                    $scopeLabel = __(
                        'Website <a href="%1">%2</a>',
                        $url,
                        $this->storeManager->getWebsite($scopeId)->getName()
                    );

                    break;
                case 'store':
                    /** @var \Magento\Store\Model\Store $store */
                    $store = $this->storeManager->getStore($scopeId);
                    $website = $store->getWebsite();
                    $url = $this->urlBuilder->getUrl(
                        '*/*/*',
                        array(
                            'section'   => $section,
                            'store'     => $store->getId()
                        )
                    );
                    $scopeLabel = __(
                        'Store view <a href="%1">%2</a>',
                        $url,
                        $website->getName() . ' / ' . $store->getName()
                    );
                    break;
            }

            $formatted .=
                '<dt class="override-scope ' . $scope . '" title="'. __('Click to see overridden value') .'">'
                    . $scopeLabel .
                '</dt>' .
                '<dd class="override-value">' . $this->getFormattedValueLabels($valueLabel) . '</dd>';
        }

        $formatted .= '</dl></div>';

        return $formatted;
    }
}
