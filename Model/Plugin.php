<?php

namespace EW\ConfigScopeHints\Model;
use \Magento\Config\Model\Config\Structure\Element\Field;
use \Magento\Framework\Phrase;

class Plugin
{
    /** @var \EW\ConfigScopeHints\Helper\Data */
    protected $helper;

    /**
     * @param \EW\ConfigScopeHints\Helper\Data $helper
     */
    public function __construct(\EW\ConfigScopeHints\Helper\Data $helper) {
        $this->helper = $helper;
    }

    /**
     * Intercept core config form block getScopeLabel() method
     * to add additional override hints.
     *
     * @see Magento\Config\Block\System\Config\Form::getScopeLabel()
     * @param \Magento\Config\Block\System\Config\Form $form
     * @param callable $getScopeLabel
     * @param Field $field
     * @return Phrase
     */
    public function aroundGetScopeLabel(\Magento\Config\Block\System\Config\Form $form, \Closure $getScopeLabel, Field $field)
    {
        $currentScopeId = null;
        switch($form->getScope()) {
            case 'websites':
                $currentScopeId = $form->getWebsiteCode();
                break;
            case 'stores':
                $currentScopeId = $form->getStoreCode();
                break;
        }
        $overriddenLevels = $this->helper->getOverriddenLevels($field->getPath(), $form->getScope(), $currentScopeId);

        /* @var $returnPhrase Phrase */
        $labelPhrase = $getScopeLabel($field);

        if(!empty($overriddenLevels)) {
            $scopeHintText = $labelPhrase . $this->helper->formatOverriddenScopes($form, $overriddenLevels);

            // create new phrase, now that constituent strings are translated individually
            $labelPhrase = new Phrase($scopeHintText, $labelPhrase->getArguments());
        }

        return $labelPhrase;
    }
}