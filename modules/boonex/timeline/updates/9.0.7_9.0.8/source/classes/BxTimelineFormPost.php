<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) UNA, Inc - https://una.io
 * MIT License - https://opensource.org/licenses/MIT
 *
 * @defgroup    Timeline Timeline
 * @ingroup     UnaModules
 *
 * @{
 */

/**
 * Create/Edit entry form
 */
class BxTimelineFormPost extends BxBaseModGeneralFormEntry
{
    protected $_bPublicMode;
    protected $_bProfileMode;

    public function __construct($aInfo, $oTemplate = false)
    {
        $this->MODULE = 'bx_timeline';

        parent::__construct($aInfo, $oTemplate);

        $iUserId = $this->_oModule->getUserId();
        $iOwnerId = $this->_oModule->getOwnerId();

        $this->_bPublicMode = isset($this->aParams['display']) && $this->aParams['display'] == $this->_oModule->_oConfig->getObject('form_display_post_add_public');
        $this->_bProfileMode = isset($this->aParams['display']) && $this->aParams['display'] == $this->_oModule->_oConfig->getObject('form_display_post_add_profile');

		$this->aFormAttrs['action'] = BX_DOL_URL_ROOT . $this->_oModule->_oConfig->getBaseUri() . 'post/';
        
		$this->aInputs['owner_id']['value'] = $iOwnerId;

        if(isset($this->aInputs['text'])) {
            if(empty($this->aInputs['text']['attrs']) || !is_array($this->aInputs['text']['attrs']))
                $this->aInputs['text']['attrs'] = array();

            $this->aInputs['text']['attrs']['id'] = $this->_oModule->_oConfig->getHtmlIds('post', 'textarea') . time();
        }

        if(isset($this->aInputs['object_privacy_view'])) {
            $this->aInputs['object_privacy_view'] = array_merge($this->aInputs['object_privacy_view'], BxDolPrivacy::getGroupChooser($this->_oModule->_oConfig->getObject('privacy_view'), 0, array(
                'title' => _t('_bx_timeline_form_post_input_object_privacy_view')
            )));

            if($this->_bPublicMode || $this->_bProfileMode)
                foreach($this->aInputs['object_privacy_view']['values'] as $iKey => $aValue) {
                    //--- Show 'Public' privacy group only in Public and Profile (for Not Owner) post forms. 
                    if(($this->_bPublicMode || ($this->_bProfileMode && $iOwnerId != $iUserId)) && isset($aValue['key']) && $aValue['key'] == BX_DOL_PG_ALL)
                        continue;

                    //--- Show a default privacy groups Profile (for Owner) post form. 
                    if($this->_bProfileMode && $iOwnerId == $iUserId && isset($aValue['key']) && (int)$aValue['key'] >= 0)
                        continue;

                    unset($this->aInputs['object_privacy_view']['values'][$iKey]);
                }
        }

        if(isset($this->aInputs['link']))
            $this->aInputs['link']['content'] = $this->_oModule->_oTemplate->getAttachLinkField($iUserId);

        if(isset($this->aInputs['photo'])) {
            $aFormNested = array(
                'params' =>array(
                    'nested_form_template' => 'uploader_nfw.html'
                ),
                'inputs' => array(),
            );

            $oFormNested = new BxDolFormNested('photo', $aFormNested, 'do_submit', $this->_oModule->_oTemplate);

            $this->aInputs['photo']['storage_object'] = $this->_oModule->_oConfig->getObject('storage_photos');
            $this->aInputs['photo']['images_transcoder'] = $this->_oModule->_oConfig->getObject('transcoder_photos_preview');
            $this->aInputs['photo']['uploaders'] = !empty($this->aInputs['photo']['value']) ? unserialize($this->aInputs['photo']['value']) : $this->_oModule->_oConfig->getUploaders('photo');
            $this->aInputs['photo']['upload_buttons_titles'] = array('Simple' => 'camera');
            $this->aInputs['photo']['multiple'] = true;
            $this->aInputs['photo']['ghost_template'] = $oFormNested;
        }

	    if(isset($this->aInputs['video'])) {
            $aFormNested = array(
                'params' =>array(
                    'nested_form_template' => 'uploader_nfw.html'
                ),
                'inputs' => array(),
            );

            $oFormNested = new BxDolFormNested('video', $aFormNested, 'do_submit', $this->_oModule->_oTemplate);

            $this->aInputs['video']['storage_object'] = $this->_oModule->_oConfig->getObject('storage_videos');
            $this->aInputs['video']['images_transcoder'] = $this->_oModule->_oConfig->getObject('transcoder_videos_poster');
            $this->aInputs['video']['uploaders'] = !empty($this->aInputs['video']['value']) ? unserialize($this->aInputs['video']['value']) : $this->_oModule->_oConfig->getUploaders('video');
            $this->aInputs['video']['upload_buttons_titles'] = array('Simple' => 'video-camera');
            $this->aInputs['video']['multiple'] = true;
            $this->aInputs['video']['ghost_template'] = $oFormNested;
        }

        if(isset($this->aInputs['attachments']))
            $this->aInputs['attachments']['content'] = '__attachments_menu__';

        if (isset($this->_oModule->_oConfig->CNF['FIELD_LOCATION_PREFIX']) && isset($this->aInputs[$this->_oModule->_oConfig->CNF['FIELD_LOCATION_PREFIX']]))
            $this->aInputs[$this->_oModule->_oConfig->CNF['FIELD_LOCATION_PREFIX']]['manual_input'] = false;
    }

    public function getCode($bDynamicMode = false)
    {
    	$sResult = parent::getCode($bDynamicMode);

    	return $this->_oModule->_oTemplate->parseHtmlByContent($sResult, array(
    		'attachments_menu' => $this->_oModule->getAttachmentsMenuObject()->getCode()
    	));
    }

    public function addHtmlEditor($iViewMode, &$aInput)
    {
        $CNF = &$this->_oModule->_oConfig->CNF;

        $oEditor = BxDolEditor::getObjectInstance(false, $this->oTemplate);
        if (!$oEditor)
            return false;

            
        if(in_array($this->aParams['display'], array($this->_oModule->_oConfig->getObject('form_display_post_add'), $this->_oModule->_oConfig->getObject('form_display_post_add_public'))))
            $oEditor->setCustomConf("
                placeholderText: '" . _t('_bx_timeline_txt_some_text_here') . "',
                initOnClick: false,
            ");

        $oEditor->setCustomToolbarButtons('');

        $this->_sCodeAdd .= $oEditor->attachEditor ('#' . $this->aFormAttrs['id'] . ' [name='.$aInput['name'].']', $iViewMode, $this->_bDynamicMode);

        return true;
    }
}

/** @} */
