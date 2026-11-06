<?
/**
 *
 */
class CPL_Admin_Lib_FormObj extends CP_Common_Lib_FormObj
{
    /**
     *
     */
    function getDateRow($displayTitle, $fieldName, $fieldValue = "", $exp = array()){
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');

        $rowCls        = isset($exp['rowCls'])        ? " {$exp['rowCls']}"                : "";
        $fieldLabelCls = isset($exp['fieldLabelCls']) ? " class='{$exp['fieldLabelCls']}'" : "";
        $fieldCls      = isset($exp['fieldCls'])      ? " class='{$exp['fieldCls']}'"      : "";
        $notesDivCls   = isset($exp['notesDivCls'])   ? " class='{$exp['notesDivCls']}'"   : "class='formFieldNotes'";
        $notes         = isset($exp['notes'])         ? $exp['notes']                      : "";
        $inputType     = isset($exp['password'])      ? "password"                         : "text";
        $extraHtml     = isset($exp['extraHtml'])     ? $exp['extraHtml']                  : "";
        $allowEdit     = isset($exp['allowEdit'])     ? $exp['allowEdit']                  : 0;
        $yearStart     = isset($exp['yearStart'])     ? $exp['yearStart']                  : date('Y') - 10;
        $yearEnd       = isset($exp['yearEnd'])       ? $exp['yearEnd']                    : date('Y') + 10;
        $minDate       = isset($exp['minDate'])       ? $exp['minDate']                    : '';
        $required      = isset($exp['required'])      ? $exp['required']                   : false;
        $fldId         = isset($exp['fldId'])         ? $exp['fldId']                      : "fld_{$fieldName}";
        $dateFormat = $fn->getIssetParam($exp, 'dateFormat', 'yy-mm-dd');

        if ($notes != ""){
           $notes = "<div class=\"{$notesDivCls}\">{$notes}</div>";
        }

        $isEditable    = isset($exp['isEditable'])    ? $exp['isEditable']      : 1;

        if (isset($exp['isEditable'])){
            $isEditable = $exp['isEditable'];
        } else if ($this->mode == 'detail'){
            $isEditable = 0;
        } else {
            $isEditable = 1;
        }

        $requiredText = '';
        $requiredFldText = '';
        if ($required) {
            $requiredText = $this->getRequiredText();
            $requiredFldText = "required='require'  aria-required='true'";
        }

        if($isEditable == 1){
            $fieldValue = $cpUtil->replaceForFormField($fieldValue);
            $fieldValueTemp = "
            <input{$fieldCls}  allowEdit='{$allowEdit}' type='text' name='{$fieldName}' fldId='{$fldId}'
                 class='fld_date MainDateField' yearStart='{$yearStart}' yearEnd='{$yearEnd}' id='fld_{$fieldName}' " .
                 "minDate='{$minDate}' " .
                 "value=\"{$fieldValue}\" dateFormat='{$dateFormat}' {$requiredFldText}>{$extraHtml}
            <input type='text' class='hiddenDateDisplay' name='hidden_date_display' data-onload='{$fieldValue}'>
            ";
        } else {
            if ($cpUtil->is_date($fieldValue)){
                $ts = strtotime($fieldValue);
                $fieldValue = date('d-m-Y', $ts);
            }
            $txt = ($fieldValue != '') ? $fieldValue : '&nbsp;';
            $fieldValueTemp = "<div class='txt'>{$txt}{$extraHtml}</div>";
        }

        $clsName = "row_{$fieldName}";


        $text = "
        <script>
            $(function() {
                // Call the function on each input
                $('.hiddenDateDisplay[data-onload]').each(function() {
                    var dateCheck = $(this).attr('data-onload');
                    
                    if(dateCheck != '') {
                        var date      = dateCheck.replace(/-/g, '/');
                        var newdate   = new Date(date);
                        var dd = ('0' + newdate.getDate()).slice(-2);
                        var mm = ('0' + (newdate.getMonth() + 1)).slice(-2)
                        var y  = newdate.getFullYear();
             
                        var endDate = dd + '-'+ mm + '-' + y;
                    }else {
                        var endDate = '';
                    }

                    $(this).val(endDate);
                });
            });
        </script>
        <div class='type-text ym-fbox-text {$clsName}{$rowCls}'>
            <label{$fieldLabelCls} for='fld_{$fieldName}'>{$displayTitle}{$requiredText}</label>
            {$notes}
            {$fieldValueTemp}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getDDBSRowBySQL($displayTitle, $fieldName, $SQL = "", $fieldValue = "", $extraParam = array()){
        $cpUtil = Zend_Registry::get('cpUtil');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $exp = &$extraParam;
        $text = "";

        $rowCls            = isset($exp['rowCls'])           ? " {$exp['rowCls']}"                : "";
        $fieldLabelCls     = isset($exp['fieldLabelCls'])    ? " class='{$exp['fieldLabelCls']}'" : "";
        $fieldCls          = isset($exp['fieldCls'])         ? " class='{$exp['fieldCls']}'"      : "class='combobox'";
        $jsFunction        = isset($exp['jsFunction'])       ? $exp['jsFunction']                 : "";
        $useKey            = isset($exp['useKey'])           ? $exp['useKey']                     : 0;
        $disabled          = isset($exp['disabled'])         ? $exp['disabled']                   : false;
        $sqlType           = isset($exp['sqlType'])          ? $exp['sqlType']                    : "TwoFields";
        $hideFirstOption   = isset($exp['hideFirstOption'])  ? 1                                  : 0;
        $selectObjID       = isset($exp['selectObjID']    )  ? "id='{$exp['selectObjID']}'"       : "";
        $hasOtherOption    = isset($exp['hasOtherOption'])   ? $exp['hasOtherOption']             : 0;
        $firstOptionLabel  = isset($exp['firstOptionLabel']) ? $exp['firstOptionLabel']           : $ln->getData("cp.form.lbl.pleaseSelect");
        $dummyFirstRowText = isset($exp['dummyFirstRowText'])? $exp['dummyFirstRowText']          : "";
        $hasLabel          = isset($exp['hasLabel'])         ? $exp['hasLabel']                   : true;
        $notesDivCls       = isset($exp['notesDivCls'])      ? " class='{$exp['notesDivCls']}'"   : " class='formFieldNotes'";
        $notesRightCls     = isset($exp['notesRightCls'])    ? " class='{$exp['notesRightCls']}'" : " class='formFieldNotesRight'";
        $notes             = isset($exp['notes'])            ? $exp['notes']                      : "";
        $notesRight        = isset($exp['notesRight'])       ? $exp['notesRight']                 : '';
        $detailValue       = isset($exp['detailValue'])      ? $exp['detailValue']                : '';
        $required          = isset($exp['required'])         ? $exp['required']                   : false;
        $extraHtml         = isset($exp['extraHtml'])        ? $exp['extraHtml']                  : "";
        $tabIndex          = isset($exp['tabindex']) ? "tabindex={$exp['tabindex']}" : '';

        $jsText            = ($jsFunction != "") ? " onchange=\"{$jsFunction}\"" : "";
        $disabled          = ($disabled == true) ? " disabled"      : "";

        if ($this->mode == 'detail'){
            $isEditable = 0;
        } else if (isset($exp['isEditable'])){
            $isEditable = $exp['isEditable'];
        } else {
            $isEditable = 1;
        }

        $requiredText = '';
        $requiredFldText = '';
        if ($required && $isEditable == 1) {
            $requiredText = $this->getRequiredText();
            $requiredFldText = "required='require'  aria-required='true'";
        }

        if ($notes != ""){
           $notes = "<div{$notesDivCls}>{$notes}</div>";
        }

        if ($notesRight != ""){
           $notesRight = "<div{$notesRightCls}>{$notesRight}</div>";
        }

        $labelText = '';
        if ($hasLabel) {
            $labelText = "
            <label for='fld_{$fieldName}'{$fieldLabelCls}>{$displayTitle}{$requiredText}</label>
            ";
        }

        $notesSpan = '';
        if ($extraHtml != "" || $notes != '' || $notesRight != ''){
            $notesSpan = "<span class='ml10'>{$extraHtml}{$notes}{$notesRight}</span>";
        }

        if($isEditable == 1){
            $ddText = '';

            if ($hideFirstOption == 0){
                $selected = ($fieldValue == "") ? " selected='selected'" : "";
                $ddText = "<option value=''{$selected}>{$firstOptionLabel}</option>";
            }

            if ($dummyFirstRowText != ""){
                $ddText .= "<option value=''>{$dummyFirstRowText}</option>";
            }

            if ($SQL != ""){
                if ($sqlType == "TwoFields"){
                    $ddText .= $dbUtil->getDropDownFromSQLCols2($db, $SQL, $fieldValue);

                } else if ($sqlType == "OneField"){
                    $ddText .= $dbUtil->getDropDownFromSQLCols1($db, $SQL, $fieldValue);

                } else if ($sqlType == "hasSeperator"){
                    $ddText .= $dbUtil->getDropDownWithSeperator($db, $SQL, $fieldValue);
                }
            }

            if ($hasOtherOption == 1){
                $selected = ($fieldValue == $cpCfg['otherVLID']) ? "selected='selected'" : "";
                $ddText .= "<option {$selected} value='{$cpCfg['otherVLID']}'>{$cpCfg['otherVLTitle']}</option>";
            }

            $fieldValueTemp = "
            <select name='{$fieldName}' id='fld_{$fieldName}' {$fieldCls}{$jsText}{$disabled} {$requiredFldText} {$tabIndex}>
                {$ddText}
            </select>
            {$notesSpan}
            ";
        } else {
            $fieldValue = ($detailValue != '') ? $detailValue : $fieldValue;
            $txt = ($fieldValue != '') ? $fieldValue : '&nbsp;';
            $fieldValueTemp = "<div class='txt'>{$txt}{$notesSpan}</div>";
        }

        $clsName = "row_{$fieldName}";

        $text = "
        <div class='type-select ym-fbox-select {$clsName}{$rowCls}'>
            {$labelText}
            {$fieldValueTemp}
        </div>
        <script type='text/javascript'>
          $(document).ready(function(){
            $('.combobox').combobox();
          });
        </script>
        ";

        return $text;
    }
}
