<?php

namespace EW\ConfigScopeHints\Plugin\Framework\Data\Form\Element;

use \Magento\Framework\Data\Form\Element\Fieldset as OriginalFieldset;

class Fieldset
{
    const CONFIG_PATH_NAME_PATTERN = '#\[.+\]#U';

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
     * Attempt to get config path from element HTML name
     *
     * @param string $name
     * @return null|string
     */
    protected function getConfigPathFromName($name) {
        //$name is of form groups[some_group_name][fields][target_config_path_value][value]
        if(preg_match_all(self::CONFIG_PATH_NAME_PATTERN, $name, $nameComponents)) {
            $nameComponents = $nameComponents[0];
            foreach($nameComponents as $i => $nameComponent) {
                if($nameComponent != '[fields]') continue;

                if(!isset($nameComponents[$i+1])) break;

                return trim($nameComponents[$i+1], '[]');
            }
        }

        return null;
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
        $configPath = $config['field_config']['id'];
        $groupPath = $config['field_config']['path'];
        $path = $groupPath . '/' . $configPath;

        $scope = $config['scope'];
        $scopeId = $config['scope_id'];
        $section = $this->request->getParam('section'); //@todo: don't talk to request directly

        $overriddenLevels = $this->helper->getOverriddenLevels(
            $path,
            $scope,
            $scopeId
        );

        if(empty($overriddenLevels) && isset($config['name'])) {
            // Accommodate certain false negatives where the config path is different than the field's ID.

            $path = $groupPath . '/' . $this->getConfigPathFromName($config['name']);
            $overriddenLevels = $this->helper->getOverriddenLevels(
                $path,
                $scope,
                $scopeId
            );
        }

        if(!empty($overriddenLevels)) {
            $config['comment'] .= $this->helper->formatOverriddenScopes($section, $overriddenLevels);
        }

        return $proceed($elementId, $type, $config, $after, $isAdvanced);
    }
}