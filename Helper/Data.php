<?php

namespace EW\ConfigScopeHints\Helper;
use \Magento\Store\Model\Website;
use \Magento\Store\Model\Store;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
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
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Backend\Model\Url $urlBuilder
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Backend\Model\Url $urlBuilder
    ) {
        $this->storeManager = $storeManager;
        $this->context = $context;
        // Ideally we would just retrieve the urlBuilder using $this->content->getUrlBuilder(), but since it retrieves
        // an instance of \Magento\Framework\Url instead of \Magento\Backend\Model\Url, we must explicitly request it
        // via DI.
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Gets store tree in a format easily walked over
     * for config path value comparison
     *
     * @return array
     */
    public function getScopeTree() {
        $tree = array('websites' => array());

        $websites = $this->storeManager->getWebsites();

        /* @var $website Website */
        foreach($websites as $website) {
            $tree['websites'][$website->getId()] = array('stores' => array());

            /* @var $store Store */
            foreach($website->getStores() as $store) {
                $tree['websites'][$website->getId()]['stores'][] = $store->getId();
            }
        }

        return $tree;
    }

    /**
     * Wrapper method to get config value at path, scope, and scope code provided
     *
     * @param $path
     * @param $contextScope
     * @param $contextScopeId
     * @return mixed
     */
    protected function _getConfigValue($path, $contextScope, $contextScopeId) {
        return $this->context->getScopeConfig()->getValue($path, $contextScope, $contextScopeId);
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

        if(is_null($currentValue)) {
            return array(); //something is off, let's bail gracefully.
        }

        $overridden = array();

        switch($contextScope) {
            case 'websites':
                $stores = array_values($tree['websites'][$contextScopeId]['stores']);
                foreach($stores as $storeId) {
                    $value = $this->_getConfigValue($path, 'stores', $storeId);
                    if($value != $currentValue) {
                        $overridden[] = array(
                            'scope'     => 'store',
                            'scope_id'  => $storeId
                        );
                    }
                }
                break;
            case 'default':
                foreach($tree['websites'] as $websiteId => $website) {
                    $websiteValue = $this->_getConfigValue($path, 'websites', $websiteId);
                    if($websiteValue != $currentValue) {
                        $overridden[] = array(
                            'scope'     => 'website',
                            'scope_id'  => $websiteId
                        );
                    }

                    foreach($website['stores'] as $storeId) {
                        $value = $this->_getConfigValue($path, 'stores', $storeId);
                        if($value != $currentValue && $value != $websiteValue) {
                            $overridden[] = array(
                                'scope'     => 'store',
                                'scope_id'  => $storeId
                            );
                        }
                    }
                }
                break;
        }

        return $overridden;
    }

    /**
     * Get HTML output for override hint UI
     *
     * @param \Magento\Config\Block\System\Config\Form $form
     * @param array $overridden
     * @return string
     */
    public function formatOverriddenScopes(\Magento\Config\Block\System\Config\Form $form, array $overridden) {
        $title = __('This setting is overridden at a more specific scope. Click for details.');

        $formatted = '<a class="overridden-hint-list-toggle" title="'. $title .'" href="#"><span>'. $title .'</span></a>'.
            '<ul class="overridden-hint-list">';

        foreach($overridden as $overriddenScope) {
            $scope = $overriddenScope['scope'];
            $scopeId = $overriddenScope['scope_id'];
            $scopeLabel = $scopeId;

            $url = '#';
            $section = $form->getSectionCode();
            switch($scope) {
                case 'website':
                    $url = $this->urlBuilder->getUrl(
                        '*/*/*',
                        array(
                            'section' => $section,
                            'website' => $scopeId
                        )
                    );
                    $scopeLabel = sprintf(
                        'website <a href="%s">%s</a>',
                        $url,
                        $this->storeManager->getWebsite($scopeId)->getName()
                    );

                    break;
                case 'store':
                    $store = $this->storeManager->getStore($scopeId);
                    $website = $store->getWebsite();
                    $url = $this->urlBuilder->getUrl(
                        '*/*/*',
                        array(
                            'section'   => $section,
                            'store'     => $store->getId()
                        )
                    );
                    $scopeLabel = sprintf(
                        'store view <a href="%s">%s</a>',
                        $url,
                        $website->getName() . ' / ' . $store->getName()
                    );
                    break;
            }

            $formatted .= "<li class='$scope'>Overridden on $scopeLabel</li>";
        }

        $formatted .= '</ul>';

        return $formatted;
    }
}
