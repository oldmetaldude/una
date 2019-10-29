<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) UNA, Inc - https://una.io
 * MIT License - https://opensource.org/licenses/MIT
 *
 * @defgroup    UnaView UNA Studio Representation classes
 * @ingroup     UnaStudio
 * @{
 */

class BxBaseStudioAuditGrid extends BxDolStudioAuditGrid
{
    function __construct($aOptions, $oTemplate = false)
    {
        parent::__construct($aOptions, $oTemplate);
    }
    
    protected function _addJsCss()
    {
        parent::_addJsCss();
        $this->_oTemplate->addJs(array('jquery.form.min.js', 'forms_audit.js'));

        $oForm = new BxTemplStudioFormView(array());
        $oForm->addCssJs();
    }
    
	protected function _getCellAdded($mixedValue, $sKey, $aField, $aRow)
    {
        return parent::_getCellDefault(bx_time_js($mixedValue), $sKey, $aField, $aRow);
    }
    
    protected function _getCellAction ($mixedValue, $sKey, $aField, $aRow)
    {
        return parent::_getCellDefault(_t($aRow['action_lang_key'], $aRow['action_lang_key_params']), $sKey, $aField, $aRow);
    }
    
    protected function _getCellContent ($mixedValue, $sKey, $aField, $aRow)
    {
        $mixedValue = '';
        if ($aRow['content_id'] > 0 && $aRow['content_info_object'] != ''){
            $oContentInfo = BxDolContentInfo::getObjectInstance($aRow['content_info_object']);
            if ($oContentInfo){
    	        $sTitle = bx_process_output($oContentInfo->getContentTitle($aRow['content_id']));
                if ($sTitle != ''){
                    $mixedValue =  $this->_oTemplate->parseHtmlByName('account_link.html', array(
                        'href' => $oContentInfo->getContentLink($aRow['content_id']),
                        'title' =>  $sTitle,
                        'content' =>  $sTitle,
                        'class' => 'bx-def-font-grayed'
                    ));
                }
            }
        }
        if ($mixedValue == ''){
            $mixedValue = bx_process_output($aRow['content_title']);
        }
        return parent::_getCellDefault($mixedValue, $sKey, $aField, $aRow);
    }
    
    protected function _getCellContext ($mixedValue, $sKey, $aField, $aRow)
    {
        if ($aRow['context_profile_id'] > 0){
    	    $oProfile = BxDolProfile::getInstance($aRow['context_profile_id']);
            if ($oProfile){
    	        $sProfile = $oProfile->getDisplayName();
                $mixedValue =  $this->_oTemplate->parseHtmlByName('account_link.html', array(
                    'href' => $oProfile->getUrl(),
                    'title' => $sProfile,
                    'content' => $sProfile,
                    'class' => 'bx-def-font-grayed'
                ));
            }
            else{
                $mixedValue = $aRow['context_profile_title'];
            }
        }
        else{
            $mixedValue = '';
        }
        return parent::_getCellDefault($mixedValue, $sKey, $aField, $aRow);
    }
    
    protected function _getCellModule ($mixedValue, $sKey, $aField, $aRow)
    {
        $oModule = bxDolModule::getInstance($aRow['content_module']);
        if($oModule && $oModule instanceof iBxDolContentInfoService){
            $mixedValue = $oModule->_aModule['title'];
        }
        else{
            $mixedValue = $aRow['content_module'];
        }
        return parent::_getCellDefault($mixedValue, $sKey, $aField, $aRow);
    }
    
    
    protected function _getCellProfile ($mixedValue, $sKey, $aField, $aRow)
    {
        if ($aRow['profile_id'] > 0){
    	    $oProfile = BxDolProfile::getInstance($aRow['profile_id']);
            if ($oProfile){
    	        $sProfile = $oProfile->getDisplayName();
                $mixedValue =  $this->_oTemplate->parseHtmlByName('account_link.html', array(
                    'href' => $oProfile->getUrl(),
                    'title' => $sProfile,
                    'content' => $sProfile,
                    'class' => 'bx-def-font-grayed'
                ));
            }
            else{
                $mixedValue = $aRow['profile_title'];
            }
        }
        else{
            $mixedValue = '';
        }
        return parent::_getCellDefault($mixedValue, $sKey, $aField, $aRow);
    }
    
    protected function _getFilterControls ()
    {
        parent::_getFilterControls();

        $aInputModules = $this->getModulesSelectOneArray('getAudit', false, false);
		$aInputModules['values'] = array_merge(array('' => _t('_adm_txt_select_module')), $aInputModules['values']);
		$oForm = new BxTemplStudioFormView(array());
        $sContent = $oForm->genRow($aInputModules);
        $oForm = new BxTemplStudioFormView(array());

        $aInputSearch = array(
            'type' => 'text',
            'name' => 'keyword',
            'attrs' => array(
                'id' => 'bx-grid-search-' . $this->_sObject,
            ),
            'tr_attrs' => array(
                'style' => 'display:none;'
            )
        );
        $sContent .= $oForm->genRow($aInputSearch);

        return  $sContent;
    }
    
    protected function _getDataSql($sFilter, $sOrderField, $sOrderDir, $iStart, $iPerPage)
    {
        $sFilterContent = bx_get('content_id');
        if(!empty($sFilter2)) {
            $sFilterContent = bx_process_input($sFilterContent);
            $this->_aOptions['source'] .= " AND `content_id` = " .  $sFilterContent . 'AND `content_module` = ' . $this->sModule;
        }
        
        $sFilterContext = bx_get('context_id');
        if(!empty($sFilterContext)) {
            $sFilterContext = bx_process_input($sFilterContext);
            $this->_aOptions['source'] .= " AND `context_profile_id` = " .  $sFilterContext;
        }
        
        $sPrifile = bx_get('profile_id');
        if(!empty($sPrifile)) {
            $sPrifile = bx_process_input($sPrifile);
            $this->_aOptions['source'] .= " AND `profile_id` = " .  $sPrifile;
        }


        return parent::_getDataSql($sFilter, $sOrderField, $sOrderDir, $iStart, $iPerPage);
    }
    
    function getJsObject()
    {
        return 'oBxDolStudioFormsAudit';
    }
    
    function getCode($isDisplayHeader = true)
    {
        return $this->_oTemplate->parseHtmlByName('forms_audit.html', array(
            'content' => parent::getCode($isDisplayHeader),
            'js_object' => $this->getJsObject(),
            'grid_object' => $this->_sObject,
            'params_divider' => $this->sParamsDivider
        ));
    }
}

/** @} */